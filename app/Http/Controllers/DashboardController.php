<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
public function index()
{
    $user = auth()->user();

    $leaveRequests = $user->leaveRequests ?? collect();
    $balances = $user->employeeBalances ?? collect();
    $leaveTypes = LeaveType::all(); // fetch all leave types

    return view('dashboard', [
        'user' => $user,
        'leaveRequests' => $leaveRequests,
        'balances' => $balances,
        'leaveTypes' => $leaveTypes, // pass to Blade
    ]);
}
}