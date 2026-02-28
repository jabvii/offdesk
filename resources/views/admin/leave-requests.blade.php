<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OffDesk - Admin Requests</title>
    <link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/leave-requests.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="nav-top">
            <h2>OFFDesk Admin</h2>
            <ul class="nav-links">
                <li><a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) class="active" @endif>Dashboard</a></li>
                <li>
                    <a href="{{ route('admin.leave.requests') }}" @if(request()->routeIs('admin.leave.requests')) class="active" @endif>
                        Requests
                        @if($pendingCount > 0)
                            <span class="badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.accounts') }}" @if(request()->routeIs('admin.accounts')) class="active" @endif>
                        Accounts
                        @if($pendingUsersCount > 0)
                            <span class="badge">{{ $pendingUsersCount }}</span>
                        @endif
                    </a>
                </li>
                <li><a href="{{ route('admin.add.account') }}" @if(request()->routeIs('admin.add.account')) class="active" @endif>Add Account</a></li>
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

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <h2 class="dblue">Pending Leave Requests (Awaiting Admin Approval)</h2>

            <div class="requests-table-wrapper">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Request Type</th>
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRequests as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}<br><small style="color: #888;">{{ $leave->user->department }}</small></td>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }} → {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</td>
                            <td>{{ $leave->total_days }}</td>
                            <td>
                                @if($leave->user->isManager())
                                    <span style="background: #B8956A; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Manager Request</span>
                                @elseif($leave->user->isSupervisor())
                                    <span style="background: #7A9A7D; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Supervisor Request</span>
                                @else
                                    <span style="background: #5B8DBE; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Employee Request</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-view-details" onclick="openDetailsModal({{ $leave->id }})" title="View Details"><i class="fas fa-eye"></i></button>
                            </td>
                            <td>
                                <button class="admin-action-btn action-approve" data-id="{{ $leave->id }}" data-action="approved" title="Approve"><i class="fas fa-check"></i></button>
                                <button class="admin-action-btn action-reject" data-id="{{ $leave->id }}" data-action="rejected" title="Reject"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>

                        <!-- Details Modal for this request -->
                        <div class="modal details-modal" id="detailsModal{{ $leave->id }}">
                            <div class="modal-content details-modal-content">
                                <div class="modal-header">
                                    <h2>Leave Request Details</h2>
                                    <button type="button" class="modal-close" onclick="closeDetailsModal({{ $leave->id }})">&times;</button>
                                </div>
                                
                                <div class="details-section">
                                    <h3>Employee Information</h3>
                                    <div class="details-grid">
                                        <div class="detail-item">
                                            <span class="detail-label">Name</span>
                                            <span class="detail-value">{{ $leave->user->name }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Department</span>
                                            <span class="detail-value">{{ $leave->user->department ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Email</span>
                                            <span class="detail-value">{{ $leave->user->email }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Role</span>
                                            <span class="detail-value">
                                                @if($leave->user->isManager()) Manager
                                                @elseif($leave->user->isSupervisor()) Supervisor
                                                @else Employee
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Leave Details</h3>
                                    <div class="details-grid">
                                        <div class="detail-item">
                                            <span class="detail-label">Leave Type</span>
                                            <span class="detail-value leave-type-badge {{ strtolower(str_replace(' ', '-', $leave->leaveType->name)) }}">{{ $leave->leaveType->name }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Duration</span>
                                            <span class="detail-value">{{ $leave->total_days }} day(s)</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Start Date</span>
                                            <span class="detail-value">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y (l)') }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">End Date</span>
                                            <span class="detail-value">{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y (l)') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Daily Sessions</h3>
                                    <div class="sessions-list">
                                        @php
                                            $sessions = $leave->sessions ?? collect();
                                            $sessionMap = $sessions->keyBy(fn($s) => $s->date->format('Y-m-d'));
                                            $start = \Carbon\Carbon::parse($leave->start_date);
                                            $end = \Carbon\Carbon::parse($leave->end_date);
                                        @endphp
                                        @for($date = $start->copy(); $date->lte($end); $date->addDay())
                                            @php
                                                $dateStr = $date->format('Y-m-d');
                                                $isWeekend = $date->isWeekend();
                                                $session = $sessionMap->get($dateStr);
                                                $sessionType = $session ? $session->session : 'whole_day';
                                            @endphp
                                            <div class="session-item {{ $isWeekend ? 'weekend' : '' }}">
                                                <span class="session-date">{{ $date->format('M d, Y') }} ({{ $date->format('l') }})</span>
                                                <span class="session-type">
                                                    @if($isWeekend)
                                                        <em>Weekend</em>
                                                    @else
                                                        {{ $sessionType === 'whole_day' ? 'Whole Day' : ucfirst($sessionType) }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Reason</h3>
                                    <div class="reason-box">
                                        {{ $leave->reason }}
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Request Tracking</h3>
                                    @php
                                        $isManagerRequest = $leave->user->isManager();
                                        $isSupervisorRequest = $leave->user->isSupervisor();
                                        $employeeSupervisor = $leave->user->supervisor;
                                        $employeeManager = $leave->user->manager;
                                        
                                        // For employees: show supervisor if they have one
                                        $hasSupervisorInChain = !$isManagerRequest && !$isSupervisorRequest && $employeeSupervisor;
                                        // For employees and supervisors: show manager if they have one
                                        $hasManagerInChain = !$isManagerRequest && $employeeManager;
                                        
                                        $stepNumber = 2;
                                    @endphp
                                    <div class="tracking-chain">
                                        <div class="tracking-step completed">
                                            <div class="step-icon">✓</div>
                                            <div class="step-info">
                                                <span class="step-role">
                                                    @if($isManagerRequest) Manager
                                                    @elseif($isSupervisorRequest) Supervisor
                                                    @else Employee
                                                    @endif
                                                </span>
                                                <span class="step-name">{{ $leave->user->name }}</span>
                                                <span class="step-detail">Submitted {{ $leave->created_at->format('M d, Y h:i A') }}</span>
                                            </div>
                                        </div>

                                        @if($hasSupervisorInChain)
                                        @php
                                            $isBypassed = $leave->supervisor_remarks === 'Bypassed by manager';
                                        @endphp
                                        <div class="chain-connector {{ $isBypassed ? 'bypassed' : 'completed' }}"></div>
                                        <div class="tracking-step {{ $isBypassed ? 'bypassed' : 'completed' }}">
                                            <div class="step-icon" style="{{ $isBypassed ? 'color: #f39c12;' : '' }}">{{ $isBypassed ? '●' : '✓' }}</div>
                                            <div class="step-info">
                                                <span class="step-role">Supervisor</span>
                                                <span class="step-name">{{ $employeeSupervisor->name }}</span>
                                                <span class="step-detail">
                                                    @if($isBypassed)
                                                        Bypassed @if($leave->supervisor_approved_at) {{ \Carbon\Carbon::parse($leave->supervisor_approved_at)->format('M d, Y h:i A') }} @endif
                                                    @else
                                                        Approved @if($leave->supervisor_approved_at) on {{ \Carbon\Carbon::parse($leave->supervisor_approved_at)->format('M d, Y h:i A') }} @endif
                                                    @endif
                                                </span>
                                                @if($leave->supervisor_remarks)
                                                    <span class="step-remarks" style="{{ $isBypassed ? 'color: #f39c12;' : '' }}">"{{ $leave->supervisor_remarks }}"</span>
                                                @endif
                                            </div>
                                        </div>
                                        @php $stepNumber++; @endphp
                                        @endif

                                        @if($hasManagerInChain)
                                        <div class="chain-connector completed"></div>
                                        <div class="tracking-step completed">
                                            <div class="step-icon">✓</div>
                                            <div class="step-info">
                                                <span class="step-role">Manager</span>
                                                <span class="step-name">{{ $employeeManager->name }}</span>
                                                <span class="step-detail">
                                                    Approved @if($leave->manager_approved_at) on {{ \Carbon\Carbon::parse($leave->manager_approved_at)->format('M d, Y h:i A') }} @endif
                                                </span>
                                                @if($leave->manager_remarks)
                                                    <span class="step-remarks">"{{ $leave->manager_remarks }}"</span>
                                                @endif
                                            </div>
                                        </div>
                                        @php $stepNumber++; @endphp
                                        @endif

                                        <div class="chain-connector current"></div>
                                        <div class="tracking-step current">
                                            <div class="step-icon">●</div>
                                            <div class="step-info">
                                                <span class="step-role">Admin (You)</span>
                                                <span class="step-name">Final Approval</span>
                                                <span class="step-detail pending-text">Awaiting your decision</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="details-section">
                                    <h3>Submission Info</h3>
                                    <p class="submission-info">Submitted on {{ $leave->created_at->format('F d, Y \a\t h:i A') }}</p>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onclick="closeDetailsModal({{ $leave->id }})">Close</button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7">No pending leave requests 🎉</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Admin Action Modal -->
<div id="adminActionModal" class="modal">
    <div class="modal-content">
        <h3 id="adminActionTitle"></h3>

        <form id="adminActionForm" method="POST">
            @csrf
            <input type="hidden" name="status" id="adminActionStatus">

            <select name="admin_remarks" id="adminRemarks" class="remarks-select" required>
                <option value="">-- Select Remarks --</option>
                <optgroup label="Approval" id="approvalOptions">
                    <option value="Approved as requested.">Approved as requested.</option>
                    <option value="Leave approved. Enjoy your time off.">Leave approved. Enjoy your time off.</option>
                    <option value="Approved. Please ensure proper handover.">Approved. Please ensure proper handover.</option>
                </optgroup>
                <optgroup label="Rejection" id="rejectionOptions">
                    <option value="Insufficient leave balance.">Insufficient leave balance.</option>
                    <option value="Request conflicts with company schedule.">Request conflicts with company schedule.</option>
                    <option value="Peak period - leave not permitted.">Peak period - leave not permitted.</option>
                    <option value="Please resubmit with correct dates.">Please resubmit with correct dates.</option>
                </optgroup>
            </select>

            <div class="modal-actions">
                <button type="button" class="conf-btn">Confirm</button>
                <button type="button" class="canc-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Details Modal functions
function openDetailsModal(id) {
    document.getElementById('detailsModal' + id).classList.add('active');
}

function closeDetailsModal(id) {
    document.getElementById('detailsModal' + id).classList.remove('active');
}

// Close details modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('details-modal')) {
        e.target.classList.remove('active');
    }
});

function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('adminActionModal');
    const modalTitle = document.getElementById('adminActionTitle');
    const form = document.getElementById('adminActionForm');
    const statusInput = document.getElementById('adminActionStatus');
    const remarks = document.getElementById('adminRemarks');
    const approvalOptions = document.getElementById('approvalOptions');
    const rejectionOptions = document.getElementById('rejectionOptions');

    // Handle Approve/Reject buttons
    document.querySelectorAll('.admin-action-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action; // approved / rejected
            const leaveId = this.dataset.id;

            modalTitle.textContent = action === 'approved' ? 'Approve Leave Request' : 'Reject Leave Request';
            statusInput.value = action;

            // form URL matches route in web.php
            form.action = `/admin/employee/leave-requests/${leaveId}/decision`;

            remarks.value = '';
            
            // Show/hide relevant options based on action
            if (action === 'approved') {
                approvalOptions.style.display = '';
                rejectionOptions.style.display = 'none';
            } else {
                approvalOptions.style.display = 'none';
                rejectionOptions.style.display = '';
            }
            
            modal.style.display = 'flex';
        });
    });

    document.querySelector('.conf-btn').addEventListener('click', function() {
        if(remarks.value.trim() === '') {
            alert('Please select remarks before confirming.');
            return;
        }
        form.submit();
    });

    document.querySelector('.canc-btn').addEventListener('click', function() {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', function(e) {
        if(e.target === modal) modal.style.display = 'none';
    });

    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape') {
            modal.style.display = 'none';
            document.querySelectorAll('.details-modal.active').forEach(m => m.classList.remove('active'));
        }
    });
});
</script>

</body>
</html>