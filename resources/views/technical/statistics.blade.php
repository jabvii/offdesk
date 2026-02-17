@extends('layouts.app')

@section('title', 'System Statistics')

@section('content')
<h2 class="mb-4">System Statistics</h2>

<a href="{{ route('technical.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card p-4">
            <h5>Users by Role</h5>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Role</th>
                        <th>Count</th>
                    </tr>
                    @foreach($usersByRole as $stat)
                    <tr>
                        <td><span class="badge bg-info">{{ ucfirst($stat->role) }}</span></td>
                        <td><strong>{{ $stat->count }}</strong></td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5>Users by Status</h5>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                    </tr>
                    @foreach($usersByStatus as $stat)
                    <tr>
                        <td><span class="badge bg-{{ $stat->status === 'approved' ? 'success' : ($stat->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($stat->status) }}</span></td>
                        <td><strong>{{ $stat->count }}</strong></td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card p-4">
            <h5>Users by Department</h5>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Department</th>
                        <th>Count</th>
                    </tr>
                    @foreach($usersByDept as $stat)
                    <tr>
                        <td>{{ $stat->department }}</td>
                        <td><strong>{{ $stat->count }}</strong></td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5>Leave Requests by Status</h5>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                    </tr>
                    @foreach($leavesByStatus as $stat)
                    <tr>
                        <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $stat->status)) }}</span></td>
                        <td><strong>{{ $stat->count }}</strong></td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
