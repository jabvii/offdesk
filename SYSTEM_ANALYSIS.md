# OffDesk Leave Request System - Architecture Analysis

## 📋 Overview
This is a Laravel-based **Leave Request Management System** with role-based access control. Employees can request leave, managers approve/reject, and admins manage the entire system including user accounts and leave policies.

---

## 🏗️ Database Schema & Model Relationships

### Entity Relationship Diagram

```
┌─────────────────┐
│     Users       │ (Employees, Managers, Admins)
├─────────────────┤
│ id (PK)         │
│ name            │
│ email (unique)  │◄──────┐
│ password        │       │
│ status          │       │
│ role            │       │
│ department      │       │
│ manager_id (FK) ├─────┐ │
│ is_admin        │     │ │
│ admin_remarks   │     │ │
│ created_at      │     │ │
└─────────────────┘     │ │
         │              │ │
         │   (owns)      │ │
         ▼              │ │
┌─────────────────────┐ │ │
│  LeaveRequest       │ │ │
├─────────────────────┤ │ │
│ id (PK)             │ │ │
│ user_id (FK) ┬──────┘ │ │
│ leave_type_id (FK)  │ │ (manager_id points to)
│ start_date          │ │
│ end_date            │ │
│ total_days          │ │
│ reason              │ │
│ status              │ │
│ manager_remarks     │ │
│ admin_remarks       │ │
│ manager_id (FK)─────┘
│ created_at          │
└─────────────────────┘
         │
         │ (has many)
         ▼
┌──────────────────────────┐
│ LeaveRequestSession      │
├──────────────────────────┤
│ id (PK)                  │
│ leave_request_id (FK)    │
│ date                     │
│ session (whole_day|....) │
│ created_at               │
└──────────────────────────┘


┌──────────────────┐
│   LeaveType      │
├──────────────────┤
│ id (PK)          │
│ name             │
│ code (unique)    │
│ max_days         │
│ created_at       │
└──────────────────┘
    ▲       ▲
    │       │ (referenced by)
    │       │
    └─┬─────┘
      │
      │ (has many)
      ▼
┌──────────────────────┐
│  LeaveBalance        │
├──────────────────────┤
│ id (PK)              │
│ user_id (FK)         │
│ leave_type_id (FK)   │
│ total_credits        │
│ used_credits         │
│ pending_credits      │
│ year                 │
│ created_at           │
│ unique(user_id,      │
│   leave_type_id,     │
│   year)              │
└──────────────────────┘
```

---

## 🔑 Key Models & Relationships

### 1. **User Model** (`app/Models/User.php`)
**Roles:** Employee, Manager, Admin, Technical

| Relationship | Type | Description |
|---|---|---|
| `subordinates()` | hasMany | Employees managed by this user |
| `manager()` | belongsTo | The user's manager |
| `leaveRequests()` | hasMany | Leave requests created by user |

**Role-based Methods:**
- `isManager()`, `isAdmin()`, `isTechnical()`, `isEmployee()`
- `canApproveLeaves()` - Managers, Admins, Technical
- `canManageUsers()` - Admins, Technical
- `canChangeRoles()` - Technical only

**User Status:** `pending | approved | rejected`

---

### 2. **LeaveRequest Model** (`app/Models/LeaveRequest.php`)
Represents a leave application from an employee.

| Relationship | Type | Description |
|---|---|---|
| `user()` | belongsTo | Employee requesting leave |
| `leaveType()` | belongsTo | Type of leave (Sick, Casual, etc.) |
| `manager()` | belongsTo | Manager assigned to review |
| `sessions()` | hasMany | Daily sessions (whole_day/morning/afternoon) |

**Status Flow:**
```
pending → pending_manager → pending_admin → approved/rejected
                ↓ (by manager)      ↓ (by admin)
            approved/rejected    approved/rejected
            (final for manager)  (final for system)
```

**Helper Methods:**
- `isPendingManagerReview()`, `isPendingAdminReview()`, `isFinal()`
- `calculateTotalDaysFromSessions()` - Sum of all session day values

---

### 3. **LeaveRequestSession Model** (`app/Models/LeaveRequestSession.php`)
Tracks individual sessions for each day in a leave request.

**Unique Constraint:** One session per day per leave request

**Session Types:**
- `whole_day` = 1 day credit
- `morning` or `afternoon` = 0.5 day credit

---

### 4. **LeaveBalance Model** (`app/Models/LeaveBalance.php`)
Tracks entitlement and usage per leave type per year.

| Field | Purpose |
|---|---|
| `total_credits` | Annual allocation (from LeaveType.max_days) |
| `used_credits` | Approved leave days deducted |
| `pending_credits` | Days in pending leave requests |
| `available_credits` | `total - used - pending` (calculated attribute) |
| `year` | Year of balance |

**Unique Constraint:** One balance per user + leave type + year

---

### 5. **LeaveType Model** (`app/Models/LeaveType.php`)
Defines types of leave and their limits.

**Examples:**
- Casual Leave (max: 12 days/year)
- Sick Leave (max: 10 days/year)
- Annual Leave (max: 20 days/year)

---

## 🔄 Request Flow & Approval Workflow

### Leave Request Lifecycle

```
EMPLOYEE CREATES REQUEST
        ↓
   [store()]
        ↓
  Sessions created (daily details)
        ↓
  Status: pending_manager
        ↓
MANAGER REVIEW (ManagerController::decision)
        ├→ ✓ Approve → Status: pending_admin
        └→ ✗ Reject → Status: rejected (FINAL)
        ↓
ADMIN REVIEW (AdminController::decision)
        ├→ ✓ Approve → Status: approved (FINAL)
        │              Update LeaveBalance.used_credits
        └→ ✗ Reject → Status: rejected (FINAL)
```

