<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmployeeWelcomeCredentials;

class Import extends Component
{
    use WithFileUploads;

    public $csvFile;
    public $importSummary = null;

    public function downloadTemplate()
    {
        $headers = [
            'Employee ID', 'Personnel Number', 'First Name', 'Last Name', 'Gender',
            'Date of Birth', 'Official Email', 'Phone', 'Department', 'Position', 'Employment Type', 'Date Joined'
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);
            fputcsv($file, ['MYS-2024-0012', 'PER-1002', 'John', 'Doe', 'Male', '1990-05-15', 'john.doe@mys.gov.lr', '+231888123456', 'Administration', 'Technical Officer', '2023-01-10']);
            fclose($file);
        };

        return response()->streamDownload($callback, 'employee_import_template.csv');
    }

    public function import()
    {
        $this->validate([
            'csvFile' => 'required|file|max:10240',
        ]);

        // Maximize execution limits for bulk operations
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
            session()->flash('error', 'The uploaded file is empty.');
            fclose($file);
            return;
        }

        // Map column headers intelligently
        $colMap = $this->mapHeaders($rawHeaders);

        // PERFORMANCE OPTIMIZATION 1: Pre-hash default password once
        $defaultHashedPassword = Hash::make('MYSWelcome@2026!');

        // PERFORMANCE OPTIMIZATION 2: Pre-fetch existing emails & employee numbers into memory sets
        $existingEmails = array_change_key_case(array_flip(Employee::pluck('official_email')->filter()->toArray()), CASE_LOWER);
        $existingEmployeeNos = array_flip(Employee::pluck('employee_no')->filter()->toArray());

        // PERFORMANCE OPTIMIZATION 3: Pre-fetch Department cache
        $departmentMap = Department::pluck('id', 'name')->toArray();

        // PERFORMANCE OPTIMIZATION 4: Get Employee role ID
        $employeeRoleId = DB::table('roles')->where('name', 'Employee')->value('id');

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

                // Skip empty lines
                if (empty(array_filter($row))) {
                    continue;
                }

                // Extract values using map
                $firstName   = $this->getValue($row, $colMap, 'first_name');
                $lastName    = $this->getValue($row, $colMap, 'last_name');
                $fullName    = $this->getValue($row, $colMap, 'full_name');
                $employeeNo  = $this->getValue($row, $colMap, 'employee_no');
                $perNo       = $this->getValue($row, $colMap, 'personnel_number');
                $gender      = $this->getValue($row, $colMap, 'gender');
                $dob         = $this->getValue($row, $colMap, 'date_of_birth');
                $email       = $this->getValue($row, $colMap, 'official_email');
                $phone       = $this->getValue($row, $colMap, 'phone');
                $designation = $this->getValue($row, $colMap, 'designation');
                $employmentType = $this->getValue($row, $colMap, 'employment_type') ?: 'Permanent';
                $employmentType = in_array($employmentType, ['Permanent', 'Contract', 'Casual', 'Intern'], true) ? $employmentType : 'Permanent';
                $deptName    = $this->getValue($row, $colMap, 'department');
                $dateJoined  = $this->getValue($row, $colMap, 'date_joined');

                // Positional search fallback if name columns unmapped
                if (empty($firstName) && empty($lastName) && empty($fullName)) {
                    foreach ($row as $cellVal) {
                        $cellVal = trim($cellVal);
                        if (!empty($cellVal) && !is_numeric($cellVal) && strlen($cellVal) > 2 && !str_contains($cellVal, '@') && !in_array(strtolower($cellVal), ['male', 'female', 'm', 'f'])) {
                            $fullName = $cellVal;
                            break;
                        }
                    }
                }

                if (empty($firstName) && !empty($fullName)) {
                    $parts = preg_split('/\s+/', trim($fullName), 2);
                    $firstName = $parts[0] ?? '';
                    $lastName = $parts[1] ?? '—';
                }

                if (empty($firstName) && empty($lastName)) {
                    $failed++;
                    $errors[] = "Row {$rowNumber}: Skipped because employee name could not be identified.";
                    continue;
                }

                if (empty($firstName)) $firstName = 'Employee';
                if (empty($lastName)) $lastName = 'Staff';

                // Resolve Employee Number
                if (empty($employeeNo)) {
                    $year = now()->format('Y');
                    $count = Employee::whereYear('created_at', $year)->count() + 1 + $successful;
                    $employeeNo = 'MYS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                }

                // Fast in-memory duplicate check for Employee ID
                if (isset($existingEmployeeNos[$employeeNo])) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Skipped duplicate Employee ID ({$employeeNo}).";
                    continue;
                }

                // Fallback Email Generation if missing
                if (empty($email)) {
                    $cleanName = Str::slug("{$firstName}.{$lastName}");
                    $cleanId = Str::slug($employeeNo);
                    $email = "{$cleanName}.{$cleanId}@mys.gov.lr";
                }

                $emailLower = strtolower($email);

                // Fast in-memory duplicate check for Email
                if (isset($existingEmails[$emailLower])) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Skipped duplicate Email ({$email}).";
                    continue;
                }

                // Mark in memory sets to prevent duplicate rows inside same CSV
                $existingEmployeeNos[$employeeNo] = true;
                $existingEmails[$emailLower] = true;

                // Resolve Department with in-memory caching
                $departmentId = null;
                if (!empty($deptName)) {
                    if (isset($departmentMap[$deptName])) {
                        $departmentId = $departmentMap[$deptName];
                    } else {
                        $department = Department::create([
                            'name'      => $deptName,
                            'code'      => strtoupper(Str::slug(Str::limit($deptName, 5, ''))),
                            'is_active' => true,
                        ]);
                        $departmentId = $department->id;
                        $departmentMap[$deptName] = $departmentId;
                    }
                }

                // Normalize Gender
                $normalizedGender = 'Male';
                if (!empty($gender)) {
                    $gLower = strtolower($gender);
                    if (str_contains($gLower, 'f') || str_contains($gLower, 'woman')) {
                        $normalizedGender = 'Female';
                    }
                }

                // Create Employee record
                $employee = Employee::create([
                    'employee_no'        => $employeeNo,
                    'personnel_number'   => $perNo ?: null,
                    'first_name'         => $firstName,
                    'last_name'          => $lastName,
                    'gender'             => $normalizedGender,
                    'date_of_birth'      => !empty($dob) && strtotime($dob) ? date('Y-m-d', strtotime($dob)) : '1990-01-01',
                    'official_email'     => $email,
                    'phone'              => $phone ?: '—',
                    'department_id'      => $departmentId,
                    'designation'        => $designation ?: 'Staff',
                    'employment_status'  => 'Active',
                    'employment_type'    => $employmentType,
                    'date_joined'        => !empty($dateJoined) && strtotime($dateJoined) ? date('Y-m-d', strtotime($dateJoined)) : now(),
                ]);

                // Create linked User account with pre-hashed password
                $user = User::create([
                    'name'        => $employee->full_name,
                    'email'       => $employee->official_email,
                    'password'    => $defaultHashedPassword,
                    'employee_id' => $employee->id,
                    'is_active'   => true,
                    'force_password_change' => true,
                ]);

                // Fast direct role pivot insert
                if ($employeeRoleId) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id'    => $employeeRoleId,
                        'model_type' => User::class,
                        'model_id'   => $user->id,
                    ]);
                }

                $welcomeAccounts[] = [
                    'name' => $employee->full_name,
                    'employee_no' => $employee->employee_no,
                    'employment_type' => $employee->employment_type,
                    'email' => $user->email,
                ];

                $successful++;
            }
            DB::commit();

            foreach ($welcomeAccounts as $account) {
                Mail::to($account['email'])->queue(new EmployeeWelcomeCredentials(
                    $account['name'],
                    $account['employee_no'],
                    $account['employment_type'] === 'Contract' ? 'Contractor' : $account['employment_type'],
                    $account['email'],
                    'MYSWelcome@2026!',
                    route('login'),
                ));
            }

            AuditService::log('imported', Employee::class, 0, [], [], "Bulk imported {$successful} employees via CSV");

        } catch (\Exception $e) {
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
            'detectedHeaders' => implode(', ', array_filter($rawHeaders)),
            'errors'          => array_slice($errors, 0, 15),
        ];
    }

    private function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $raw) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $raw));

            if (in_array($normalized, ['firstname', 'first', 'givenname', 'fname'])) {
                $map['first_name'] = $index;
            } elseif (in_array($normalized, ['lastname', 'last', 'surname', 'familyname', 'lname'])) {
                $map['last_name'] = $index;
            } elseif (in_array($normalized, ['name', 'fullname', 'employeename', 'staffname', 'names', 'officername', 'personnelname', 'nameofemployee'])) {
                $map['full_name'] = $index;
            } elseif (in_array($normalized, ['employeeno', 'employeeid', 'empid', 'id', 'code', 'employeenumber', 'staffid', 'fileno', 'payrollno'])) {
                $map['employee_no'] = $index;
            } elseif (in_array($normalized, ['personnelnumber', 'personnelno', 'perno', 'perid'])) {
                $map['personnel_number'] = $index;
            } elseif (in_array($normalized, ['officialemail', 'email', 'emailaddress', 'mail'])) {
                $map['official_email'] = $index;
            } elseif (in_array($normalized, ['phone', 'phonenumber', 'mobile', 'contact', 'cell', 'telephone'])) {
                $map['phone'] = $index;
            } elseif (in_array($normalized, ['designation', 'position', 'jobtitle', 'title', 'role', 'post'])) {
                $map['designation'] = $index;
            } elseif (in_array($normalized, ['employmenttype', 'employeetype', 'stafftype', 'contracttype'])) {
                $map['employment_type'] = $index;
            } elseif (in_array($normalized, ['department', 'dept', 'departmentname', 'section', 'unit', 'division', 'ministry'])) {
                $map['department'] = $index;
            } elseif (in_array($normalized, ['gender', 'sex'])) {
                $map['gender'] = $index;
            } elseif (in_array($normalized, ['datejoined', 'hiredate', 'employmentdate', 'dateemployed', 'firstemployed'])) {
                $map['date_joined'] = $index;
            } elseif (in_array($normalized, ['dateofbirth', 'dob', 'birthdate'])) {
                $map['date_of_birth'] = $index;
            }
        }
        return $map;
    }

    private function getValue(array $row, array $map, string $key): string
    {
        if (isset($map[$key]) && isset($row[$map[$key]])) {
            return trim($row[$map[$key]]);
        }
        return '';
    }

    public function render()
    {
        return view('livewire.employees.import');
    }
}
