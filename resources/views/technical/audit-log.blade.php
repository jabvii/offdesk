@extends('layouts.app')

@section('title', 'System Audit Log')

@section('content')
<h2 class="mb-4">System Audit Log</h2>

<a href="{{ route('technical.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="card p-4 mb-4">
    <h5>Filter</h5>
    <form method="GET" action="{{ route('technical.audit.log') }}" class="row mb-3">
        <div class="col-md-4">
            <select name="action" class="form-select">
                <option value="all">All Actions</option>
                <option value="user_approved" {{ request('action') === 'user_approved' ? 'selected' : '' }}>User Approved</option>
                <option value="user_rejected" {{ request('action') === 'user_rejected' ? 'selected' : '' }}>User Rejected</option>
                <option value="user_deleted" {{ request('action') === 'user_deleted' ? 'selected' : '' }}>User Deleted</option>
                <option value="leave_approved" {{ request('action') === 'leave_approved' ? 'selected' : '' }}>Leave Approved</option>
                <option value="leave_rejected" {{ request('action') === 'leave_rejected' ? 'selected' : '' }}>Leave Rejected</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="From Date">
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
                    <th>Action</th>
                    <th>Subject</th>
                    <th>Performed By</th>
                    <th>Remarks</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span></td>
                    <td>{{ $log->auditable?->name ?? 'N/A' }} ({{ class_basename($log->auditable_type) }})</td>
                    <td>{{ $log->performedBy->name }}</td>
                    <td>{{ $log->remarks ?? 'N/A' }}</td>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No audit logs found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <nav>
        {{ $logs->links() }}
    </nav>
</div>

@endsection
