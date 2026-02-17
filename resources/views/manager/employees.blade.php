@extends('layouts.app')

@section('title', 'Team Employees')

@section('content')
<h2 class="mb-4">Team Employees</h2>

<a href="{{ route('manager.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="card p-4">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
            <tr>
                <td>{{ $emp->name }}</td>
                <td>{{ $emp->email }}</td>
                <td>{{ $emp->department }}</td>
                <td><span class="badge bg-success">{{ ucfirst($emp->status) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">No employees in your team.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
