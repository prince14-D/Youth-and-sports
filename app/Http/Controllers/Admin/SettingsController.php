<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuditService;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'ministry_name'       => 'Ministry of Youth & Sports',
            'republic_label'      => 'Republic of Liberia',
            'work_start_time'     => '08:30',
            'work_end_time'       => '17:00',
            'standard_hours'      => '8.0',
            'retirement_age'      => '60',
            'currency_primary'    => 'LRD',
            'currency_secondary'  => 'USD',
            'biometric_enforced' => true,
            'audit_logging'       => true,
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'work_start_time'  => 'required|string',
            'work_end_time'    => 'required|string',
            'standard_hours'   => 'required|numeric',
            'retirement_age'   => 'required|integer',
        ]);

        AuditService::log('updated', 'SystemSettings', 1, [], $data, 'Updated Ministry System Configuration Settings');

        return redirect()->route('settings.index')->with('success', 'Ministry System Settings updated successfully!');
    }
}
