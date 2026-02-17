@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<h2 class="mb-4">Role Management</h2>

<a href="{{ route('technical.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="card p-4 mb-4">
    <h5>Filter</h5>
    <form method="GET" class="row mb-3">
        <div class="col-md-4">
            <select name="role" class="form-select">
                <option value="all">All Roles</option>
                <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="technical" {{ request('role') === 'technical' ? 'selected' : '' }}>Technical</option>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
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
                    <th>Current Role</th>
                    <th>Department</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($user->role) }}</span></td>
                    <td>{{ $user->department ?? 'N/A' }}</td>
                    <td>
                        <form action="{{ route('technical.user.role', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <select name="role" class="form-select form-select-sm" style="width: 150px; display:inline-block;">
                                <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="technical" {{ $user->role === 'technical' ? 'selected' : '' }}>Technical</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
