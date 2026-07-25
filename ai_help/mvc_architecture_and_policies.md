# WorkHub MVC & Policy Centralization Architecture Guidelines

This document details the architectural decisions, design patterns, and coding conventions implemented in the WorkHub codebase. Any subsequent modifications or features added to the application must strictly adhere to these practices.

---

## 🛡️ Policy-Based Access Control (PBAC)

WorkHub leverages Laravel's native Authorization Gate/Policy system to centralize access control logic. **All controller actions, view layouts, and API requests must utilize policies rather than inline conditional DB queries.**

### Policy Definitions and Scopes

| Target Model | Policy Class | Authorization Scope | Key Methods |
| :--- | :--- | :--- | :--- |
| `Task` | `TaskPolicy` | Handles personal space tasks (user ownership) & company scope tasks (project/company membership). | `view`, `update`, `delete`, `restore`, `forceDelete` |
| `Project` | `ProjectPolicy` | Segregates personal projects (owned by creator) and company-owned projects (only modifiable by admin). | `view`, `create`, `update`, `delete`, `restore`, `forceDelete` |
| `Company` | `CompanyPolicy` | Controls organization management (members, settings). Custom administrative constraints prevent admins from leaving. | `view`, `update`, `delete`, `leave` |
| `CompanyUsers` | `CompanyUsersPolicy` | Manages membership record actions (e.g. restoring soft-deleted memberships). | `restore`, `forceDelete` |
| `CompanyInvitation` | `CompanyInvitationPolicy` | Authorizes invitation responses (verifies invitation belongs to the authenticated user). | `handle` |

---

## ⚙️ Soft Deletes & Admin Restorations

Special care must be taken when managing soft-deleted parent relationships to avoid orphaned records or invalid state.

1. **Relationship Queries**: When querying parent models of soft-deleted resources (e.g., project of a deleted task), use `withTrashed()` or `onlyTrashed()` where appropriate.
2. **Cascading Actions**: When a resource is permanently deleted (`forceDelete`):
    - **Projects**: Permanently delete all tasks within the project.
    - **Companies**: Permanently delete all projects, tasks, and member relationship records within the company.

---

## ⚡ Caching Strategies

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

## 📁 File Structure Reference

Keep files clean and strictly separated:
- **Models** (`app/Models/*`): Handle database schema mappings and model relationships.
- **Controllers** (`app/Http/Controllers/*`): Request handling, delegation to Policies, database operation calls, and redirects.
- **Policies** (`app/Policies/*`): All permission checks and authorization logic.
- **Views** (`resources/views/*`): Simple Blade files with layout logic. Utilize `@can('update', $model)` to conditionally hide or show action items.
