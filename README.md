# ✅ PHASE 3: Company & Project System (Core SaaS Structure)

🎯 **Goal:** Build **multi-tenant SaaS architecture** with proper ownership

---

## 🏢 Company System

### Company Database

- [ ]  Create `companies` table
    - id
    - name
    - join_code
    - created_by (user_id)
- [ ]  Add `company_id` to `users` table (nullable at first)

### Company Logic

- [ ]  Create company
    - Creator becomes **company admin**
    - `company_id` stored in users table
- [ ]  Join company using join code
    - Validate code
    - Assign `company_id` to user
- [ ]  Restrict: **one user → one company only**

---

## 📁 Project System (NEW – Important)

### Project Database

- [ ]  Create `projects` table
    - id
    - company_id
    - name
    - description
    - created_by (user_id)

### Project Logic

- [ ]  Create project (admin only)
- [ ]  List projects by company
- [ ]  Ensure project always belongs to **same company as user**

---

## ✅ Task Update (Now Project-Based)

### Task Database Changes

- [ ]  Add `project_id` to `tasks` table
- [ ]  Remove direct `company_id` from tasks

### Task Logic

- [ ]  Assign tasks under a project
- [ ]  Show tasks by project
- [ ]  Ensure project → company → user match

---

## 🔗 Relationships (Very Important)

### User Model

```
User belongsTo Company
User hasMany Tasks (assigned)

```

### Company Model

```
Company hasMany Users
Company hasMany Projects

```

### Project Model

```
ProjectbelongsToCompany
ProjecthasManyTasks

```

### Task Model

```
Task belongsTo Project
Task belongsToUser(assigned_to)

```

---
