---
name: laravel react inertia starter agent
description: Agent for Laravel React Inertia Starter - Multi-Gate Authentication System (Admin & Peoples)
---
# Laravel React Inertia Starter Agent

## Copilot Guide

- **[copilot-instructions](.github/copilot-instructions.md)** - General AI Agent Instructions (Main Reference)
- **[Application Instructions](.github/instructions/application.instructions.md)** - High-level architecture, tech stack, and development workflow
- **[Backend Instructions](.github/instructions/backend.instructions.md)** - Backend development: Actions, Controllers, Policies, Models
- **[Frontend Instructions](.github/instructions/frontend.instructions.md)** - Frontend development: React, TypeScript, Inertia.js, Components, Layouts
- **[Security & Testing Instructions](.github/instructions/security-test.instructions.md)** - Multi-gate authentication, security practices, and testing patterns
- **[CRUD Generation Instructions](.github/instructions/crud.instructions.md)** - Auto-generate CRUD features from chat commands
- **[Documentation Instructions](.github/instructions/documentation.instructions.md)** - Code documentation, API docs, and user guides
- **[Laravel Instructions](.github/instructions/laravel.instructions.md)** - Laravel patterns, conventions, and code quality standards


## Quick Commands

- **Development**: `npm run dev` (Vite), `composer run dev` (alias)
- **Testing**: `composer run test`, `php artisan test --filter=testName`
- **Quality**: `composer run checks` (all), `npm run checks` (frontend)
- **Build**: `npm run build`, `php artisan migrate:fresh --seed`
<!-- - **Debug**: `/horizon` (queues), `/telescope` (debug dashboard) -->

## Laravel Boost Tools (MCP)

This project uses Laravel Boost MCP server with powerful development tools:

- **`search-docs`** - Search version-specific Laravel ecosystem documentation (Laravel, Inertia, Pest, Tailwind, etc.)
- **`tinker`** - Execute PHP code in Laravel application context (like `php artisan tinker`)
- **`database-query`** - Run read-only SQL queries against the configured database
- **`browser-logs`** - Read browser console logs, errors, and exceptions for frontend debugging
- **`get-absolute-url`** - Generate correct URLs for Laravel Herd environment
- **`application-info`** - Get comprehensive app info (PHP version, packages, models)
- **`list-routes`** - List all available routes with filters
- **`list-artisan-commands`** - List all available Artisan commands
- **`read-log-entries`** - Read application log entries
- **`last-error`** - Get details of the last backend error/exception

## Build/Test Commands

- `composer run test` - Run all tests with Pest (parallel, compact output)
- `php artisan test --filter=testName` - Run specific test by name
- `php artisan test tests/Feature/ExampleTest.php` - Run specific test file
- `php artisan test --coverage` - Run tests with coverage report
- `composer run pint` - Format PHP code with Laravel Pint
- `composer run stan` - Run PHPStan static analysis (3GB memory limit)
- `composer run rector` - Run Rector code refactoring
- `composer run coverage` - Run tests with 90% minimum coverage
- `composer run type-coverage` - Check 100% type coverage
- `composer run checks` - Run all quality checks (rector, pint, stan, coverage, type-coverage, test)
- `npm run lint` - Lint TypeScript/React code with ESLint
- `npm run format` - Format code with Prettier
- `npm run checks` - Run frontend linting and formatting
- `npm run dev` - Start Vite dev server with HMR
- `npm run build` - Build frontend assets for production

## Testing Patterns

### Test Creation

- **Feature Tests**: `php artisan make:test --pest FeatureName` (most common)
- **Unit Tests**: `php artisan make:test --pest --unit UnitName`
- **Browser Tests**: `php artisan make:test --pest --browser BrowserName` (Pest v4)
- Tests auto-use `RefreshDatabase` trait and factories

### Test Execution

- **All tests**: `php artisan test`
- **Specific test**: `php artisan test --filter=testName`
- **Test file**: `php artisan test tests/Feature/ExampleTest.php`
- **Parallel**: Tests run in parallel by default for speed
- **Coverage**: `composer run coverage` (90% minimum required)

### Browser Testing (Pest v4)

