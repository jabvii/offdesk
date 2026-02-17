@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Account Details</h2>
        <a href="{{ route('technical.accounts') }}" class="btn btn-outline-secondary">Back to Accounts</a>
    </div>

    <div class="row">
        <!-- User Information Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Name:</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($user->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($user->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($user->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td>{{ $user->department ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @if($user->deleted_at)
                            <tr>
                                <th>Deleted:</th>
                                <td>
                                    <span class="text-danger">{{ $user->deleted_at->format('Y-m-d H:i') }}</span>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Manager/Team Information -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Organizational Info</h5>
                </div>
                <div class="card-body">
                    @if($user->manager)
                        <div class="mb-3">
                            <h6 class="text-muted">Manager</h6>
                            <a href="{{ route('technical.account.detail', $user->manager->id) }}">
                                {{ $user->manager->name }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $user->manager->email }}</small>
                        </div>
                    @endif

                    @if($user->subordinates && $user->subordinates->count() > 0)
                        <div>
                            <h6 class="text-muted">Team Members ({{ $user->subordinates->count() }})</h6>
                            <ul class="list-unstyled">
                                @foreach($user->subordinates as $subordinate)
                                    <li class="mb-2">
                                        <a href="{{ route('technical.account.detail', $subordinate->id) }}">
                                            {{ $subordinate->name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $subordinate->email }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!$user->manager && $user->subordinates && $user->subordinates->count() === 0)
                        <p class="text-muted">No organizational relationships</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balances -->
    @if($user->role === 'employee' || $user->role === 'manager')
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Leave Balances</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th class="text-end">Total Credits</th>
                                        <th class="text-end">Used</th>
                                        <th class="text-end">Pending</th>
                                        <th class="text-end">Available</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->leaveBalances as $balance)
                                        <tr>
                                            <td><strong>{{ $balance->leaveType->name }}</strong></td>
                                            <td class="text-end">{{ number_format($balance->total_credits, 1) }}</td>
                                            <td class="text-end">{{ number_format($balance->used_credits, 1) }}</td>
                                            <td class="text-end">{{ number_format($balance->pending_credits, 1) }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-primary">
                                                    {{ number_format($balance->available_credits, 1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No leave balances</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Leave Requests History -->
    @if($user->leaveRequests->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Leave Requests History ({{ $user->leaveRequests->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->leaveRequests->sortByDesc('created_at') as $request)
                                        <tr>
                                            <td>{{ $request->leaveType->name }}</td>
                                            <td>
                                                <small>
                                                    {{ $request->start_date->format('M d, Y') }}
                                                    to
                                                    {{ $request->end_date->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>{{ number_format($request->total_days, 1) }}</td>
                                            <td>
                                                @if($request->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($request->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @elseif($request->status === 'pending_manager')
                                                    <span class="badge bg-warning">Pending Manager</span>
                                                @elseif($request->status === 'pending_admin')
                                                    <span class="badge bg-info">Pending Admin</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $request->created_at->format('M d, Y') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Actions -->
    @if(!$user->deleted_at && $user->id !== Auth::user()->id)
        <div class="row">
            <div class="col-12">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Approve Button -->
                            @if($user->status === 'pending')
                                <div class="col-md-4 mb-3">
                                    <form action="{{ route('technical.account.approve', $user->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            Approve Account
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <!-- Reject Button -->
                            @if($user->status === 'pending')
                                <div class="col-md-4 mb-3">
                                    <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                        Reject Account
                                    </button>
                                </div>
                            @endif

                            <!-- Delete Button -->
                            @if($user->status === 'approved')
                                <div class="col-md-4 mb-3">
                                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        Delete Account
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Reject Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('technical.account.reject', $user->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="remarks" class="form-label">Remarks (optional)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('technical.account.delete', $user->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <strong>Warning!</strong> This will permanently delete <strong>{{ $user->name }}'s</strong> account.
                        This action cannot be undone.
                    </div>
                    <div class="form-group mb-3">
                        <label for="delete-remarks" class="form-label">Remarks (optional)</label>
                        <textarea class="form-control" id="delete-remarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
