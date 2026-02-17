@extends('layouts.app')

@section('title', 'Approved Leave Requests')

@section('content')
<h2 class="mb-4">Approved Leave Requests</h2>

<a href="{{ route('manager.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Approved Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedRequests as $req)
                <tr>
                    <td><strong>{{ $req->user->name }}</strong></td>
                    <td>{{ $req->leaveType->name }}</td>
                    <td>{{ $req->start_date->format('Y-m-d') }}</td>
                    <td>{{ $req->end_date->format('Y-m-d') }}</td>
                    <td>{{ $req->total_days }}</td>
                    <td>{{ $req->reviewed_at?->format('Y-m-d') ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No approved requests</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
