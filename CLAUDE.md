# CLAUDE.md — CoworkPlatform Project Memory

> Read this file at the start of every session. This is the single source of truth for the project.

---

## What this project is

An Airbnb-style coworking/workspace reservation platform built as an internship project.
Three-sided marketplace: **customer** (browse & book) + **host/seller** (list & manage spaces) + **admin** (approve, moderate, manage).

Design inspiration: **wework.com** (premium feel, full-bleed photography, heavy whitespace, clean typography).
Functional reference: **coworker.com** (filtering, quick quote) — but we build better: real instant booking, real availability calendar, Stripe payments.

---

## Stack — locked, no changes

| Layer | Tech |
|---|---|
| Backend framework | Laravel 11 (PHP 8.2+) |
| Auth | Laravel Sanctum — SPA cookie mode (NOT JWT) |
| ORM | Eloquent |
| Database | MySQL 8 |
| Frontend framework | Vue 3 — Composition API + `<script setup>` only |
| State management | Pinia (NOT Vuex — Vuex is maintenance-only) |
| Routing (frontend) | Vue Router 4 |
| HTTP client | Axios — single shared instance in `resources/js/api.js` |
| CSS framework | Bootstrap 5 — custom compiled via Sass (NOT CDN) |
| Build tool | Vite + `@vitejs/plugin-vue` + `laravel-vite-plugin` |
| File storage | Local for dev → S3 for prod |
| Payments | Stripe Connect (marketplace split — host gets paid automatically) |
| Queue | Redis + Laravel Horizon |
| Search | Laravel Scout (database driver first → Meilisearch when > 100k listings) |
| Real-time | Laravel Reverb (WebSockets for messages + availability) |

---

## Project name & database

- **App name:** CoworkPlatform
- **Database:** `cowork_platform` (MySQL, utf8mb4)
- **API prefix:** `/api/v1/`
- **Frontend port:** 5173 (Vite dev server)
- **Backend port:** 8000 (php artisan serve)

---

## Folder structure

```
cowork-platform/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── SpaceController.php
│   │   │   ├── SpaceWorkspaceController.php
│   │   │   ├── BookingController.php
│   │   │   ├── AmenityController.php
│   │   │   ├── ReviewController.php
│   │   │   ├── MessageController.php
│   │   │   ├── Host/
│   │   │   │   ├── HostSpaceController.php
│   │   │   │   ├── HostBookingController.php
│   │   │   │   └── HostDashboardController.php
│   │   │   └── Admin/
│   │   │       ├── AdminSpaceController.php
│   │   │       ├── AdminUserController.php
│   │   │       └── AdminDashboardController.php
│   │   ├── Middleware/
│   │   │   └── EnsureUserHasRole.php
│   │   └── Requests/
│   │       ├── StoreSpaceRequest.php
│   │       ├── UpdateSpaceRequest.php
│   │       └── StoreBookingRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Space.php
│   │   ├── SpaceWorkspace.php
│   │   ├── Booking.php
│   │   ├── BookingDay.php
│   │   ├── Amenity.php
│   │   ├── Review.php
│   │   └── Message.php
│   └── Filters/           ← FilterPipeline classes (PriceFilter, AmenityFilter, etc.)
├── resources/
│   ├── js/
│   │   ├── app.js         ← Vue entry point
│   │   ├── App.vue
│   │   ├── api.js         ← Axios instance (single source, withCredentials: true)
│   │   ├── router/
│   │   │   └── index.js
│   │   ├── stores/        ← Pinia stores
│   │   │   ├── auth.js
│   │   │   ├── spaces.js
│   │   │   └── booking.js
│   │   ├── composables/   ← useAuth, useBooking, useDebouncedSearch, useGeolocation
│   │   ├── components/    ← SpaceCard, FilterBar, CalendarPicker, QuoteModal, etc.
│   │   └── views/
│   │       ├── customer/  ← HomeView, SearchView, SpaceDetailView, LoginView, RegisterView
│   │       ├── host/      ← HostDashboard, HostSpaceList, HostSpaceEdit, HostCalendar
│   │       └── admin/     ← AdminDashboard, AdminUsers, AdminSpaces
│   └── css/
│       └── app.scss       ← Bootstrap Sass — variables overridden BEFORE import
└── routes/
    ├── web.php            ← Single catch-all → SPA blade view
    └── api.php            ← All API routes under /api/v1/
```

