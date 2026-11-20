<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;

// ── Public API Routes ─────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    Route::post('/auth/login',  [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/attendance/punch', [App\Http\Controllers\Api\AttendanceController::class, 'punch']);
});

// ── Protected API Routes (Sanctum) ───────────────────────────────────────
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Employees (Phase 1 — read for mobile app)
    Route::get('/employees',        [EmployeeController::class, 'index']);
    Route::get('/employees/{id}',   [EmployeeController::class, 'show']);

    // Phase 4 — Employee Self-Service
    Route::get('/my/profile',       [EmployeeController::class, 'myProfile']);
    Route::get('/my/payslips',      [EmployeeController::class, 'myPayslips']);
    Route::get('/my/leave',         [EmployeeController::class, 'myLeave']);
    Route::get('/my/attendance',    [EmployeeController::class, 'myAttendance']);
    Route::post('/my/attendance/punch', [App\Http\Controllers\Api\AttendanceController::class, 'punch']);
    Route::get('/my/notifications', [EmployeeController::class, 'myNotifications']);
    Route::get('/my/excuses',      [EmployeeController::class, 'myExcuses']);
    Route::get('/my/applications', [EmployeeController::class, 'myApplications']);
});
