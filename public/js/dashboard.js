// Leave Request Modal Management
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('leaveRequestModal');
    const leaveForm = modal ? modal.querySelector('form') : null;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const reasonTextarea = document.getElementById('reason');

    // Modal helper
    function openModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    window.closeModal = closeModal;

    // modal button
    const openBtn = document.getElementById('openLeaveModalLink');
    if (openBtn) {
        openBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openModal();
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    // validation
    if (leaveForm) {
        leaveForm.addEventListener('submit', function (e) {
            const leaveType = document.getElementById('leave_type_id')?.value;
            const startDate = startDateInput?.value;
            const endDate = endDateInput?.value;
            const reason = reasonTextarea?.value.trim();

            if (!leaveType) {
                e.preventDefault();
                alert('Please select a leave type');
                return;
            }

            if (!startDate || !endDate) {
                e.preventDefault();
                alert('Please select both start and end dates');
                return;
            }

            if (new Date(endDate) < new Date(startDate)) {
                e.preventDefault();
                alert('End date cannot be before start date');
                return;
            }

            if (!reason) {
                e.preventDefault();
                alert('Please provide a reason for leave');
                return;
            }

            if (reason.length > 500) {
                e.preventDefault();
                alert('Reason cannot exceed 500 characters');
                return;
            }
        });
    }

    // End date minimum = start date
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function () {
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });
    }

    // alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    function calculateDays() {
        if (!startDateInput.value || !endDateInput.value) return;
        const days = countBusinessDays(
            new Date(startDateInput.value),
            new Date(endDateInput.value)
        );
        updateDaysDisplay(days);
    }

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', calculateDays);
        endDateInput.addEventListener('change', calculateDays);
    }

    // Cancel leave confirmation + calendar update
    document.querySelectorAll('form[action*="cancel"]').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to cancel this leave request?')) {
                e.preventDefault();
                return;
            }

            // Remove the dot from the calendar (simple DOM method)
            const leaveId = form.dataset.leaveId;
            if (leaveId) {
                const dot = document.querySelector(`.calendar-dot[data-leave-id="${leaveId}"]`);
                if (dot) dot.remove();
            }

            // If using FullCalendar
            if (typeof calendar !== 'undefined' && leaveId) {
                const event = calendar.getEventById(leaveId);
                if (event) event.remove();
            }
        });
    });

    // Reason character counter
    if (reasonTextarea) {
        const maxLength = 500;
        const counter = document.createElement('div');
        counter.style.cssText =
            'text-align:right;font-size:12px;color:#7f8c8d;margin-top:4px;';
        counter.textContent = `0 / ${maxLength}`;
        reasonTextarea.parentElement.appendChild(counter);

        reasonTextarea.addEventListener('input', function () {
            const length = this.value.length;
            counter.textContent = `${length} / ${maxLength}`;

            if (length > maxLength) counter.style.color = '#e74c3c';
            else if (length > maxLength * 0.9) counter.style.color = '#f39c12';
            else counter.style.color = '#7f8c8d';
        });
    }
});

// count business days
function countBusinessDays(startDate, endDate) {
    let count = 0;
    const current = new Date(startDate);
    while (current <= endDate) {
        const dayOfWeek = current.getDay();
        if (dayOfWeek !== 0 && dayOfWeek !== 6) count++;
        current.setDate(current.getDate() + 1);
    }
    return count;
}

// business day display
function updateDaysDisplay(days) {
    let daysDisplay = document.getElementById('daysDisplay');
    if (!daysDisplay) {
        daysDisplay = document.createElement('div');
        daysDisplay.id = 'daysDisplay';
        daysDisplay.style.cssText =
            'margin-top: 8px; color: #3498db; font-size: 14px; font-weight: 600;';
        const endDateGroup = document.getElementById('end_date').parentElement;
        endDateGroup.appendChild(daysDisplay);
    }
    daysDisplay.textContent = `Total business days: ${days}`;
}

// Attach confirm only using classes
document.querySelectorAll('.accept-form, .reject-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const confirmed = confirm('Are you sure you want to proceed?');
        if(!confirmed) e.preventDefault();
    });
});

function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}