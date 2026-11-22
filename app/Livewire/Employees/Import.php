<?php

namespace App\Livewire\Employees;

use App\Mail\EmployeeWelcomeCredentials;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Import extends Component
{
    use WithFileUploads;

    public $csvFile;
    public bool $sendWelcomeEmails = false;
    public $importSummary = null;

    public function downloadTemplate()
    {
        $headers = [
            'Employee ID',
            'Personnel Number',
            'First Name',
            'Last Name',
            'Gender',
            'Date of Birth',
            'Official Email',
            'Phone',
            'Department',
            'Position',
            'Employment Type',
            'Date Joined',
            'Contract End Date',
            'Salary LRD',
            'Salary USD',
            'Bank Name',
            'Bank Account',
            'TIN Number',
            'Social Security No',
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);
            fputcsv($file, [
                'MYS-2026-0101', 'PER-1001', 'John', 'Doe', 'Male', '1990-05-15',
                'john.doe@mys.gov.lr', '+231888123456', 'Administration', 'Technical Officer',
                'Permanent', '2024-01-10', '', '75000', '375', 'LBDI', '00123456789', 'TIN-900112', 'NASS-44210',
            ]);
            fputcsv($file, [
                'MYS-2026-0102', 'PER-1002', 'Mary', 'Johnson', 'Female', '1993-08-22',
                'mary.johnson@mys.gov.lr', '+231777654321', 'Project Delivery Unit (PDU)', 'Project Consultant',
                'Contract', '2026-01-15', '2026-12-31', '65000', '325', 'Ecobank Liberia', '00987654321', 'TIN-900245', 'NASS-44299',
            ]);
            fclose($file);
        };

        return response()->streamDownload($callback, 'mys_employee_import_template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import()
    {
        $this->validate([
            'csvFile' => 'required|file|max:10240',
        ]);

        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');

        // Remove UTF-8 BOM if present
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        $rawHeaders = fgetcsv($file);
        if (!$rawHeaders) {
            session()->flash('error', 'The uploaded CSV file is empty.');
            fclose($file);
            return;
        }

        $colMap = $this->mapHeaders($rawHeaders);
        $defaultHashedPassword = Hash::make('MYSWelcome@2026!');

        // Pre-fetch existing emails (from BOTH employees & users) and employee numbers
        $existingEmpEmails = Employee::pluck('official_email')->filter()->toArray();
        $existingUserEmails = User::pluck('email')->filter()->toArray();
        $existingEmails = array_change_key_case(array_flip(array_merge($existingEmpEmails, $existingUserEmails)), CASE_LOWER);
        $existingEmployeeNos = array_flip(Employee::withTrashed()->pluck('employee_no')->filter()->toArray());

        // Case-insensitive department lookup (by name or code)
        $departments = Department::all();
        $deptByName = [];
        $deptByCode = [];
        foreach ($departments as $d) {
            $deptByName[strtolower(trim($d->name))] = $d->id;
            $deptByCode[strtolower(trim($d->code))] = $d->id;
        }

        $employeeRoleId = DB::table('roles')->where('name', 'Employee')->value('id');
        $contractorRoleId = DB::table('roles')->where('name', 'Contractor')->value('id');

        $successful = 0;
        $welcomeAccounts = [];
        $skipped = 0;
        $failed = 0;
        $errors = [];
        $rowNumber = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;

                if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                    continue;
                }

                $firstName      = $this->getValue($row, $colMap, 'first_name');
                $lastName       = $this->getValue($row, $colMap, 'last_name');
                $fullName       = $this->getValue($row, $colMap, 'full_name');
                $employeeNo     = $this->getValue($row, $colMap, 'employee_no');
                $perNo          = $this->getValue($row, $colMap, 'personnel_number');
                $gender         = $this->getValue($row, $colMap, 'gender');
                $dob            = $this->getValue($row, $colMap, 'date_of_birth');
                $email          = $this->getValue($row, $colMap, 'official_email');
                $phone          = $this->getValue($row, $colMap, 'phone');
                $designation    = $this->getValue($row, $colMap, 'designation');
                $rawType        = $this->getValue($row, $colMap, 'employment_type');
                $deptName       = $this->getValue($row, $colMap, 'department');
                $dateJoined     = $this->getValue($row, $colMap, 'date_joined');
                $contractEnd    = $this->getValue($row, $colMap, 'contract_end_date');
                $salaryLrd      = $this->parseMoney($this->getValue($row, $colMap, 'salary_lrd'));
                $salaryUsd      = $this->parseMoney($this->getValue($row, $colMap, 'salary_usd'));
                $bankName       = $this->getValue($row, $colMap, 'bank_name');
                $bankAccount    = $this->getValue($row, $colMap, 'bank_account');
                $tinNumber      = $this->getValue($row, $colMap, 'tin_number');
                $ssn            = $this->getValue($row, $colMap, 'social_security_no');

                // Normalize Employment Type
                $typeLower = strtolower($rawType);
                if (str_contains($typeLower, 'contract') || str_contains($typeLower, 'consult')) {
                    $employmentType = 'Contract';
                } elseif (str_contains($typeLower, 'casual') || str_contains($typeLower, 'temp')) {
                    $employmentType = 'Casual';
                } elseif (str_contains($typeLower, 'intern')) {
                    $employmentType = 'Intern';
                } else {
                    $employmentType = 'Permanent';
                }

                // Positional fallback if name columns unmapped
                if (empty($firstName) && empty($lastName) && empty($fullName)) {
                    foreach ($row as $cellVal) {
                        $cellVal = trim((string) $cellVal);
                        if (!empty($cellVal) && !is_numeric($cellVal) && strlen($cellVal) > 2 && !str_contains($cellVal, '@') && !in_array(strtolower($cellVal), ['male', 'female', 'm', 'f'], true)) {
                            $fullName = $cellVal;
                            break;
                        }
                    }
                }

                if (empty($firstName) && !empty($fullName)) {
                    $parts = preg_split('/\s+/', trim($fullName), 2);
                    $firstName = $parts[0] ?? '';
                    $lastName = $parts[1] ?? 'Staff';
                }

                if (empty($firstName) && empty($lastName)) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: Skipped because employee name could not be identified.";
                    continue;
                }

                if (empty($firstName)) {
                    $firstName = 'Employee';
                }
                if (empty($lastName)) {
                    $lastName = 'Staff';
                }

                // Resolve Employee Number
                if (empty($employeeNo)) {
                    $year = now()->format('Y');
                    $seq = Employee::withTrashed()->whereYear('created_at', $year)->count() + 1 + $successful;
                    do {
                        $employeeNo = 'MYS-' . $year . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                        $seq++;
                    } while (isset($existingEmployeeNos[$employeeNo]));
                }

                if (isset($existingEmployeeNos[$employeeNo])) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Skipped duplicate Employee ID ({$employeeNo}).";
                    continue;
                }

                if (empty($email)) {
                    $cleanName = Str::slug("{$firstName}.{$lastName}");
                    $cleanId = Str::slug($employeeNo);
                    $email = "{$cleanName}.{$cleanId}@mys.gov.lr";
                }

                $emailLower = strtolower($email);
                if (isset($existingEmails[$emailLower])) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Skipped duplicate Email ({$email}).";
                    continue;
                }

                $existingEmployeeNos[$employeeNo] = true;
                $existingEmails[$emailLower] = true;

                // Resolve Department (case-insensitive match by name or code)
                $departmentId = null;
                if (!empty($deptName)) {
                    $key = strtolower(trim($deptName));
                    if (isset($deptByName[$key])) {
                        $departmentId = $deptByName[$key];
                    } elseif (isset($deptByCode[$key])) {
                        $departmentId = $deptByCode[$key];
                    } else {
                        $baseCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', Str::limit($deptName, 6, ''))) ?: 'DEPT';
                        $code = $baseCode;
                        $suffix = 1;
                        while (isset($deptByCode[strtolower($code)])) {
                            $code = Str::limit($baseCode, 4, '') . $suffix++;
                        }
                        $department = Department::create([
                            'name'      => $deptName,
                            'code'      => $code,
                            'is_active' => true,
                        ]);
                        $departmentId = $department->id;
                        $deptByName[$key] = $departmentId;
                        $deptByCode[strtolower($code)] = $departmentId;
                    }
                }

                $normalizedGender = 'Male';
                if (!empty($gender)) {
                    $gLower = strtolower($gender);
                    if (str_contains($gLower, 'f') || str_contains($gLower, 'woman')) {
                        $normalizedGender = 'Female';
                    }
                }

                $employee = Employee::create([
                    'employee_no'        => $employeeNo,
                    'personnel_number'   => $perNo ?: null,
                    'first_name'         => $firstName,
                    'last_name'          => $lastName,
                    'gender'             => $normalizedGender,
                    'date_of_birth'      => !empty($dob) && strtotime($dob) ? date('Y-m-d', strtotime($dob)) : '1990-01-01',
                    'nationality'        => 'Liberian',
                    'official_email'     => $email,
                    'phone'              => $phone ?: null,
                    'department_id'      => $departmentId,
                    'designation'        => $designation ?: 'Staff',
                    'employment_status'  => 'Active',
                    'employment_type'    => $employmentType,
                    'date_joined'        => !empty($dateJoined) && strtotime($dateJoined) ? date('Y-m-d', strtotime($dateJoined)) : now()->toDateString(),
                    'contract_end_date'  => !empty($contractEnd) && strtotime($contractEnd) ? date('Y-m-d', strtotime($contractEnd)) : null,
                    'salary_amount_lrd'  => $salaryLrd,
                    'salary_amount_usd'  => $salaryUsd,
                    'bank_name'          => $bankName ?: null,
                    'bank_account'       => $bankAccount ?: null,
                    'tin_number'         => $tinNumber ?: null,
                    'social_security_no' => $ssn ?: null,
                ]);

                $user = User::create([
                    'name'                  => $employee->full_name,
                    'email'                 => $employee->official_email,
                    'password'              => $defaultHashedPassword,
                    'employee_id'           => $employee->id,
                    'is_active'             => true,
                    'force_password_change' => true,
                ]);

                $assignedRoleId = ($employmentType === 'Contract' && $contractorRoleId) ? $contractorRoleId : $employeeRoleId;
                if ($assignedRoleId) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id'    => $assignedRoleId,
                        'model_type' => User::class,
                        'model_id'   => $user->id,
                    ]);
                }

                $welcomeAccounts[] = [
                    'name'            => $employee->full_name,
                    'employee_no'     => $employee->employee_no,
                    'employment_type' => $employee->employment_type,
                    'email'           => $user->email,
                ];

                $successful++;
            }
            DB::commit();

            $emailsSent = 0;
            $emailsFailed = 0;
            if ($this->sendWelcomeEmails) {
                foreach ($welcomeAccounts as $account) {
                    try {
                        Mail::to($account['email'])->send(new EmployeeWelcomeCredentials(
                            $account['name'],
                            $account['employee_no'],
                            $account['employment_type'] === 'Contract' ? 'Contractor' : $account['employment_type'],
                            $account['email'],
                            'MYSWelcome@2026!',
                            route('login'),
                        ));
                        $emailsSent++;
                    } catch (\Throwable $mailEx) {
                        $emailsFailed++;
                        if (count($errors) < 20) {
                            $errors[] = "Email failed for {$account['email']}: " . Str::limit($mailEx->getMessage(), 110);
                        }
                    }
                }
            }

            AuditService::log('imported', Employee::class, 0, [], [
                'successful' => $successful, 'skipped' => $skipped, 'failed' => $failed,
            ], "Bulk imported {$successful} employees via CSV");

        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Import processing error: ' . $e->getMessage());
            return;
        } finally {
            fclose($file);
        }

        $this->importSummary = [
            'successful'      => $successful,
            'skipped'         => $skipped,
            'failed'          => $failed,
            'emailsSent'      => $emailsSent ?? 0,
            'emailsFailed'    => $emailsFailed ?? 0,
            'detectedHeaders' => implode(', ', array_filter($rawHeaders)),
            'errors'          => array_slice($errors, 0, 20),
        ];
    }

    private function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $raw) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $raw));

            if (in_array($normalized, ['firstname', 'first', 'givenname', 'fname'], true)) {
                $map['first_name'] = $index;
            } elseif (in_array($normalized, ['lastname', 'last', 'surname', 'familyname', 'lname'], true)) {
                $map['last_name'] = $index;
            } elseif (in_array($normalized, ['name', 'fullname', 'employeename', 'staffname', 'names', 'officername', 'personnelname', 'nameofemployee'], true)) {
                $map['full_name'] = $index;
            } elseif (in_array($normalized, ['employeeno', 'employeeid', 'empid', 'id', 'code', 'employeenumber', 'staffid', 'fileno', 'payrollno'], true)) {
                $map['employee_no'] = $index;
            } elseif (in_array($normalized, ['personnelnumber', 'personnelno', 'perno', 'perid'], true)) {
                $map['personnel_number'] = $index;
            } elseif (in_array($normalized, ['officialemail', 'email', 'emailaddress', 'mail'], true)) {
                $map['official_email'] = $index;
            } elseif (in_array($normalized, ['phone', 'phonenumber', 'mobile', 'contact', 'cell', 'telephone'], true)) {
                $map['phone'] = $index;
            } elseif (in_array($normalized, ['designation', 'position', 'jobtitle', 'title', 'role', 'post'], true)) {
                $map['designation'] = $index;
            } elseif (in_array($normalized, ['employmenttype', 'employeetype', 'stafftype', 'contracttype'], true)) {
                $map['employment_type'] = $index;
            } elseif (in_array($normalized, ['department', 'dept', 'departmentname', 'section', 'unit', 'division', 'ministry'], true)) {
                $map['department'] = $index;
            } elseif (in_array($normalized, ['gender', 'sex'], true)) {
                $map['gender'] = $index;
            } elseif (in_array($normalized, ['datejoined', 'hiredate', 'employmentdate', 'dateemployed', 'firstemployed'], true)) {
                $map['date_joined'] = $index;
            } elseif (in_array($normalized, ['dateofbirth', 'dob', 'birthdate'], true)) {
                $map['date_of_birth'] = $index;
            } elseif (in_array($normalized, ['contractenddate', 'contractexpiry', 'enddate', 'expirydate'], true)) {
                $map['contract_end_date'] = $index;
            } elseif (in_array($normalized, ['salarylrd', 'salaryamountlrd', 'lrd', 'basicsalarylrd', 'grosslrd'], true)) {
                $map['salary_lrd'] = $index;
            } elseif (in_array($normalized, ['salaryusd', 'salaryamountusd', 'usd', 'basicsalaryusd', 'grossusd'], true)) {
                $map['salary_usd'] = $index;
            } elseif (in_array($normalized, ['bankname', 'bank'], true)) {
                $map['bank_name'] = $index;
            } elseif (in_array($normalized, ['bankaccount', 'bankaccountno', 'accountnumber', 'accountno', 'acctno'], true)) {
                $map['bank_account'] = $index;
            } elseif (in_array($normalized, ['tinnumber', 'tin', 'taxid', 'taxnumber'], true)) {
                $map['tin_number'] = $index;
            } elseif (in_array($normalized, ['socialsecurityno', 'nasscorp', 'nasscorpno', 'ssn', 'socialsecurity'], true)) {
                $map['social_security_no'] = $index;
            }
        }
        return $map;
    }

    private function parseMoney(string $raw): float
    {
        $clean = preg_replace('/[^0-9.]/', '', $raw);
        return is_numeric($clean) ? (float) $clean : 0.00;
    }

    private function getValue(array $row, array $map, string $key): string
    {
        if (isset($map[$key]) && isset($row[$map[$key]])) {
            return trim((string) $row[$map[$key]]);
        }
        return '';
    }

    public function render()
    {
        return view('livewire.employees.import');
    }
}