---

## Database schema — core tables

```
users
  id, name, email, password, role ENUM(customer|host|admin), created_at, updated_at

host_profiles
  id, user_id FK, business_name, description, stripe_account_id, payout_method

spaces
  id, host_id FK→users, title, slug, description, address, lat DECIMAL(10,7), lng DECIMAL(10,7)
  city, country, status ENUM(draft|published|paused), rating_avg, created_at, updated_at

space_photos
  id, space_id FK, path, order INT

amenities
  id, name, icon, category

amenity_space (pivot)
  space_id FK, amenity_id FK

workspace_types
  id, name  ← hot_desk | dedicated_desk | private_office | meeting_room

space_workspaces
  id, space_id FK, workspace_type_id FK, capacity, price_hourly, price_daily, price_monthly, currency

bookings
  id, customer_id FK→users, space_workspace_id FK, start_date, end_date
  status ENUM(pending|confirmed|cancelled|completed), total_amount
  stripe_payment_intent_id, created_at, updated_at

booking_days (double-booking guard)
  id, booking_id FK, space_workspace_id FK, day DATE
  UNIQUE KEY (space_workspace_id, day)

reviews
  id, booking_id FK, customer_id FK, space_id FK, rating TINYINT, comment TEXT

messages
  id, thread_id FK, sender_id FK→users, body TEXT, read_at TIMESTAMP nullable

threads
  id, space_id FK, customer_id FK, host_id FK, created_at
```

---

## Auth flow — Sanctum SPA mode

1. Vue calls `GET /sanctum/csrf-cookie`
2. Vue calls `POST /api/v1/login` → Laravel sets session cookie
3. All subsequent `/api/v1/*` calls auto-include cookie + `X-XSRF-TOKEN` header
4. `auth:sanctum` middleware validates on every protected route
5. Logout: `POST /api/v1/logout` deletes current access token

**Never use JWT for this project. Sanctum only.**

---

## Role system

Three roles on `users.role`: `customer`, `host`, `admin`

Middleware: `EnsureUserHasRole` — registered as alias `role` in `bootstrap/app.php`

Usage in routes:
```php
Route::middleware(['auth:sanctum', 'role:host'])->prefix('host')->group(...)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(...)
```

Vue route guards in `router/index.js` check Pinia auth store for role before allowing navigation.

---

## API route map (summary)

```
PUBLIC
  POST   /api/v1/register
  POST   /api/v1/login
  POST   /api/v1/logout
  GET    /api/v1/me
  GET    /api/v1/spaces                          ← search + filter
  GET    /api/v1/spaces/{space}                  ← detail
  GET    /api/v1/workspaces/{workspace}/availability
  GET    /api/v1/amenities
  GET    /api/v1/spaces/{space}/reviews

CUSTOMER (auth:sanctum)
  GET    /api/v1/bookings
  POST   /api/v1/bookings
  GET    /api/v1/bookings/{booking}
  PATCH  /api/v1/bookings/{booking}/cancel
  POST   /api/v1/bookings/{booking}/review
  GET    /api/v1/threads
  GET    /api/v1/threads/{thread}
  POST   /api/v1/threads/{thread}/messages
  POST   /api/v1/spaces/{space}/enquire         ← Quick Quote (single host, not multi-broker)

HOST (auth:sanctum + role:host) — prefix: /api/v1/host
  GET    /dashboard
  CRUD   /spaces
  POST   /spaces/{space}/photos
  DELETE /spaces/{space}/photos/{photo}
  PATCH  /spaces/{space}/photos/reorder
  CRUD   /spaces/{space}/workspaces
  GET    /bookings
  GET    /bookings/{booking}
  PATCH  /bookings/{booking}/confirm
  PATCH  /bookings/{booking}/cancel
  GET    /threads

ADMIN (auth:sanctum + role:admin) — prefix: /api/v1/admin
  GET    /dashboard
  CRUD   /users
  PATCH  /users/{user}/role
  GET    /spaces
  PATCH  /spaces/{space}/approve
  PATCH  /spaces/{space}/reject
  DELETE /spaces/{space}
```

