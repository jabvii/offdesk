@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">Manager Dashboard</h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 32px; font-weight: bold;">{{ $teamMembers }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Team Members</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 32px; font-weight: bold;">{{ $pendingRequests }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Pending Reviews</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px;">
            <div style="font-size: 32px; font-weight: bold;">{{ $thisMonthRequests }}</div>
            <div style="font-size: 14px; opacity: 0.9;">This Month</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card p-4">
            <h5 class="card-title mb-3">Quick Actions</h5>
            <a href="{{ route('manager.employees') }}" class="btn btn-primary me-2">View Team Employees</a>
            <a href="{{ route('manager.leave.requests') }}" class="btn btn-info me-2">Review Leave Requests</a>
            <a href="{{ route('manager.leave.approved') }}" class="btn btn-success me-2">View Approved Leaves</a>
            <a href="#addLeaveModalLink" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addLeaveModal">Submit My Leave</a>
        </div>
    </div>
</div>

<!-- Submit Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('manager.leave.submit') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="leave_type_id" class="form-label">Leave Type</label>
                        <select name="leave_type_id" class="form-select" required>
                            <option>-- Select Leave Type --</option>
                            <option value="1">Vacation Leave</option>
                            <option value="2">Sick Leave</option>
                            <option value="3">Emergency Leave</option>
                            <option value="4">Paternity Leave</option>
                            <option value="5">Parental Leave</option>
                            <option value="6">Service Incentive Leave</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