- Browser tests in `tests/Browser/`
- Use `visit('/path')` to navigate
- Interact with page: `click()`, `type()`, `fill()`, `select()`
- Assert content: `assertSee()`, `assertNoJavascriptErrors()`
- Laravel features work: `Event::fake()`, `assertAuthenticated()`, factories
- Take screenshots: `screenshot()` for debugging

### Test Data

- Use factories: `User::factory()->create()`, `User::factory()->make()`
- Check factory states before creating custom data
- Use `fake()` or `$this->faker` for random data (follow existing patterns)
- Datasets for validation testing: `->with(['email1', 'email2'])`

## Architecture Patterns

### Actions (Business Logic)

- Location: `app/Actions/{Feature}/{Action}Action.php`
- Single-purpose classes for business operations organized by feature
- Use constructor injection for dependencies
- Return DTOs or models, not arrays

#### Why gate-based layout?

The audit showed that many actions are gate-specific (Admin vs Student) or shared across gates. Moving actions under a gate-first layout makes intent explicit, simplifies policy and FormRequest mappings, and reduces accidental cross-gate imports when migrating code.

#### Action Structure by Gate (recommended):
```
app/Actions/
├── Admin/
│   ├── User/
│   │   ├── CreateUserAction.php
│   │   ├── UpdateUserAction.php
│   │   └── DeleteUserAction.php
│   ├── Peoples/
│   │   ├── CreatePeopleAction.php
│   │   ├── UpdatePeopleAction.php
│   │   └── DeletePeopleAction.php
│   ├── {Feature}/
│   │   ├── Create{Feature}Action.php
│   │   └── Update{Feature}Action.php
│   │   └── Delete{Feature}Action.php
│   └── Profile/
│       ├── UpdateProfileAction.php
│       └── ChangePasswordAction.php
├── Peoples/
│   ├── Attendance/
│   │   ├── RecordAttendanceAction.php
│   │   └── BulkRecordAttendanceAction.php
│   └── Profile/
│       └── EditProfileAction.php
└── Shared/
    ├── Email/
    │   └── SendNotificationAction.php
    ├── Export/
    │   └── ExportToExcelAction.php
    └── Upload/
        └── ProcessImageUploadAction.php
```

Notes:
- Keep action classes single responsibility and named `*Action`.
- Prefer `Shared` for actions consumed by multiple gates (e.g., email, exports, uploads).
- Admin should own actions for user management, {feature} management.
- Peoples should own actions for profile management and limited feature access.

#### Mapping (examples from the project)
- **Admin Actions**:
  - `App\Actions\Admin\User\CreateUserAction` - Create admin user
  - `App\Actions\Admin\User\UpdateUserAction` - Update admin user
  - `App\Actions\Admin\User\DeleteUserAction` - Delete admin user
  - `App\Actions\Admin\Peoples\CreatePeopleAction` - Create people (admin)
  - `App\Actions\Admin\Peoples\UpdatePeopleAction` - Update people (admin)
  - `App\Actions\Admin\Peoples\DeletePeopleAction` - Delete people (admin)
  - `App\Actions\Admin\{Feature}\Create{Feature}Action` - Create feature entity
  - `App\Actions\Admin\{Feature}\Update{Feature}Action` - Update feature entity
  - `App\Actions\Admin\{Feature}\Delete{Feature}Action` - Delete feature entity

- **Peoples Actions**:
  - `App\Actions\Peoples\Profile\UpdateProfileAction` - Update own profile
  - `App\Actions\Peoples\{Feature}\Create{Feature}Action` - Create feature entity (if allowed)
  - `App\Actions\Peoples\{Feature}\Update{Feature}Action` - Update feature entity (if allowed)

- **Shared Actions**:
  - `App\Actions\Shared\Email\SendNotificationAction` - Email notifications
  - `App\Actions\Shared\Export\ExportToExcelAction` - Excel export
  - `App\Actions\Shared\Upload\ProcessImageUploadAction` - Image upload

If an action is used by multiple gates, prefer placing it under `Shared/` and create gate-specific wrappers only when business logic differs.

