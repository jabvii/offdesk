@extends('layouts.app')

@section('title', 'Leave Request Details')

@section('content')
<h2 class="mb-4">Leave Request Details</h2>

<a href="{{ route('manager.leave.requests') }}" class="btn btn-secondary mb-3">Back to Requests</a>

<div class="card p-4">
    <h5>Employee: {{ $request->user->name }}</h5>
    <p><strong>Leave Type:</strong> {{ $request->leaveType->name }}</p>
    <p><strong>From:</strong> {{ $request->start_date->format('Y-m-d') }} <strong>To:</strong> {{ $request->end_date->format('Y-m-d') }}</p>
    <p><strong>Total Days:</strong> {{ $request->total_days }}</p>
    <p><strong>Reason:</strong> {{ $request->reason }}</p>

    <hr>

    <h6>Your Decision:</h6>
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('manager.leave.forward', $request->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="manager_remarks" class="form-label">Manager Remarks</label>
                    <textarea name="manager_remarks" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Forward to Admin for Approval</button>
            </form>
        </div>
        <div class="col-md-6">
            <form action="{{ route('manager.leave.reject', $request->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="manager_remarks" class="form-label">Rejection Reason</label>
                    <textarea name="manager_remarks" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this request?')">Reject Request</button>
            </form>
        </div>
    </div>
</div>

@endsection
