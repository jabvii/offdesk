<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\TechnicalController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ManagerMiddleware;
use App\Http\Middleware\TechnicalMiddleware;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout Route (auth only)
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// User Dashboard & Leave Routes (auth only)
Route::middleware('auth')->group(function () {
    // User dashboard
    Route::get('/dashboard', [LeaveRequestController::class, 'index'])->name('dashboard');

    // Leave requests
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::post('/leave/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave.cancel');
});

// Admin Routes (auth + admin only)
Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {
    // Admin dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Leave requests management
    Route::get('employee/leave-requests', [AdminController::class, 'leaveRequests'])->name('admin.leave.requests');
    Route::post('employee/leave-requests/{id}/decision', [AdminController::class, 'decision'])->name('admin.leave.decision');

    // User account approvals
    Route::get('/accounts', [AdminController::class, 'accounts'])->name('admin.accounts');
    Route::post('/users/{user}/approve', [AdminController::class, 'approveUser'])->name('admin.users.approve');
    Route::post('/users/{user}/reject', [AdminController::class, 'rejectUser'])->name('admin.users.reject');

    Route::get('/employees/approved', [AdminController::class, 'approvedEmployees'])->name('admin.approved_accounts');
});

// Manager Routes (auth + manager only)
Route::prefix('manager')->middleware(['auth', ManagerMiddleware::class])->group(function () {
    // Manager dashboard
    Route::get('/dashboard', [ManagerController::class, 'index'])->name('manager.dashboard');

    // View team employees
    Route::get('/employees', [ManagerController::class, 'employees'])->name('manager.employees');

    // Leave request management
    Route::get('/leave-requests', [ManagerController::class, 'leaveRequests'])->name('manager.leave.requests');
    Route::get('/leave-requests/{id}', [ManagerController::class, 'requestDetail'])->name('manager.leave.detail');
    Route::post('/leave-requests/{id}/forward', [ManagerController::class, 'forward'])->name('manager.leave.forward');
    Route::post('/leave-requests/{id}/reject', [ManagerController::class, 'reject'])->name('manager.leave.reject');

    // View approved leave requests
    Route::get('/leave-requests/approved', [ManagerController::class, 'approvedRequests'])->name('manager.leave.approved');

    // Manager submit their own leave request
    Route::post('/leave/submit', [ManagerController::class, 'submitLeave'])->name('manager.leave.submit');
});

// Technical Admin Routes (auth + technical only)
Route::prefix('technical')->middleware(['auth', TechnicalMiddleware::class])->group(function () {
    // Technical dashboard
    Route::get('/dashboard', [TechnicalController::class, 'index'])->name('technical.dashboard');

    // Account management
    Route::get('/accounts', [TechnicalController::class, 'accounts'])->name('technical.accounts');
    Route::get('/accounts/{id}', [TechnicalController::class, 'accountDetail'])->name('technical.account.detail');
    Route::post('/accounts/{id}/approve', [TechnicalController::class, 'approveAccount'])->name('technical.account.approve');
    Route::post('/accounts/{id}/reject', [TechnicalController::class, 'rejectAccount'])->name('technical.account.reject');
    Route::post('/accounts/{id}/delete', [TechnicalController::class, 'deleteAccount'])->name('technical.account.delete');

    // Audit log
    Route::get('/audit-log', [TechnicalController::class, 'auditLog'])->name('technical.audit.log');

    // Managers by department
    Route::get('/managers', [TechnicalController::class, 'managersByDepartment'])->name('technical.managers.list');

    // Role management
    Route::get('/role-management', [TechnicalController::class, 'roleManagement'])->name('technical.role.management');
    Route::post('/users/{id}/role', [TechnicalController::class, 'updateRole'])->name('technical.user.role');

    // Statistics
    Route::get('/statistics', [TechnicalController::class, 'statistics'])->name('technical.statistics');
});

// This will catch invalid URLs like "/dashboasd"
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});