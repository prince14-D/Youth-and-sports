<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffIdCardController extends Controller
{
    public function myIdCard(Request $request): View
    {
        $employee = $request->user()?->employee;
        abort_unless($employee, 403, 'No staff or contractor profile is linked to your account.');
        return $this->show($request, $employee);
    }

    public function show(Request $request, Employee $employee): View
    {
        $this->authorizeCardAccess($request, $employee);
        $data = $this->buildCardData($employee);
        return view('id-cards.show', $data);
    }

    public function downloadPdf(Request $request, Employee $employee)
    {
        $this->authorizeCardAccess($request, $employee);
        $data = $this->buildCardData($employee);

        return Pdf::loadView('id-cards.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download("MYS-ID-Card-{$employee->employee_no}.pdf");
    }

    public function verify(string $employeeNo, string $hash): View
    {
        $employee = Employee::with(['department', 'supervisor'])
            ->where('employee_no', $employeeNo)
            ->first();

        $isAuthentic = false;
        if ($employee) {
            $expected = self::verificationHash($employee);
            $isAuthentic = hash_equals($expected, strtoupper($hash));
        }

        return view('id-cards.verify', [
            'employee'    => $isAuthentic ? $employee : null,
            'isAuthentic' => $isAuthentic,
            'employeeNo'  => $employeeNo,
            'checkedAt'   => now(),
        ]);
    }

    public static function verificationHash(Employee $employee): string
    {
        $secret = (string) config('app.key', 'mys-hrms');
        return strtoupper(substr(hash_hmac('sha256', $employee->id . '|' . $employee->employee_no, $secret), 0, 12));
    }

    private function authorizeCardAccess(Request $request, Employee $employee): void
    {
        $user = $request->user();
        $canViewAll = $user && $user->can('view employees');
        $isSelf = $user && $user->employee_id && (int) $user->employee_id === (int) $employee->id;

        abort_unless($canViewAll || $isSelf, 403, 'You are not authorized to access this ID card.');
    }

    private function buildCardData(Employee $employee): array
    {
        $employee->load(['department', 'supervisor', 'emergencyContacts']);
        $isContractor = $employee->isContractor();
        $verifyHash = self::verificationHash($employee);
        $verifyUrl = route('id-cards.verify', [
            'employeeNo' => $employee->employee_no,
            'hash'       => $verifyHash,
        ]);

        // Generate crisp SVG QR Code via BaconQrCode (zero GD/Imagick requirement)
        $renderer = new ImageRenderer(
            new RendererStyle(180, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($verifyUrl);
        $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        // Logo base64
        $logoFile = public_path('images/logo.png');
        $logoDataUri = is_file($logoFile)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile))
            : null;

        // Photo base64
        $photoFile = $employee->photo_path ? public_path("storage/{$employee->photo_path}") : null;
        $photoDataUri = ($photoFile && is_file($photoFile))
            ? 'data:' . (mime_content_type($photoFile) ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($photoFile))
            : null;

        $issueDate = $employee->date_joined ?? now();
        $validUntil = $isContractor
            ? ($employee->contract_end_date?->format('d M Y') ?? 'Per Active Contract')
            : now()->addYears(2)->format('d M Y');

        $emergencyContact = $employee->emergencyContacts->first();
        $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));

        return compact(
            'employee',
            'isContractor',
            'verifyHash',
            'verifyUrl',
            'qrDataUri',
            'logoDataUri',
            'photoDataUri',
            'issueDate',
            'validUntil',
            'emergencyContact',
            'initials'
        );
    }
}