---

## Double-booking protection

Strategy A — `booking_days` UNIQUE index (for daily bookings):
Insert one row per day inside a DB transaction. Duplicate key = conflict = rollback.

Strategy B — `SELECT ... FOR UPDATE` row lock (for hourly meeting rooms):
```sql
START TRANSACTION;
SELECT * FROM space_workspaces WHERE id = ? FOR UPDATE;
-- check overlap, if none → INSERT booking
COMMIT;
```

Always lock in ascending workspace ID order to prevent deadlocks.

---

## Frontend — Vue SPA structure

Three sub-apps inside one SPA, separated by route prefix and lazy-loaded:
- `/` → customer views (public + auth)
- `/host/*` → host panel (role guard)
- `/admin/*` → admin panel (role guard)

All three share the same Pinia stores, Axios instance, and component library.

---

## Bootstrap Sass setup

Variables overridden BEFORE `@import 'bootstrap/scss/bootstrap'`:
```scss
$primary: #1a1a2e;
$font-family-sans-serif: 'Inter', sans-serif;
$border-radius: 0.75rem;
$border-radius-lg: 1rem;
$card-border-width: 0;
$card-box-shadow: 0 2px 16px rgba(0,0,0,.08);
```

Never use Bootstrap CDN. Always compile from Sass source.

---

## Build phases completed

- [x] Phase 0 — Project setup (Laravel + Vue 3 + Bootstrap 5 Sass + Sanctum + Git)
- [x] Phase 1 — Full database schema + all migrations + factories + seeders
- [x] Phase 2 — Auth end-to-end (Sanctum ↔ Pinia store ↔ Vue route guards)
- [x] Phase 3 — API skeleton (all routes + controllers + resources + policies + Postman tested)
- [x] Phase 4 Slice 1 — Listings (FilterPipeline, SpaceCard, SearchView, SpaceDetailView, AppNav, spaces store)
- [ ] Phase 4 Slice 2 — Booking (availability calendar + double-booking guard + Stripe)
- [ ] Phase 4 Slice 3 — Host dashboard (space CRUD + calendar + stats)
- [ ] Phase 4 Slice 4 — Admin panel (approvals + user management)

---

## Current phase

**Phase 4 Slice 1 complete. Next: Phase 4 Slice 2 — Booking (availability calendar + double-booking guard + Stripe).**

---

## Key decisions & reasons (don't second-guess these)

| Decision | Why |
|---|---|
| Sanctum not JWT | Simpler revocation, Laravel team recommended for first-party SPA |
| Pinia not Vuex | Vuex is maintenance-only for Vue 3, Pinia is the official successor |
| Composition API only | No Options API anywhere — consistency + composables pay off at scale |
| FilterPipeline pattern | One class per filter, controller stays clean, easy to add new filters |
| booking_days UNIQUE index | Atomic double-booking guard at DB level, no race conditions |
| Single Axios instance | One place for interceptors, token attachment, 401 redirect |
| Scout database driver first | Zero infrastructure, enough for early scale, swap to Meilisearch later |

---

## Reference sites

- Design inspiration: **wework.com** — full-bleed hero, premium whitespace, bold typography
- Functional reference: **coworker.com** — filtering UX, listing structure (but no instant booking — that's our edge)

---

## Notes for Claude in IDE

- When writing controllers: keep them thin, push logic to Service classes or Jobs
- When writing Vue: Composition API + `<script setup>` only, no Options API
- When writing queries: always eager load with `with()`, never N+1
- When touching auth: always check if `sanctum/csrf-cookie` is called first in Vue
- API responses always use API Resources, never raw `$model->toArray()`
- All validation in Form Request classes, never inline in controllers
- Check `CLAUDE.md` before starting any new feature — update the phase checklist when done