#### Usage Pattern (controller constructor injection)
```php
use App\Actions\Admin\User\CreateUserAction;
use App\Actions\Admin\User\UpdateUserAction;

public function __construct(
    private readonly CreateUserAction $createUserAction,
    private readonly UpdateUserAction $updateUserAction,
) {}

public function store(StoreUserRequest $request): RedirectResponse
{
    $this->authorize('create', User::class);

    try {
        $user = $this->createUserAction->execute(
            userData: $request->validated(),
            imageFile: $request->file('image')
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat!');
    } catch (\Exception $e) {
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}
```

#### Migration guidance (small, safe steps)
1. Pick a small set of clearly-scoped actions (3–6) from the audit that are unambiguously Admin-only or Shared.
2. Move files into the gate folder, update namespaces, run `composer dump-autoload`.
3. Update imports in controllers/tests that reference them.
4. Run targeted tests and static analysis (PHPStan/Pint) before moving more files.

This layout keeps intent explicit and makes future gate-level refactors predictable.


#### Feature-Based Organization Benefits:
- **Logical Grouping**: Related actions grouped together
- **Easier Navigation**: Clear feature-based structure
- **Scalability**: Easy to add new features without cluttering
- **Maintainability**: Changes isolated to specific feature folders
- **Testing**: Tests organized by feature in `tests/Unit/Actions/{Feature}/`

### Form Requests (Validation)

- **Location Pattern**: `app/Http/Requests/{Gate}/{Feature}/{Update/Store}{Feature}Request`
- **Gate-Specific Structure**: 
  - **Admin**: `app/Http/Requests/Admin/User/StoreUserRequest.php`
  - **Peoples**: `app/Http/Requests/Peoples/Profile/UpdateProfileRequest.php`
- All validation in Form Request classes, not controllers
- **Messages**: Indonesian language (Bahasa Indonesia Baku) for user-facing content
- **Cross-Gate Email Validation** - prevent duplicate emails across all gates:
  ```php
  'email' => [
      'required',
      'string',
      'email', 
      'max:255',
      new UniqueEmailAcrossGates('users', $userId), // Checks all authentication tables
  ],
  ```
- **Gate-Specific Authorization**:
  ```php
  public function authorize(): bool
  {
      return $this->user()->can('create', People::class); // Gate-specific policy
  }
  ```
- **Required Methods**: `rules()`, `attributes()`, `messages()`
- Check siblings for array vs string validation rule patterns

### Three-Gate Development Patterns

#### When Creating New Features

**Backend Pattern:**
1. **Controller**: `app/Http/Controllers/{Gate}/{Feature}Controller.php`
2. **Form Requests**: `app/Http/Requests/{Gate}/{Feature}/`
3. **Actions**: `app/Actions/{Gate}/{Feature}/` or `app/Actions/Shared/{Feature}/`
4. **Policies**: Gate-specific authorization in existing policies
5. **Routes**: Add to appropriate `routes/{gate}.php` or `routes/web.php` (public)

**Frontend Pattern:**
1. **Pages**: `resources/js/pages/{gate}/{feature}/`
2. **Layout**: Use gate-appropriate layout (`admin-layout`, `peoples-layout`, `site-layout`)
3. **Components**: Reuse `@/components/ui/` across gates
4. **Entry Point**: Ensure correct Vite entry (`admin.tsx`, `peoples.tsx`, `site.tsx`)

#### Gate-Specific UI Guidelines

**Admin Portal (Desktop-First):**
- Rich data tables with sorting, filtering, pagination
- Sidebar navigation with collapsible sections
- Complex forms with multiple steps
- Export/import functionality
- Full CRUD operations for all entities
- Dashboard with analytics and charts

**Peoples Portal (Responsive):**
- Profile management interface
- Limited CRUD access to specific features
- Clean and intuitive forms
- Responsive design for mobile and desktop
- User-friendly navigation
- Feature-specific dashboards

**Public Site (Responsive):**
- Fully responsive design
- SEO-optimized pages
- Fast loading with optimized assets
- Clear call-to-action buttons
- Professional presentation

#### Authentication Flow

