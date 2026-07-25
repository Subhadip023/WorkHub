# 📋 WorkHub Project Plan

This document outlines the current project status, accomplished refactoring milestones, and the roadmap for upcoming development phases of the **WorkHub** application.

---

## 🚀 Status Overview
- **Core Security Refactoring**: `100% Completed`
- **Soft Delete Centralization**: `100% Completed`
- **Session Caching Optimization**: `100% Completed`
- **Test Suite Status**: `144 / 144 Tests Passing (100% success rate)`

---

## 🎯 Phase 1: Hardening & Refactoring (Completed)
- [x] **Centralized Policy-Based Access Control (PBAC)**
  - Migrated manual checks from `TrashController` and `CompanyController` to policies.
  - Implemented `CompanyInvitationPolicy` for standardizing workspace invite access.
- [x] **Secure Soft-Delete Management**
  - Updated all policies to support safe soft-delete verification using `withTrashed()`.
  - Added recursive cascading on force-delete (e.g. deleting parent project/company purges nested tasks/members).
- [x] **Optimized Layout Performance**
  - Cached workspace invitation checks for 5 minutes inside `AppServiceProvider` View Composer.
  - Integrated smart cache invalidation on invite, accept, and reject actions in `CompanyController`.
- [x] **Fixed Layout Engine Bugs**
  - Cleaned up a duplicate `@endif` in the admin dashboard base layout causing syntax/compilation issues.

---

## 🛣️ Phase 2: Membership Management & direct controls (Upcoming)
- [ ] **Implement direct `CompanyUsers` membership actions**
  - Hook up `CompanyUsersController` methods (e.g., direct membership editing, role adjustment, and custom controls) through `CompanyUsersPolicy`.
- [ ] **Improve Note management authorization**
  - Align UI note buttons to respect `NotePolicy` for create, edit, delete, and view permissions.
- [ ] **UI/UX Direct Access Check**
  - Ensure all layout views conditionally render action elements (Delete, Restore, Force Delete, Edit) using Blade's `@can` and `@cannot` directives matching the newly created policies.

---

## 🧪 Testing & CI Roadmap
- [ ] **Mock Mail Testing**
  - Add feature tests for invitation emails to verify proper generation and content.
- [ ] **Continuous Integration (CI) configuration**
  - Add GitHub Actions workflows to auto-run Pest tests on new branch pushes to prevent regressions.
