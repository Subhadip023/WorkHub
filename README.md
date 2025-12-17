# ✅ PHASE 4: Roles, Authorization & Security

🎯 **Goal:** Control access inside company & projects

---

## 👤 Roles (Company-Level)

### Roles Definition

- [ ]  Company Admin
- [ ]  Company Member

> Store role as:
> 
- `users.role`
    
    **OR**
    
- `company_users` table (optional later)

---

## 🔐 Authorization Rules

### Middleware & Policies

- [ ]  Create `CompanyAdminMiddleware`
- [ ]  Create `ProjectPolicy`
- [ ]  Create `TaskPolicy`

---

### Access Control Rules

### Company

- [ ]  Only admin can:
    - Create projects
    - Invite users
    - Delete projects

### Project

- [ ]  Users can only access projects in **their company**
- [ ]  Prevent cross-company project access

### Task

- [ ]  Admin can:
    - Create tasks
    - Assign tasks
- [ ]  Members can:
    - Update task status only
- [ ]  Prevent task access outside project/company

---

## 🔒 Multi-Tenant Security (Critical Learning)

- [ ]  Always filter by `company_id`
- [ ]  Never trust incoming IDs
- [ ]  Validate project → company → user chain
- [ ]  Use policies instead of controller logic

---

# 🚀 OPTIONAL PHASES (Later – Highly Recommended)

- [ ]  Task status (pending / in-progress / completed)
- [ ]  Project-based pagination
- [ ]  Search tasks inside project
- [ ]  Activity logs
- [ ]  REST API version
- [ ]  React frontend integration
