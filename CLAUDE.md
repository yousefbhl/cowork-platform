# CLAUDE.md — CoworkPlatform Project Memory

> Read this file at the start of every session. This is the single source of truth for the project.

---

## What this project is

An Airbnb-style coworking/workspace reservation platform built as an internship project.
Three-sided marketplace: **customer** (browse & book) + **host/seller** (list & manage spaces) + **admin** (approve, moderate, manage).

Design system: **Linen & Forest** — forest green `#2D6A4F`/`#1B4332`, linen `#F7F4EF`, white surfaces, `#E4DFD7` borders, Inter + DM Mono, `0.75rem` radius.

---

## Stack — locked, no changes

| Layer | Tech |
|---|---|
| Backend framework | Laravel 11 (PHP 8.2+) |
| Auth | Laravel Sanctum — **Bearer token mode** (tokens in `localStorage` as `auth_token`) |
| ORM | Eloquent |
| Database | MySQL 8 |
| Frontend framework | Vue 3 — Composition API + `<script setup>` only |
| State management | Pinia setup stores (`ref`/`computed` returned as plain object) |
| Routing (frontend) | Vue Router 4 with `beforeEach` guard |
| HTTP client | Axios — single shared instance in `resources/js/api.js` |
| CSS framework | Bootstrap 5 — custom compiled via Sass in `resources/css/app.scss` |
| Build tool | Vite + `@vitejs/plugin-vue` + `laravel-vite-plugin` |
| File storage | Local for dev → S3 for prod |

---

## Project name & database

- **App name:** CoworkPlatform
- **Database:** `cowork_platform` (MySQL, utf8mb4)
- **API prefix:** `/api/v1/`
- **Frontend port:** 5173 (Vite dev server)
- **Backend port:** 8000 (php artisan serve)

---

## Auth flow — Sanctum Bearer token mode

1. Vue calls `POST /api/v1/login` → Laravel returns `{ user, token }`
2. Token stored in `localStorage` as `auth_token`
3. Axios request interceptor attaches `Authorization: Bearer {token}` to every request
4. 401 response interceptor removes token + redirects to `/login`
5. Pinia auth store rehydrates from `localStorage` on page load via router `beforeEach`
6. Logout: `POST /api/v1/logout` deletes the Sanctum token, then clears localStorage

**This is Bearer token mode, NOT SPA cookie mode. Never use `sanctum/csrf-cookie`.**

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

## Folder structure (actual current state)