```php
// Login Controllers per Gate
AdminLoginController::class     // Handles admin authentication
PeoplesLoginController::class   // Handles peoples authentication  
UnifiedLoginController::class   // Auto-detects user type and redirects

// Session Isolation (ClearsOtherGuards trait)
$this->clearAllGuardsExcept('peoples'); // Logout other gates before peoples login
```

### Resources (API Responses)

- Location: `app/Http/Resources/`
- Transform models for API/frontend consumption
- Use for consistent data formatting

### Models

- Use `final` classes by default
- Constructor property promotion: `public function __construct(public GitHub $github) {}`
- Explicit return types: `public function users(): HasMany`
- Casts in `casts()` method, not `$casts` property
- Relationships with proper return type hints

## Three-Gate Authentication System

This application implements a multi-gate authentication system with unified login:

### Authentication Gates
- **Admin Gate**: `auth:web` - System administrators with full access to all features
- **Peoples Gate**: `auth:peoples` - Regular users with limited access (profile management and specific features)

### Unified Login System
- Single login endpoint that automatically detects user type (admin or peoples)
- Redirects to appropriate dashboard based on user role
- Session isolation prevents simultaneous logins across different gates

### Gate Structure Overview
```
Authentication Models:
├── User.php          # Admin authentication (guard: web)
└── People.php        # Peoples authentication (guard: peoples)

Frontend Entries:
├── admin.tsx         # Admin portal entry point
├── peoples.tsx       # Peoples portal entry point
└── site.tsx          # Public site entry point

Blade Templates:
├── admin/app.blade.php    # Mounts admin.tsx
├── peoples/app.blade.php  # Mounts peoples.tsx
└── site/app.blade.php     # Mounts site.tsx

Controllers Structure:
├── Admin/            # Admin-only controllers (auth:web) - Full CRUD access
├── Peoples/          # Peoples-only controllers (auth:peoples) - Limited access
├── Auth/             # Authentication controllers for all gates
└── Public/           # Public site controllers (no auth)

Routes Structure:
├── web.php           # Public site routes
├── admin.php         # Admin routes (middleware: auth:web)
├── peoples.php       # Peoples routes (middleware: auth:peoples)
└── auth.php          # Authentication routes (unified login)
```

### Session Isolation
- **ClearsOtherGuards**: Trait prevents multiple gate sessions
- **Separate Guards**: Each gate has isolated session storage
- **Security**: No cross-gate authentication contamination

### Gate-Specific Patterns
```php
// Controller Location Pattern
app/Http/Controllers/{Gate}/{Feature}Controller.php

// Form Request Pattern  
app/Http/Requests/{Gate}/{Feature}/{Store|Update}{Feature}Request.php

// Route Middleware Pattern
Route::middleware(['auth:{gate}', 'verified'])->group(function () {
    // Gate-specific routes
});

// Frontend Page Pattern
resources/js/pages/{gate}/{feature}/Index.tsx
```

## Component Patterns

### UI Components (Radix UI + shadcn/ui)

- **Location**: `@/components/ui/` (base components)
- **Pattern**: Radix UI primitives with Tailwind styling
- **Variants**: Use `class-variance-authority` for component variants
- **Examples**: `Button`, `Dialog`, `DropdownMenu`, `Table`
- **Composition**: Build complex components from primitives

### Page Components

- **Location**: `@/pages/` (Inertia page components organized by gate)
- **Admin Pages**: `@/pages/admin/` - Full-featured desktop UI
- **Peoples Pages**: `@/pages/peoples/` - Responsive interface for profile and features
- **Public Pages**: `@/pages/public/` - Public site pages
- **Props**: Typed with TypeScript interfaces
- **Navigation**: Use `<Link>` or `router.visit()`, never regular `<a>` tags

### Layout Components

- **Location**: `@/layouts/`
- **Admin Layout**: `admin-layout.tsx` - Sidebar-based desktop layout
- **Peoples Layout**: `peoples-layout.tsx` - Responsive layout for peoples portal
- **Auth Layout**: `auth-layout.tsx` - Clean authentication pages
- **Site Layout**: `site-layout.tsx` - Public site header/footer

### Import Patterns

