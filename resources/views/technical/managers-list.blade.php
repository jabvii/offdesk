@extends('layouts.app')

@section('title', 'Managers by Department')

@section('content')
<h2 class="mb-4">Managers by Department</h2>

<a href="{{ route('technical.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>

<div class="row">
    @foreach($managersByDept as $dept => $managers)
    <div class="col-md-6 mb-4">
        <div class="card p-4">
            <h5>{{ $dept }} Department</h5>
            @forelse($managers as $mgr)
            <div class="mb-3">
                <p><strong>{{ $mgr->name }}</strong> ({{ $mgr->email }})</p>
                <p class="text-muted">Team: {{ $mgr->subordinates->count() }} employees</p>
                <div class="list-group list-group-sm">
                    @foreach($mgr->subordinates as $emp)
                    <a href="#" class="list-group-item list-group-item-action small">{{ $emp->name }}</a>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-muted">No manager assigned</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

@endsection