```
cowork-platform/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php            ← register, login, logout, me
│   │   │   ├── ProfileController.php         ← updateProfile, updatePassword
│   │   │   ├── WishlistController.php        ← index, store, destroy
│   │   │   ├── SpaceController.php
│   │   │   ├── SpaceWorkspaceController.php
│   │   │   ├── BookingController.php
│   │   │   ├── AmenityController.php
│   │   │   ├── WorkspaceTypeController.php
│   │   │   ├── ReviewController.php
│   │   │   ├── MessageController.php
│   │   │   ├── Host/
│   │   │   │   ├── HostSpaceController.php   ← uploadPhoto, deletePhoto too
│   │   │   │   ├── HostBookingController.php
│   │   │   │   └── HostDashboardController.php
│   │   │   └── Admin/
│   │   │       ├── AdminSpaceController.php
│   │   │       ├── AdminUserController.php
│   │   │       └── AdminDashboardController.php
│   │   ├── Middleware/
│   │   │   └── EnsureUserHasRole.php
│   │   ├── Requests/
│   │   │   ├── StoreSpaceRequest.php         ← includes workspaces[] validation
│   │   │   ├── UpdateSpaceRequest.php
│   │   │   └── StoreBookingRequest.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── SpaceResource.php
│   │       ├── SpaceWorkspaceResource.php
│   │       ├── AmenityResource.php
│   │       └── BookingResource.php
│   ├── Models/
│   │   ├── User.php                          ← wishlistedSpaces() belongsToMany
│   │   ├── Space.php                         ← slug route key, scopePublished
│   │   ├── SpaceWorkspace.php
│   │   ├── SpacePhoto.php
│   │   ├── HostProfile.php
│   │   ├── WorkspaceType.php
│   │   ├── Booking.php
│   │   ├── BookingDay.php
│   │   ├── Amenity.php
│   │   ├── Review.php
│   │   ├── Thread.php
│   │   └── Message.php
│   └── Services/
│       └── BookingService.php                ← DB transaction + lockForUpdate + booking_days
├── database/
│   ├── migrations/                           ← all run; latest: 2026_06_28 wishlists
│   └── seeders/
│       ├── DatabaseSeeder.php                ← just calls DemoSeeder
│       └── DemoSeeder.php                    ← 7 users, 9 Casablanca spaces, 11 bookings
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   ├── App.vue
│   │   ├── api.js                            ← Bearer interceptor + 401 redirect
│   │   ├── router/
│   │   │   └── index.js                      ← beforeEach: rehydrate auth + load wishlist
│   │   ├── stores/
│   │   │   ├── auth.js
│   │   │   ├── spaces.js
│   │   │   ├── booking.js
│   │   │   └── wishlist.js                   ← savedIds[], toggle(), ensureLoaded()
│   │   ├── utils/
│   │   │   └── photoUrl.js                   ← prepend /storage/ unless starts with http
│   │   ├── components/
│   │   │   ├── AppNav.vue                    ← avatar → /profile, Wishlist link for customers
│   │   │   ├── SpaceCard.vue                 ← heart toggle button (top-right of photo)
│   │   │   ├── FilterBar.vue
│   │   │   └── CalendarPicker.vue
│   │   └── views/
│   │       ├── customer/
│   │       │   ├── HomeView.vue
│   │       │   ├── SearchView.vue
│   │       │   ├── SpaceDetailView.vue
│   │       │   ├── BookingsView.vue
│   │       │   ├── ProfileView.vue           ← name/email + password change
│   │       │   ├── WishlistView.vue
│   │       │   ├── LoginView.vue
│   │       │   └── RegisterView.vue
│   │       ├── host/
│   │       │   ├── HostDashboard.vue
│   │       │   ├── MyListings.vue
│   │       │   ├── CreateListing.vue         ← full workspace rows + photo upload
│   │       │   └── HostBookings.vue
│   │       └── admin/
│   │           ├── AdminDashboard.vue
│   │           └── AdminSpaces.vue
│   └── css/
│       └── app.scss                          ← Bootstrap Sass + .sk shimmer animation
└── routes/
    ├── web.php
    └── api.php
```

---

## Database schema — all tables

```
users
  id, name, email, password, role ENUM(customer|host|admin), created_at, updated_at

host_profiles
  id, user_id FK, business_name, description, stripe_account_id, phone, website

spaces
  id, host_id FK→users, title, slug UNIQUE, description, address
  lat DECIMAL(10,7), lng DECIMAL(10,7), city, country
  status ENUM(draft|published|paused), rating_avg, reviews_count, created_at, updated_at
  ← getRouteKeyName() returns 'slug' — all {space} bindings resolve by slug

space_photos
  id, space_id FK, path, order INT, is_cover BOOLEAN

amenities
  id, name, icon, category

amenity_space (pivot, no id, no timestamps)
  space_id FK, amenity_id FK
  PRIMARY KEY (amenity_id, space_id)

workspace_types
  id, name, label   ← hot_desk | dedicated_desk | private_office | meeting_room

space_workspaces
  id, space_id FK, workspace_type_id FK
  capacity INT, price_hourly, price_daily, price_monthly DECIMAL, currency CHAR(3)

bookings
  id, customer_id FK→users, space_workspace_id FK
  start_date DATE, end_date DATE
  status ENUM(pending|confirmed|cancelled|completed), total_amount DECIMAL
  stripe_payment_intent_id nullable, created_at, updated_at

booking_days (double-booking guard)
  id, booking_id FK, space_workspace_id FK, day DATE
  UNIQUE KEY (space_workspace_id, day)
  ← checkout day NOT inserted (hotel model: end_date exclusive)

reviews
  id, booking_id FK UNIQUE, customer_id FK, space_id FK
  rating TINYINT, comment TEXT nullable

threads
  id, space_id FK, customer_id FK, host_id FK, created_at

messages
  id, thread_id FK, sender_id FK→users, body TEXT, read_at TIMESTAMP nullable

wishlists
  id, user_id FK cascadeOnDelete, space_id FK cascadeOnDelete, created_at, updated_at
  UNIQUE KEY (user_id, space_id)
  ← accessed via User::wishlistedSpaces() belongsToMany
```

---

## API route map (complete)

