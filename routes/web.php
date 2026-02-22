<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ManagerMiddleware;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout Route (auth only)
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// User Dashboard & Leave Routes (auth only)
Route::middleware('auth')->group(function () {
    // User dashboard
    Route::get('/employee/dashboard', [LeaveRequestController::class, 'index'])->name('dashboard');

    // Leave requests
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::post('/leave/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave.cancel');
    Route::get('/employee/leave-history', [LeaveRequestController::class, 'history'])->name('employee.leave.history');
});

// Admin Routes (auth + admin only)
Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {
    // Admin dashboard
    Route::get('/employee/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Leave requests management
    Route::get('/employee/leave-requests', [AdminController::class, 'leaveRequests'])->name('admin.leave.requests');
    Route::post('/employee/leave-requests/{id}/decision', [AdminController::class, 'decision'])->name('admin.leave.decision');
    Route::get('/employee/leave-requests/{id}/sessions', [AdminController::class, 'getLeaveRequestSessions'])->name('admin.leave.sessions');

    // User account management
    Route::get('/accounts', [AdminController::class, 'accounts'])->name('admin.accounts');
    Route::get('approved', [AdminController::class, 'approvedEmployees'])->name('admin.approved_accounts');

    // Add account
    Route::get('/add-account', [AdminController::class, 'showAddAccount'])->name('admin.add.account');
    Route::post('/add-account', [AdminController::class, 'storeAccount'])->name('admin.store.account');

});

// Manager Routes (auth + manager only)
Route::prefix('manager')->middleware(['auth', ManagerMiddleware::class])->group(function () {
    // Manager dashboard
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');

    // Employee leave requests awaiting manager approval
    Route::get('/leave-requests', [ManagerController::class, 'leaveRequests'])->name('manager.leave.requests');
    Route::post('/leave-requests/{id}/decision', [ManagerController::class, 'decision'])->name('manager.leave.decision');
    Route::get('/leave-requests/{id}/sessions', [ManagerController::class, 'getLeaveRequestSessions'])->name('manager.leave.sessions');
    
    // Manager's own leave history
    Route::get('/leave-history', [LeaveRequestController::class, 'history'])->name('manager.leave.history');
});

// This will catch invalid URLs like "/dashboasd"
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});