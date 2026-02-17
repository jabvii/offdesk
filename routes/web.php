<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;
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

// This will catch invalid URLs like "/dashboasd"
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

use App\Http\Middleware\TechnicalMiddleware; // we'll make this

Route::prefix('technical')->middleware(['auth', 'technical'])->group(function () {
    Route::get('/dashboard', [TechnicalAdminController::class, 'dashboard'])
        ->name('technical.dashboard');
    
    Route::get('/accounts', [TechnicalAdminController::class, 'accounts'])->name('technical.accounts');
    Route::get('/role-management', [TechnicalAdminController::class, 'roleManagement'])->name('technical.role.management');
    Route::get('/audit-log', [TechnicalAdminController::class, 'auditLog'])->name('technical.audit.log');
    Route::get('/managers', [TechnicalAdminController::class, 'managersList'])->name('technical.managers.list');
    Route::get('/statistics', [TechnicalAdminController::class, 'statistics'])->name('technical.statistics');
});