- **Alias**: `@/` for `resources/js/`
- **Components**: `import { Button } from '@/components/ui/button'`
- **Utils**: `import { cn } from '@/utils/utils'`
- **Types**: `import { User } from '@/types/models'`
- **Pagination**: `import DynamicPaginator from '@/components/DynamicPaginator'`
- **Gate-specific**: Organize imports by gate context

### Application Content Language
- **Indonesian**: All user-facing content in Bahasa Indonesia Baku
  - Page titles: "Manajemen Pengguna", "Daftar Siswa", "Laporan Kehadiran"
  - Card descriptions: "Total siswa aktiv", "Kehadiran hari ini"
  - Button labels: "Simpan", "Hapus", "Edit", "Tambah Data"
  - Form labels: "Nama Lengkap", "Email", "Nomor Telepon"
  - Status messages: "Data berhasil disimpan", "Gagal menghapus data"
  - Validation messages: "Email wajib diisi", "Format nomor telepon salah"
- **Consistency**: Follow existing terminology and phrasing patterns
- **Documentation**: Keep technical documentation (like this AGENTS.md) in English for developer reference


## Database & Debugging

### Database Tools

- **Migrations**: `php artisan migrate:fresh --seed`
- **Tinker**: Use Laravel Boost `tinker` tool or `php artisan tinker`
- **Query**: Use Laravel Boost `database-query` tool for read-only queries
- **Schema**: Use Laravel Boost `database-schema` tool

### Debugging Tools

<!-- - **Horizon**: Queue monitoring at `/horizon` -->
- **Telescope**: Debug dashboard at `/telescope` (dev only)
<!-- - **Debugbar**: Laravel Debugbar for request debugging -->
- **Browser Logs**: Use Laravel Boost `browser-logs` tool
- **App Logs**: Use Laravel Boost `read-log-entries` tool
- **Last Error**: Use Laravel Boost `last-error` tool

### Queue Management

<!-- - **Horizon**: Monitor queues, failed jobs, metrics -->
- **Commands**: `php artisan queue:work`, `php artisan queue:restart`
- **Jobs**: Implement `ShouldQueue` interface for background processing

## PHP Code Style

- Always use `declare(strict_types=1);` at top of PHP files
- Use PHP 8.4+ constructor property promotion: `public function __construct(public GitHub $github) {}`
- Always use explicit return type declarations for methods
- Use `final` classes by default (enforced by Pint)
- Import all classes, constants, and functions at top of file
- Use strict comparison operators (`===`, `!==`)
- Follow Laravel conventions: Eloquent models, Form Requests for validation, API Resources
- PHPDoc for complex generics and array shapes only
- Private over protected members for encapsulation

## TypeScript/React Code Style

- Use TypeScript with strict typing enabled
- Import React components with named imports: `import { Component } from '@/components/component'`
- Import `{feature}Route` from `@/routes/{gate}/{feature}` for Inertia navigation
- Use Inertia.js for navigation: `<Link>` components and `router.visit()`
- Follow existing component patterns in `resources/js/components/`
- Use Tailwind CSS v4 classes for styling (no deprecated utilities)
- Use `@/` path alias for imports from `resources/js/`
- Prefer functional components with hooks over class components
- Use `cn()` utility for conditional class names

## Environment & Deployment


### Asset Building

- **Development**: `npm run dev` for HMR
- **Production**: `npm run build` for optimized assets
- **Vite Error**: If manifest error, run `npm run build` or restart dev server

### Services

- **Redis**: Caching, sessions, queues
- **MySQL**: Primary database

### Code Quality Checks

- **PHP**: `./vendor/bin/pint --preset laravel` for formatting
- **TypeScript/React**: `npx eslint . --fix` for linting
- **PHPStan**: `./vendor/bin/phpstan analyze --memory-limit=3G` for type checking. Read `docs/rules/larastan-rules.md` for custom rules.
- **Tests**: `./vendor/bin/pest` to run all tests

## Troubleshooting



### Debug Workflow

1. Check browser logs with Laravel Boost `browser-logs` tool
2. Check application logs with `read-log-entries` tool
3. Use `tinker` tool to test PHP code
4. Use `database-query` tool to inspect data
5. Check `/telescope` for request debugging
6. Use browser tests to verify frontend behavior