```
PUBLIC
  POST   /api/v1/register
  POST   /api/v1/login
  POST   /api/v1/logout            (auth:sanctum)
  GET    /api/v1/me                (auth:sanctum)
  GET    /api/v1/spaces
  GET    /api/v1/spaces/{space}
  GET    /api/v1/spaces/{space}/reviews
  GET    /api/v1/workspaces/{workspace}/availability
  GET    /api/v1/amenities
  GET    /api/v1/workspace-types

CUSTOMER (auth:sanctum)
  PATCH  /api/v1/profile
  PATCH  /api/v1/profile/password
  GET    /api/v1/wishlist
  POST   /api/v1/wishlist/{space}
  DELETE /api/v1/wishlist/{space}
  GET    /api/v1/bookings
  POST   /api/v1/bookings
  GET    /api/v1/bookings/{booking}
  PATCH  /api/v1/bookings/{booking}/cancel
  POST   /api/v1/bookings/{booking}/review
  GET    /api/v1/threads
  GET    /api/v1/threads/{thread}
  POST   /api/v1/threads/{thread}/messages
  POST   /api/v1/spaces/{space}/enquire

HOST (auth:sanctum + role:host) — prefix: /api/v1/host
  GET    /dashboard
  GET    /spaces                    (paginated)
  POST   /spaces                    (creates space + workspaces in DB::transaction)
  GET    /spaces/{space}
  PUT    /spaces/{space}
  DELETE /spaces/{space}
  POST   /spaces/{space}/photos
  DELETE /spaces/{space}/photos/{photoId}
  GET    /bookings
  GET    /bookings/{booking}
  PATCH  /bookings/{booking}/confirm
  PATCH  /bookings/{booking}/cancel

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

## Frontend routes

```
/                    → HomeView          (public)
/spaces              → SearchView        (public)
/spaces/:slug        → SpaceDetailView   (public)
/login               → LoginView         (guestOnly)
/register            → RegisterView      (guestOnly)
/bookings            → BookingsView      (requiresAuth)
/profile             → ProfileView       (requiresAuth)
/wishlist            → WishlistView      (requiresAuth)
/host                → HostDashboard     (requiresAuth, role: host)
/host/listings       → MyListings        (requiresAuth, role: host)
/host/listings/new   → CreateListing     (requiresAuth, role: host)
/host/bookings       → HostBookings      (requiresAuth, role: host)
/admin               → AdminDashboard    (requiresAuth, role: admin)
/admin/spaces        → AdminSpaces       (requiresAuth, role: admin)
```

Router `beforeEach`:
1. If `!auth.user && auth.token` → `await auth.fetchUser()`
2. If `auth.isAuthenticated` → `wishlistStore.ensureLoaded()` (fire-and-forget)
3. Redirect guests away from `requiresAuth` routes → `/login?redirect=...`
4. Redirect authenticated users away from `guestOnly` routes → role-appropriate dashboard

---

## Double-booking protection

`booking_days` UNIQUE index `(space_workspace_id, day)` acts as an atomic guard.
`BookingService` wraps insert in `DB::transaction` + `lockForUpdate`.
Carbon iterates start (inclusive) → end (exclusive) — checkout day is NOT inserted.

---

## Key patterns — follow exactly

**Controllers (thin):**
- Auth-scoped queries: `$request->user()->bookings()->...` (never `Booking::where('customer_id', ...)`)
- Always eager-load with `with([...])` — never N+1
- Return API Resources, never raw `->toArray()`
- Inline validation for simple updates (ProfileController style)
- Form Requests for complex validation (StoreSpaceRequest, StoreBookingRequest)

**Pinia stores:**
- Setup store pattern: `defineStore('name', () => { const x = ref(); return { x } })`
- No Options API stores

**Vue components:**
- `<script setup>` only — no Options API anywhere
- `@/utils/photoUrl` for ALL image src — never hardcode `/storage/` prefix
- `.sk` CSS class for skeleton shimmers (defined in `app.scss`)

**Migrations:**
- `return new class extends Migration`
- `$table->foreignId('x')->constrained()->cascadeOnDelete()`
- Named with timestamp prefix `YYYY_MM_DD_HHMMSS_`

**Space routing:**
- `Space::getRouteKeyName()` returns `'slug'` — ALL `{space}` route bindings resolve by slug
- This includes public routes, host routes, admin routes, and wishlist routes

**Photo URLs:**
- Cover photos stored as either local paths OR full `https://` URLs (Unsplash)
- Always use `photoUrl(path)` from `@/utils/photoUrl.js` — returns path as-is if `http`, else prepends `/storage/`

