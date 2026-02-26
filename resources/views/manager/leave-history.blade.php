<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Leave History</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/employee/leave-history.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Manager</h2>
            <ul class="nav-links">
                <li><a href="{{ route('manager.dashboard') }}" @if(request()->routeIs('manager.dashboard')) class="active" @endif>Dashboard</a></li>
                <li><a href="#" id="openLeaveModalLink">Request Leave</a></li>
                <li><a href="{{ route('manager.leave.requests') }}" @if(request()->routeIs('manager.leave.requests')) class="active" @endif>Requests</a></li>
                <li><a href="{{ route('manager.team') }}" @if(request()->routeIs('manager.team')) class="active" @endif>View Team</a></li>
                <li><a href="{{ route('manager.leave.history') }}" @if(request()->routeIs('manager.leave.history')) class="active" @endif>Leave History</a></li>
            </ul>
        </div>
        <div class="nav-bottom">
            <ul class="nav-links">
                <li>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirmLogout()">
                        @csrf
                        <button type="submit" class="nav-link logout-link">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main content -->
    <div class="main-content">
        <div class="container">
            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <!-- Leave History Section -->
            <div class="leave-history-container">
                <div class="history-header">
                    <h2>All Your Leave Requests</h2>
                </div>

                @forelse($allRequests as $request)
                    <div class="leave-request-item {{ str_replace('_', '-', strtolower($request->status)) }}">
                        <div class="leave-request-info">
                            <h4 class="{{ strtolower($request->leaveType->code) }}">{{ $request->leaveType->name }}</h4>

                            <p class="pdark">
                                <strong>Dates:</strong>
                                {{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}
                                - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                ({{ $request->total_days }} day{{ $request->total_days > 1 ? 's' : '' }})
                            </p>

                            <p>
                                <strong>Reason:</strong> {{ $request->reason }}
                            </p>

                            <p>
                                <strong>Requested on:</strong> {{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y \a\t h:i A') }}
                            </p>

                            @if($request->admin_remarks && !in_array($request->status, ['pending_admin']))
                                <div class="remarks-section">
                                    <p><span class="remarks-label">Admin Remarks:</span> {{ $request->admin_remarks }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="leave-request-actions">
                            <span class="status-badge status-{{ str_replace('_', '-', $request->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                            <button type="button" class="btn btn-track" onclick="openTrackingModal({{ $request->id }})" title="Track Request"><i class="fas fa-question"></i></button>
                        </div>
                    </div>

                    <!-- Tracking Modal for this request -->
                    <div class="modal tracking-modal" id="trackingModal{{ $request->id }}">
                        <div class="modal-content tracking-modal-content">
                            <div class="modal-header">
                                <h2>Request Tracking</h2>
                                <button type="button" class="modal-close" onclick="closeTrackingModal({{ $request->id }})">&times;</button>
                            </div>
                            <div class="tracking-info">
                                <p><strong>{{ $request->leaveType->name }}</strong></p>
                                <p>{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</p>
                                <p class="tracking-status status-{{ $request->status }}">
                                    Status: {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </p>
                            </div>

                            @php
                                $status = $request->status;
                                $user = auth()->user();
                                
                                $managerStatus = 'completed';
                                $adminStatus = 'waiting';
                                
                                if ($status === 'pending_admin') {
                                    $adminStatus = 'current';
                                } elseif ($status === 'approved') {
                                    $adminStatus = 'completed';
                                } elseif ($status === 'rejected') {
                                    $adminStatus = 'rejected';
                                } elseif ($status === 'cancelled') {
                                    $adminStatus = 'cancelled';
                                }
                            @endphp

                            <div class="tracking-chain">
                                <div class="tracking-step {{ $managerStatus }}">
                                    <div class="step-icon">✓</div>
                                    <div class="step-info">
                                        <span class="step-role">You (Manager)</span>
                                        <span class="step-name">{{ $user->name }}</span>
                                        <span class="step-detail">Submitted {{ $request->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                </div>

                                <div class="chain-connector {{ $adminStatus }}"></div>
                                <div class="tracking-step {{ $adminStatus }}">
                                    <div class="step-icon">
                                        @if($adminStatus === 'completed') ✓
                                        @elseif($adminStatus === 'rejected') ✗
                                        @elseif($adminStatus === 'cancelled') ○
                                        @elseif($adminStatus === 'current') ●
                                        @else 2
                                        @endif
                                    </div>
                                    <div class="step-info">
                                        <span class="step-role">Admin</span>
                                        <span class="step-name">Final Approval</span>
                                        @if($adminStatus === 'completed')
                                            <span class="step-detail">Approved</span>
                                        @elseif($adminStatus === 'rejected')
                                            <span class="step-detail rejected-text">Rejected</span>
                                        @elseif($adminStatus === 'cancelled')
                                            <span class="step-detail cancelled-text">Cancelled</span>
                                        @elseif($adminStatus === 'current')
                                            <span class="step-detail pending-text">Awaiting approval</span>
                                        @else
                                            <span class="step-detail">Pending</span>
                                        @endif
                                        @if($request->admin_remarks)
                                            <span class="step-remarks">"{{ $request->admin_remarks }}"</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($status === 'cancelled')
                            <div class="cancelled-info">
                                <p><strong>Cancelled by:</strong> {{ $user->name }} (You)</p>
                            </div>
                            @endif

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="closeTrackingModal({{ $request->id }})">Close</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="no-requests">No leave requests found</p>
                @endforelse

                <!-- Pagination -->
                @if($allRequests->hasPages())
                    <div class="pagination-simple">
                        @if($allRequests->onFirstPage())
                            <span class="pagination-arrow disabled"><i class="fas fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $allRequests->previousPageUrl() }}" class="pagination-arrow"><i class="fas fa-chevron-left"></i></a>
                        @endif
                        
                        <span class="pagination-info">Page {{ $allRequests->currentPage() }} of {{ $allRequests->lastPage() }}</span>
                        
                        @if($allRequests->hasMorePages())
                            <a href="{{ $allRequests->nextPageUrl() }}" class="pagination-arrow"><i class="fas fa-chevron-right"></i></a>
                        @else
                            <span class="pagination-arrow disabled"><i class="fas fa-chevron-right"></i></span>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div class="modal" id="leaveRequestModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Submit Leave Request</h2>
        </div>
        <form action="{{ route('leave.store') }}" method="POST" id="leaveRequestForm">
            @csrf
            <div class="form-group">
                <label for="leave_type_id">Leave Type</label>
                <select name="leave_type_id" id="leave_type_id" required>
                    <option value="">Select Leave Type</option>
                    @forelse($leaveTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @empty
                        <option value="">No leave types available</option>
                    @endforelse
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" required>
            </div>
            
            <!-- Daily Sessions Table -->
            <div id="sessionsTableContainer" style="display: none; margin-top: 20px;">
                <label>Select Session for Each Day</label>
                <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Session</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsTableBody">
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <label for="reason">Reason</label>
                <textarea name="reason" id="reason" required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
<script>
// Tracking modal functions
function openTrackingModal(id) {
    document.getElementById('trackingModal' + id).classList.add('active');
}

function closeTrackingModal(id) {
    document.getElementById('trackingModal' + id).classList.remove('active');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('tracking-modal')) {
        e.target.classList.remove('active');
    }
});

function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

function openModal() {
    document.getElementById('leaveRequestModal').classList.add('active');
}

function closeModal() {
    document.getElementById('leaveRequestModal').classList.remove('active');
    document.getElementById('leaveRequestForm').reset();
    document.getElementById('sessionsTableContainer').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const openLeaveModalLink = document.getElementById('openLeaveModalLink');
    if (openLeaveModalLink) {
        openLeaveModalLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    const endDateInput = document.getElementById('end_date');
    const startDateInput = document.getElementById('start_date');
    const sessionsTableContainer = document.getElementById('sessionsTableContainer');
    const sessionsTableBody = document.getElementById('sessionsTableBody');
    const leaveRequestForm = document.getElementById('leaveRequestForm');

    function generateSessionsTable() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (!startDate || !endDate) {
            sessionsTableContainer.style.display = 'none';
            return;
        }

        const start = new Date(startDate + 'T00:00:00');
        const end = new Date(endDate + 'T00:00:00');

        if (start > end) {
            alert('Start date must be before or equal to end date');
            endDateInput.value = '';
            sessionsTableContainer.style.display = 'none';
            return;
        }

        sessionsTableBody.innerHTML = '';
        let currentDate = new Date(start);
        const dateFormat = (date) => date.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });

        while (currentDate <= end) {
            const dateStr = currentDate.toISOString().split('T')[0];
            const formattedDate = dateFormat(currentDate);
            const dayOfWeek = currentDate.getDay();
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

            const row = document.createElement('tr');

            if (isWeekend) {
                row.classList.add('weekend-row');
                row.style.backgroundColor = '#f0f0f0';
                row.style.color = '#999';
                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>
                        <span class="weekend-label" style="font-style: italic; color: #aaa;">
                            Weekend
                        </span>
                    </td>
                `;
            } else {
                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>
                        <select name="daily_sessions[]" class="session-select" data-date="${dateStr}" required>
                            <option value="whole_day">Whole Day</option>
                            <option value="morning">Morning (8:00am-12:00pm)</option>
                            <option value="afternoon">Afternoon (1:00pm-5:30pm)</option>
                        </select>
                    </td>
                `;
            }

            sessionsTableBody.appendChild(row);
            currentDate.setDate(currentDate.getDate() + 1);
        }

        sessionsTableContainer.style.display = 'block';
    }

    endDateInput.addEventListener('change', generateSessionsTable);
    startDateInput.addEventListener('change', generateSessionsTable);

    leaveRequestForm.addEventListener('submit', function(e) {
        const sessionSelects = document.querySelectorAll('.session-select');

        if (sessionSelects.length === 0) {
            e.preventDefault();
            alert('Please select start and end dates first');
            return false;
        }
    });
});

window.onclick = function(event) {
    const modal = document.getElementById('leaveRequestModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>
</body>
</html>
