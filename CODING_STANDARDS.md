# WorkHub Coding Standards & Architectural Guidelines

This document serves as the single source of truth for architectural decisions, coding standards, design patterns, and development guidelines in the WorkHub codebase.

---

## 1. Facade Import Conventions

- **Do NOT** use fully-qualified inline facade paths in method calls, logic blocks, or service providers.
  - ❌ **Incorrect**: `\Illuminate\Support\Facades\Gate::before(...)`
  - ❌ **Incorrect**: `\Illuminate\Support\Facades\Http::get(...)`
  - ❌ **Incorrect**: `\Illuminate\Support\Facades\URL::forceScheme(...)`
  - ❌ **Incorrect**: `\Spatie\Permission\PermissionRegistrar::class`

- **DO** always import classes and Facades at the top of the file using standard `use` statements.
  - ✅ **Correct**:
    ```php
    use Illuminate\Support\Facades\Gate;

    Gate::before(function ($user, $ability) {
        return $user->isSuperAdmin() ? true : null;
    });
    ```

---

## 2. 🛡️ Policy-Based Access Control (PBAC)

WorkHub leverages Laravel's native Authorization Gate/Policy system to centralize access control logic. **All controller actions, view layouts, and API requests must utilize policies rather than inline conditional DB checks.**

### Policy Scope Reference

| Target Model | Policy Class | Authorization Scope | Key Methods |
| :--- | :--- | :--- | :--- |
| `Task` | `TaskPolicy` | Handles personal space tasks (user ownership) & company scope tasks (project/company membership). | `view`, `update`, `delete`, `restore`, `forceDelete` |
| `Project` | `ProjectPolicy` | Segregates personal projects (owned by creator) and company-owned projects (only modifiable by admin). | `view`, `create`, `update`, `delete`, `restore`, `forceDelete` |
| `Company` | `CompanyPolicy` | Controls organization management (members, settings). Custom constraints prevent admins from leaving. | `view`, `update`, `delete`, `leave` |
| `CompanyUsers` | `CompanyUsersPolicy` | Manages membership record actions (e.g. restoring soft-deleted memberships). | `restore`, `forceDelete` |
| `CompanyInvitation` | `CompanyInvitationPolicy` | Authorizes invitation responses (verifies invitation belongs to authenticated user). | `handle` |

---

## 3. ⚙️ Soft Deletes & Cascading Restorations

Care must be taken when querying or restoring soft-deleted resources to prevent orphaned records or invalid states:

1. **Relationship Queries**: When querying parent models of soft-deleted resources (e.g., project of a deleted task), use `withTrashed()` or `onlyTrashed()` where appropriate.
2. **Cascading Actions**: When a resource is permanently deleted (`forceDelete`):
    - **Projects**: Permanently delete all tasks within the project.
    - **Companies**: Permanently delete all projects, tasks, and member relationship records within the company.

---

## 4. ⚡ Caching Strategies

To avoid duplicate database queries across views, WorkHub uses the Laravel Cache Facade for repeating layouts.

### User Workspace Invitations

Pending invitations are loaded in a View Composer registered in `AppServiceProvider`.
- **Cache Key**: `pending_invitations_{user_id}`
- **Expiration**: 5 Minutes (300 seconds)
- **Invalidation**: The cache **must** be cleared when:
    - An invitation is sent (`CompanyController@invite`)
    - An invitation is accepted (`CompanyController@acceptInvitation`)
    - An invitation is rejected (`CompanyController@rejectInvitation`)

---

## 5. 📁 MVC Separation & File Architecture

Keep files clean, focused, and strictly separated:
- **Models** (`app/Models/*`): Handle database schema mappings, casts, and model relationships.
- **Controllers** (`app/Http/Controllers/*`): Request validation, delegation to Policies, database operations, and redirects.
- **Policies** (`app/Policies/*`): All permission checks and authorization logic.
- **Views** (`resources/views/*`): Blade templates using modern styling. Utilize `@can('update', $model)` to conditionally render action elements.
