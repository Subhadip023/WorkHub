# ✅ PHASE 1 — Basic Task System (NO Users)

🎯 Goal:

Learn **form submission → controller → DB → Blade**

---

## 📌 Features

- Add task
- View task list
- Delete task

---

## 🗄️ Database (Phase 1)

### tasks table

```
id
title
description
status
timestamps

```

---

## 🧩 What You Learn Here

| Concept | Covered |
| --- | --- |
| Routing | ✅ |
| Controller | ✅ |
| Blade | ✅ |
| Forms | ✅ |
| Validation | ✅ |
| DB | ✅ |
| CRUD | ✅ |

---

## 🔁 Flow

```
Blade Form → POST route → Controller → Model → DB → Redirect → Blade View

```

---

## 🛠 Example Routes

```php
Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

```
