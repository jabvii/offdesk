# CSS Structure Reorganization Documentation

## Overview
The CSS files have been reorganized from a flat, monolithic structure into a well-organized directory structure that mirrors the views folder layout. This improves maintainability, scalability, and makes it easier to manage styles for different parts of the application.

## New CSS Structure

```
public/css/
├── app.css (existing - Tailwind CSS configuration)
├── auth.css (deprecated - old file, use css/auth/auth.css)
├── dashboard.css (deprecated - old file, no longer used)
├── shared/
│   ├── globals.css - Global resets, base styles, and universal elements
│   ├── sidebar.css - Sidebar navigation and related styles
│   ├── buttons.css - All button styling (primary, danger, secondary, admin buttons)
│   ├── alerts.css - Alert messages styling
│   ├── modals.css - Modal dialogs and popups
│   └── forms.css - Form elements, inputs, selects, form groups
├── auth/
│   └── auth.css - Login and register page specific styles
├── employee/
│   ├── dashboard.css - Employee dashboard specific styles (balance cards, calendar)
│   └── leave-history.css - Employee leave history page styles
├── manager/
│   ├── dashboard.css - Manager dashboard styles (balance cards, calendar)
│   └── leave-requests.css - Manager leave requests handling styles
└── admin/
    ├── dashboard.css - Admin dashboard specific styles (stats, cards)
    ├── leave-requests.css - Admin leave requests management
    └── accounts.css - Admin accounts/user management
```

## CSS File Organization by Purpose

### Shared Files (public/css/shared/)
These files contain shared/reusable styles used across multiple pages:

- **globals.css**: Reset styles, base element styling, container, dashboard layout wrapper
- **sidebar.css**: Navigation sidebar, nav-links, logo area
- **buttons.css**: All button variants (.btn, .btn-primary, .btn-danger, etc.)
- **alerts.css**: Alert boxes and notification styling
- **modals.css**: Modal windows, popups, dialog boxes
- **forms.css**: Form groups, inputs, selects, labels, checkboxes, textareas

### Feature-Specific Files

**Auth (public/css/auth/)**
- `auth.css`: Login and register page styles only

**Employee (public/css/employee/)**
- `dashboard.css`: Balance cards, calendar, leave dots, weekend indicators
- `leave-history.css`: Leave history list, filtering, pagination

**Manager (public/css/manager/)**
- `dashboard.css`: Balance cards, calendar (mirrors employee dashboard)
- `leave-requests.css`: Request table, status badges, handling UI

**Admin (public/css/admin/)**
- `dashboard.css`: Stats grid, admin cards, custom admin UI
- `leave-requests.css**: Admin leave request approval interface
- `accounts.css`: User account management, user tables

## Blade File CSS Link Updates

All blade files have been updated to include the new CSS paths. Here's the pattern:

### Employee Pages
```html
<link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/employee/dashboard.css') }}">
```

### Auth Pages (Login/Register)
```html
<link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
```

### Manager Pages
```html
<link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/manager/dashboard.css') }}">
// or
<link rel="stylesheet" href="{{ asset('css/manager/leave-requests.css') }}">
```

### Admin Pages
```html
<link rel="stylesheet" href="{{ asset('css/shared/globals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/alerts.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/modals.css') }}">
<link rel="stylesheet" href="{{ asset('css/shared/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
// or
<link rel="stylesheet" href="{{ asset('css/admin/leave-requests.css') }}">
// or
<link rel="stylesheet" href="{{ asset('css/admin/accounts.css') }}">
```

## Files Updated

### New CSS Files Created
- public/css/shared/globals.css
- public/css/shared/sidebar.css
- public/css/shared/buttons.css
- public/css/shared/alerts.css
- public/css/shared/modals.css
- public/css/shared/forms.css
- public/css/employee/dashboard.css
- public/css/employee/leave-history.css
- public/css/manager/dashboard.css
- public/css/manager/leave-requests.css
- public/css/auth/auth.css

### Blade Files Updated (CSS links only)
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- resources/views/employee/dashboard.blade.php
- resources/views/employee/leave-history.blade.php
- resources/views/manager/dashboard.blade.php
- resources/views/manager/leave-requests.blade.php
- resources/views/admin/dashboard.blade.php
- resources/views/admin/leave-requests.blade.php
- resources/views/admin/accounts.blade.php
- resources/views/admin/approved_accounts.blade.php
- resources/views/admin/add-account.blade.php

## Deprecation Notice

The following files are now deprecated and should not be used:
- `public/css/auth.css` (old) → Use `public/css/auth/auth.css` instead
- `public/css/dashboard.css` (old) → Use role-specific files in `public/css/employee/`, `public/css/manager/`, `public/css/admin/`

These old files can be safely deleted after verifying all pages load correctly.

## Benefits of This Structure

1. **Better Organization**: CSS grouped by feature/view, making it easier to find relevant styles
2. **Reduced CSS Bloat**: Each page loads only the CSS it needs
3. **Maintainability**: Changes to one feature's styles are isolated to that feature's CSS
4. **Scalability**: Easy to add new roles or features with dedicated CSS files
5. **Code Reusability**: Shared styles clearly separated from feature-specific styles
6. **Preview Functionality**: Following views folder structure makes it intuitive where to find CSS
7. **Performance**: Smaller CSS files can potentially be better cached

## No Visual Changes

The UI appearance remains **exactly the same** - this is a pure organizational restructuring of CSS files with no modifications to styles or functionality.

## Testing Checklist

- [ ] Employee Dashboard loads correctly with proper styling
- [ ] Employee Leave History page renders without CSS issues
- [ ] Manager Dashboard works as expected
- [ ] Manager Leave Requests shows proper styling
- [ ] Admin Dashboard displays correctly
- [ ] Admin Leave Requests interface functional
- [ ] Admin Accounts/Users pages display correctly
- [ ] Login and Register pages render properly
- [ ] All buttons, forms, alerts, and modals display correctly
- [ ] Calendar and leave indicators visible and styled properly
- [ ] Responsive design works on mobile/tablet (768px breakpoint)
- [ ] Hover effects functional on all interactive elements
- [ ] No console errors related to CSS loading