---

## Shimmer skeleton CSS

Global `.sk` class in `app.scss`:
```css
.sk {
    background-image: linear-gradient(90deg, #EEEAE3 0%, #E4DFD7 50%, #EEEAE3 100%);
    background-size: 200% 100%;
    animation: sk-shimmer 2s linear infinite;
    border-radius: 4px;
}
@keyframes sk-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
```

Use `.sk` divs with explicit `height`/`width` inline styles for content-shaped skeletons.
Never use Bootstrap `placeholder-glow` — use `.sk` instead.

---

## Demo seed data (run: `php artisan migrate:fresh --seed`)

| Role | Email | Password |
|---|---|---|
| admin | admin@cowork.test | password |
| host | host@cowork.test | password |
| host | host2@cowork.test | password |
| customer | customer@cowork.test | password |

- 9 spaces (7 published, 2 draft) across 2 hosts in Casablanca
- Cover photos: Unsplash full `https://` URLs
- 4 workspace types: hot_desk, dedicated_desk, private_office, meeting_room
- 11 bookings with realistic status mix
- Pricing in MAD (Moroccan Dirham)

---

## Build phases completed

- [x] Phase 0 — Project setup (Laravel + Vue 3 + Bootstrap 5 Sass + Sanctum + Git)
- [x] Phase 1 — Full database schema + all migrations + factories + seeders
- [x] Phase 2 — Auth end-to-end (Sanctum Bearer token ↔ Pinia store ↔ Vue route guards)
- [x] Phase 3 — API skeleton (all routes + controllers + resources + policies)
- [x] Phase 4 Slice 1 — Listings (FilterPipeline, SpaceCard, SearchView, SpaceDetailView, AppNav, spaces store)
- [x] Phase 4 Slice 2 — Booking engine (BookingService + CalendarPicker + checkout + BookingsView)
- [x] Phase 4 Slice 3 — Host dashboard (MyListings, CreateListing with workspaces, HostBookings, HostDashboard)
- [x] Phase 4 Slice 4 — Admin panel (AdminSpaces — approval/reject/unpublish)
- [x] Phase 4 Slice 5 — Demo seeder (realistic Casablanca data, Unsplash photos)
- [x] Phase 5 — Loading skeletons + empty states (all 5 list views, Linen & Forest)
- [x] Phase 6 — Profile & Settings (ProfileController, PATCH /profile + /profile/password, ProfileView)
- [x] Phase 7 — Wishlist (wishlists table, belongsToMany, WishlistController, wishlist store, heart button on SpaceCard, WishlistView)

---

## Next up (not yet built)

- [ ] Stripe Connect integration (host onboarding + payment split on booking)
- [ ] Space edit flow (`/host/listings/:slug/edit`)
- [ ] Admin user management view (`/admin/users`)
- [ ] Messages / threads UI
- [ ] Review submission flow (post-stay)
- [ ] Space detail skeleton loading state

---

## Key decisions & reasons

| Decision | Why |
|---|---|
| Sanctum Bearer tokens (not cookies) | Simpler for SPA + mobile, token in localStorage |
| Pinia not Vuex | Vuex is maintenance-only for Vue 3 |
| Composition API only | Consistency + composables pay off at scale |
| `booking_days` UNIQUE index | Atomic double-booking guard at DB level |
| Single Axios instance | One place for interceptors, token attachment, 401 redirect |
| BookingService in transaction | lockForUpdate + booking_days insert atomic; 422 on conflict |
| Checkout day excluded | Hotel model — end_date is checkout, not a booked night |
| `photoUrl()` utility | External Unsplash URLs can't have `/storage/` prepended |
| `.sk` shimmer not Bootstrap placeholder | Sweeping gradient shimmer, Linen & Forest tones |
| `belongsToMany` for wishlist | No Wishlist model needed, pivot handles uniqueness via DB constraint |
| `syncWithoutDetaching` for wishlist add | Idempotent — second save never throws duplicate key error |
| Space slug as route key | `Space::getRouteKeyName()` = `'slug'` — consistent across all route groups |
| `ensureLoaded()` in router guard | Wishlist state ready before first SpaceCard renders, fire-and-forget |
| Inline validation in ProfileController | Simple 2-field updates don't warrant a Form Request class |
