@extends('layouts.app')

@section('title', 'Leave Requests Approval')

@section('content')
<h2 class="mb-4">Leave Requests for Approval ({{ $pendingCount }})</h2>

<a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Duration</th>
                    <th>Days</th>
                    <th>Manager Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingRequests as $req)
                <tr>
                    <td><strong>{{ $req->user->name }}</strong></td>
                    <td>{{ $req->leaveType->name }}</td>
                    <td>{{ $req->start_date->format('M d') }} - {{ $req->end_date->format('M d, Y') }}</td>
                    <td>{{ $req->total_days }}</td>
                    <td>{{ $req->manager_remarks ?? 'No remarks' }}</td>
                    <td>
                        <form action="{{ route('admin.leave.decision', $req->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <input type="text" name="admin_remarks" placeholder="Remarks" class="form-control form-control-sm mb-2" required>
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <form action="{{ route('admin.leave.decision', $req->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <input type="text" name="admin_remarks" placeholder="Rejection reason" class="form-control form-control-sm mb-2" required>
                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No pending leave requests</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
