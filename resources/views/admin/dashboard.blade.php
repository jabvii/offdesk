@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<h2 class="mb-4">Admin Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 32px; font-weight: bold;">{{ $totalUsers }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Total Users</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 32px; font-weight: bold;">{{ $totalManagers }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Managers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 32px; font-weight: bold;">{{ $totalEmployees }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Employees</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 32px; font-weight: bold;">{{ $pendingLeaves }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Pending Approvals</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card p-4">
            <h5 class="card-title mb-3">Quick Actions</h5>
            <a href="{{ route('admin.leave.requests') }}" class="btn btn-primary me-2">Review Leave Requests</a>
            <a href="{{ route('admin.accounts') }}" class="btn btn-info me-2">Approve Accounts</a>
            <a href="{{ route('admin.approved_accounts') }}" class="btn btn-success">View Employees</a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card p-4">
            <h5>This Month</h5>
            <p><strong>Approved:</strong> {{ $approvedThisMonth }}</p>
            <p><strong>Rejected:</strong> {{ $rejectedLeaves }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5>This Year</h5>
            <p><strong>Total Requests:</strong> {{ $totalThisYear }}</p>
            <p><strong>Pending Approval:</strong> {{ $pendingLeaves }}</p>
        </div>
    </div>
</div>

@endsection