---

## 👥 User Roles & Permissions

| Role | Permissions | Workflow Role |
|---|---|---|
| **Employee** | Create leave requests, view own balance & history | Initiator |
| **Manager** | View subordinates' leave requests, approve/reject (first level) | First Approver |
| **Admin** | Manage users, approve/reject leave (final level), view all data | Final Approver & System Admin |
| **Technical** | Like Admin but may have extended capabilities | Admin-equivalent |

---

## 🛣️ Route Structure

### Authentication Routes
```
GET  /login              → Login form
POST /login              → Process login
GET  /register           → Register form
POST /register           → Process registration
POST /logout             → Logout (auth required)
```

### Employee Routes (auth)
```
GET  /employee/dashboard        → View own leave balance & requests
POST /leave                     → Create leave request
POST /leave/{id}/cancel         → Cancel pending request
```

### Manager Routes (auth + ManagerMiddleware)
```
GET  /manager/dashboard              → Manager dashboard
GET  /manager/leave-requests         → View subordinates' pending requests
POST /manager/leave-requests/{id}/decision → Approve/reject with remarks
GET  /manager/leave-requests/{id}/sessions → Get sessions as JSON
```

### Admin Routes (auth + AdminMiddleware)
```
GET  /admin/employee/dashboard       → Admin overview dashboard
GET  /admin/employee/leave-requests  → View all pending requests
POST /admin/employee/leave-requests/{id}/decision → Admin approval decision
GET  /admin/employee/leave-requests/{id}/sessions → Get sessions as JSON

GET  /admin/accounts                 → New user registrations pending approval
POST /admin/users/{id}/approve       → Approve user account
POST /admin/users/{id}/reject        → Reject user account
GET  /admin/approved                 → View approved employees
```

---

## 🎯 Key Controllers

### **LeaveRequestController** (Employee)
- `index()` - Dashboard: calculates balances, fetches user's leave requests
- `store()` - Create new leave request with sessions
- `cancel()` - Cancel pending request

### **ManagerController**
- `dashboard()` - Manager overview with subordinate stats
- `leaveRequests()` - List subordinates' pending requests
- `decision()` - Approve/reject with remarks
- `getLeaveRequestSessions()` - Return sessions as JSON

### **AdminController**
- `index()` - Dashboard with system metrics
- `leaveRequests()` - All pending admin-level requests
- `decision()` - Final approval/rejection
- `accounts()` - Pending user registrations
- `approveUser()` / `rejectUser()` - User account approval
- `approvedEmployees()` - List approved users

---

## 📊 Business Logic Highlights

### Leave Balance Calculation
1. **Initial Balance**: Created automatically in `LeaveRequestController::index()` with `total_credits` = `LeaveType.max_days`
2. **Pending Balance**: When request status = `pending_admin`, `pending_credits` increases
3. **Used Balance**: When request approved, `used_credits` increases by calculated total_days
4. **Available**: `available_credits = total_credits - used_credits - pending_credits`

### Session-Based Day Calculation
- Employees select session type for each day in date range
- Sessions stored in `LeaveRequestSession` table
- `getDayValue()`: whole_day = 1, morning/afternoon = 0.5
- `calculateTotalDaysFromSessions()`: Sum all session values

### Hierarchical Approval
- **First Level**: Manager reviews leaves from assigned employees (manager_id match)
- **Second Level**: Admin reviews all manager-approved leaves
- **Dual Remarks**: Both manager_remarks and admin_remarks stored
- **Status Enum**: Tracks stage in approval pipeline

---

## 🔐 Security Features

1. **Role-based Access Control (RBAC)**
   - Middleware guards (AdminMiddleware, ManagerMiddleware)
   - Role checking in User model methods

2. **Organizational Hierarchy**
   - `manager_id` creates reporting line
   - Managers only see subordinates' requests
   - Self-referential relationship possible

3. **Status Guards**
   - Only pending requests visible to managers
   - Only pending_admin requests visible to admins
   - Only own requests visible to employees (except filtering)

4. **Approval Workflow Safety**
   - Two-level approval prevents single-person authority
   - Remarks required at each stage
   - Status immutable after final decision

---

## 📝 Evolution Notes

The system evolved from simple to complex:
1. Started with basic Leave Request table
2. Added sessions for half-day tracking
3. Extended user model with roles and manager hierarchy
4. Implemented two-level (manager + admin) approval workflow
5. Added user account approval process
6. Refined status enum to track approval stage (pending_manager vs pending_admin)

---

## 🔗 Cross-cutting Concerns

| Concern | Implementation |
|---|---|
| **Current Year Auto-Init** | LeaveBalance created on-demand in controller index methods |
| **Unique Constraints** | (user_id, leave_type_id, year) on LeaveBalance; (leave_request_id, date) on Sessions |
| **Cascading Deletes** | User deletion cascades to LeaveRequest and LeaveBalance |
| **Null Handling** | manager_id nullable; null on manager deletion (SET NULL) |

---

## 📋 Summary Table

| Aspect | Details |
|---|---|
| **Core Entities** | 5 models (User, LeaveRequest, Session, Balance, Type) |
| **Roles** | 4 types (Employee, Manager, Admin, Technical) |
| **Status States** | 6 (pending → pending_manager → pending_admin → approved/rejected/cancelled) |
| **Approval Levels** | 2 (Manager → Admin) |
| **Granularity** | Day-level with half-day support via sessions |
| **Time Scope** | Annual balance per leave type |
