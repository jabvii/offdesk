@extends('layouts.app')

@section('title', 'Manage Accounts')

@section('content')
<h2 class="mb-4">Account Management</h2>

<a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="card p-4 mb-4">
    <h5 class="card-title">Pending Approvals ({{ $pendingUsersCount }})</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingUsers as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->department ?? 'N/A' }}</td>
                    <td>
                        <form action="{{ route('admin.users.approve', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form action="{{ route('admin.users.reject', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No pending approvals</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4">
    <h5 class="card-title">All Approved Accounts</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allUsers as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($user->role) }}</span></td>
                    <td>{{ $user->department ?? 'N/A' }}</td>
                    <td><span class="badge bg-{{ $user->status === 'approved' ? 'success' : 'danger' }}">{{ ucfirst($user->status) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No approved accounts</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
