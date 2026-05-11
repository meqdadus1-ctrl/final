# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Setup from scratch
composer run setup

# Dev server (Laravel + Vite + queue + logs concurrently)
composer run dev

# Run tests
composer run test
php artisan test --filter=TestName

# Code formatting
./vendor/bin/pint

# Database
php artisan migrate
php artisan migrate:rollback
php artisan tinker

# Clear caches (do this after route/config changes)
php artisan config:clear && php artisan route:clear && php artisan view:clear
```

## Architecture Overview

Laravel 13 · PHP 8.3 · MySQL · Bootstrap 5.3 RTL (Arabic UI, `dir="rtl"`) · Font Awesome 6 · Vite + Tailwind (Tailwind is installed but most views use Bootstrap via CDN).

### Key Domain: Ledger (Employee Financial Ledger)

The financial core of the system. **All money movements must go through `LedgerService`** — never write directly to `employee_ledger`.

```
credit  = amount owed TO employee  → raises balance
debit   = amount owed BY employee  → lowers balance
balance_after = balance_before + credit - debit
```

**`LedgerService` public methods:**
- `addEntry(employeeId, type, credit, debit, description, date, extra[])` — single entry
- `recordSalaryPayment(SalaryPayment, adjIds[])` — creates multiple ledger rows for one salary
- `deleteSalaryEntries(SalaryPayment)` — removes salary ledger rows and recalculates
- `recalculateBalances(employeeId)` — rebuilds all `balance_after` values from scratch; call after any manual delete/update
- `recordPayment / recordLoanDisbursement / recordOpeningBalance` — domain-specific helpers

**`reference_type` values in `employee_ledger`:**
`SalaryPayment` | `ExcelImport` | `SalaryAdjustment` | `Loan` | `Employee` (opening balance) | `Manual`

**Important invariant:** `balance_after` on each row is denormalised running balance. After any direct DB delete/update on ledger rows, always call `recalculateBalances()`.

### Salary Calculation Flow

`SalaryController::calculate()` (preview) → `salary.review` view → `SalaryController::store()` (saves `SalaryPayment` + calls `ledger->recordSalaryPayment()`).

All monetary values are rounded to the nearest integer via `(int) round(...)`. Net salary = `(int) round(max(0, gross - deductions))`.

### Authentication & Authorisation

Two separate auth guards:
1. **Admin/staff** — standard Laravel auth (`auth` middleware) + Spatie roles/permissions
2. **Employee self-service portal** — `employee.portal` middleware (separate session key), routes under `/portal`

Permission middleware aliases (registered in `bootstrap/app.php`):
- `permission:` → Spatie PermissionMiddleware
- `role:` → Spatie RoleMiddleware
- `employee.portal` → `EmployeePortalMiddleware`

### Excel Import (Ledger)

`LedgerImportController` imports historical transactions into the ledger. Key details:
- `parseDate()` accepts `dd/mm/yyyy` strings and Excel serial numbers; rejects years outside 2000–2100 (prevents the 1899-12-30 Excel zero-date bug)
- Duplicate detection uses `reference_type = 'ExcelImport'` + `reference_id` (the Excel row ref number)
- Imported entries use `ref_number = '0'` as fallback when no ref exists

### Attendance & ZKTeco

`AttendanceController` supports:
- Manual entry
- Excel import (maps `fingerprint_id` to employees)
- ZKTeco device pull via `rats/zkteco` package — device IP stored in `.env` or config

### FCM Push Notifications

`FcmService::sendToEmployee(fcmToken, title, body)` — used in chat, salary, and department channel controllers. Requires `FIREBASE_*` env vars. Token stored on `employees.fcm_token`. Always wrap in `try/catch` — a failed push must not abort the main operation.

### Polling (No WebSockets)

Real-time features use **polling**, not WebSockets. Pattern:
- Controller exposes a `poll(Request $request, Model $model)` method
- View calls `setInterval(() => fetch('/route/poll?after={lastId}'), 4000)`
- Returns JSON `{ messages: [...] }`

Used in: `AdminChatController`, `DepartmentChannelController`.

### Department Channels

New group messaging feature at `/channels`. Messages stored in `department_messages`. Read tracking via `department_message_reads` (upsert pattern with `last_read_id`). FCM sent to all department employees except sender.

### Views

All views use `<x-app-layout>` (renders `resources/views/layouts/app.blade.php`). The layout loads Bootstrap 5.3 RTL + Font Awesome from CDN. **Do not add Tailwind utility classes to admin views** — use Bootstrap classes. Tailwind is only active for auth/portal pages.

Navigation sidebar is inline in `app.blade.php` (not a separate component). Unread badge counts are computed inline with `@php` blocks in the nav.

### API (Mobile App)

All mobile API routes live under `/api/v1/` with Sanctum token auth. Controllers in `app/Http/Controllers/Api/`. The API mirrors the web features for the employee self-service mobile app.
