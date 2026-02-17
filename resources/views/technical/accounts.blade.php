@extends('layouts.app')

@section('title', 'Account Management')

@section('content')
<h2 class="mb-4">Account Management</h2>

<a href="{{ route('technical.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="card p-4 mb-4">
    <h5>Filter</h5>
    <form method="GET" action="{{ route('technical.accounts') }}" class="row mb-3">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="all">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="all">All Roles</option>
                <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="technical" {{ request('role') === 'technical' ? 'selected' : '' }}>Technical</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort_by" class="form-select">
                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Sort by Name</option>
                <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>Sort by Email</option>
                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Sort by Date</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($user->role) }}</span></td>
                    <td>{{ $user->department ?? 'N/A' }}</td>
                    <td><span class="badge bg-{{ $user->status === 'approved' ? 'success' : ($user->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($user->status) }}</span></td>
                    <td>
                        <a href="{{ route('technical.account.detail', $user->id) }}" class="btn btn-sm btn-info">View</a>
                        @if($user->status === 'pending')
                        <form action="{{ route('technical.account.approve', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No accounts found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
