# Aries Restro

QR-based contactless table ordering SaaS. A customer scans a QR code at their restaurant table, orders from their own phone (no app, no login), the order reaches the kitchen and waiter instantly, and the table pays one combined bill via Razorpay. Built as multi-tenant SaaS — Aries Innovation (Super Admin) onboards independent hotels/cafes/restaurants, each with their own menu, staff, tables, and payment settings.

Five apps live in this one Laravel codebase:

| App | Who | URL prefix |
|---|---|---|
| **Super Admin** | Aries Innovation's own team — sees/manages every hotel | `/super-admin` |
| **Hotel Admin** | One hotel's own dashboard — scoped to that hotel only | `/hotel-admin` |
| **Waiter** | Table grid, order management, calls | `/waiter` |
| **Chef** | Kitchen queue, stock availability | `/chef` |
| **Customer** | No-login QR ordering flow | `/order/...` |

---

## Tech stack

- **Laravel 13** (PHP 8.3) + MySQL
- Blade views, vanilla JS/CSS (no frontend framework, no Vite build — plain files in `public/css` and `public/js`)
- `spatie/laravel-permission` (roles), `spatie/laravel-activitylog` (audit trail), `endroid/qr-code` (QR generation), `laravel/reverb` (WebSocket realtime, with polling fallback)

---

## Setup

```bash
composer install
cp .env.example .env        # then edit DB_* to match your MySQL instance
php artisan key:generate
php artisan migrate --seed
php artisan storage:link    # needed for menu item photo uploads
```

Then serve it however you normally do (`php artisan serve`, or point your web server's document root at `public/`).

### Environment notes specific to this project

- **MySQL storage engine**: if your MySQL/MariaDB instance's `default_storage_engine` isn't InnoDB (some shared local dev setups default to MyISAM, which has no foreign-key support), `config/database.php`'s `mysql` connection already forces `'engine' => 'InnoDB'` — you don't need to change anything, but if migrations fail with a "max key length" or FK error, that's the symptom to check for.
- **No GD extension assumed**: QR codes are generated as SVG (`endroid/qr-code`'s `SvgWriter`), not PNG, specifically so this works without the PHP GD extension installed. If you do have GD and want raster QR images instead, swap `SvgWriter` for `PngWriter` in `app/Services/QrCodeService.php`.
- **Mail**: `.env` ships with `MAIL_MAILER=log`, so password-reset emails aren't actually sent anywhere — they're written to `storage/logs/laravel.log`. Open that file and search for "Reset Password" to get the link during local testing. Point `MAIL_MAILER` at a real driver (smtp, ses, etc.) before this goes anywhere near production.

---

## URLs

Assuming `APP_URL=http://localhost:8000` (adjust to your actual host):

| Screen | URL |
|---|---|
| Home / marketing page ("Request a demo" leads land in Super Admin → Demo requests) | `/` |
| Staff login (shared by all 4 staff roles) | `/login` |
| Forgot password (Super Admin/Hotel Admin only — Waiter/Chef/Manager are reset by their Hotel Admin) | `/forgot-password` |
| Super Admin dashboard | `/super-admin/dashboard` |
| Hotel Admin dashboard | `/hotel-admin/dashboard` |
| Waiter tables | `/waiter/tables` |
| Chef queue | `/chef/queue` |
| Customer ordering | `/order/{hotel-slug}/{table-uuid}` — **there is no generic customer URL**; every customer session starts from a specific table's QR code. Get one from **Hotel Admin → Tables → QR code** (or **Super Admin → Tables & QR codes**, which works for any hotel). |

All staff routes (`/super-admin/*`, `/hotel-admin/*`, `/waiter/*`, `/chef/*`) require login and the matching role — visiting the wrong one gets a 403, not a redirect.

---

## Seeded accounts

One demo hotel ("Aries Café", Ahmedabad) is seeded with one account per role. **Password for all four is `ChangeMe@123`** — this is a dev-only seed password, rotate it before any real deployment.

| Role | Email | Employee ID | Lands on |
|---|---|---|---|
| Super Admin | `tech.ariesinnovation@gmail.com` | — | `/super-admin/dashboard` |
| Hotel Admin | `admin@ariescafe.test` | `ADM-0001` | `/hotel-admin/dashboard` |
| Waiter | `rahul.sharma@ariesrestro.test` | `WTR-1042` | `/waiter/tables` |
| Chef | `anita.verma@ariesrestro.test` | `CHF-0231` | `/chef/queue` |

The login form accepts either the email or the employee ID.

**Password resets are role-aware**: Super Admin and Hotel Admin can self-service reset via `/forgot-password` (real email, logged to `storage/logs/laravel.log` per the mail note above). Waiter/Chef/Manager accounts are managed by their Hotel Admin instead — `/forgot-password` tells them to contact their Hotel Admin, and no email is sent (Hotel Admin resets a staff member's password from **Hotel Admin → Staff → Edit**, leaving the password field blank to keep it unchanged).

---

## Demo data

`php artisan migrate:fresh --seed` (or the plain `--seed` from setup above) now leaves the database in a **clean, real-world starting state** — ready for genuine end-to-end testing (scan an actual QR code, place a real waiter call, etc.) rather than always showing pre-populated demo activity:

- 8 tables at "Aries Café", all `available` — no pre-existing carts, sessions, or waiter calls.
- 10 menu items across 4 categories (Starters, Main Course, Beverages, Desserts), matching the finalized Customer app's exact names/prices/ratings.
- 1 active standard-plan subscription for the demo hotel.

If you want the database to instead look like the finalized UI screenshots (Table 5 mid-order, Tables 2/3/7 with kitchen activity, 2 pending waiter calls) — e.g. for a visual demo — run this **in addition** to the base seed:

```bash
php artisan db:seed --class=DemoShowcaseSeeder
```

---

## Project structure

```
app/
  Http/
    Controllers/
      Auth/            login, logout, forgot/reset password
      Customer/         QR-scan session start, menu, cart, track, bill, pay
      Waiter/, Chef/    staff apps
      HotelAdmin/       tables, menu, staff, profile — scoped to their own hotel
      SuperAdmin/       hotels, subscriptions, payment settings, activity log,
                        + platform-wide tables/sales/customers (all hotels)
    Middleware/
      ResolveTableSession.php   resolves the customer's table/session from an httpOnly cookie
    Requests/           one FormRequest per write action (validation lives here, not in controllers)
  Services/             business logic lives here, NOT in controllers — see note below
  Models/               one Eloquent model per table, with a BelongsToHotel trait for
                        automatic multi-tenant scoping (see app/Models/Concerns/BelongsToHotel.php)
  Enums/                native PHP enums for every status column (OrderStatus, TableStatus, etc.)

resources/views/
  layouts/               app.blade.php (customer/waiter/chef/login), admin.blade.php (both admin panels)
  customer/, waiter/, chef/, hoteladmin/, superadmin/, auth/

public/css/, public/js/   plain files, one per role, no build step — referenced directly via asset()

database/
  migrations/            14 domain migrations + the Spatie permission/activitylog ones
  seeders/                see "Demo data" above for what each one does
```

### Why a service layer

Controllers stay thin — they validate (via `FormRequest`), call a service method, and redirect/return a view. All mutation logic (creating/updating records, image upload handling, the "restrictOnDelete → fall back to a friendly message" pattern, session/cookie assignment, role-based redirects) lives in `app/Services/`, organized to mirror the controller structure it serves (`Services/HotelAdmin/`, `Services/SuperAdmin/`, `Services/Customer/`, `Services/Auth/`, plus the shared `QrCodeService`).

This is deliberate groundwork for turning this into an API backend later: when that happens, a new set of API controllers can call the exact same service classes instead of duplicating business logic — the services don't know or care whether they were called from a Blade-rendering web controller or a JSON API controller.

### Multi-tenant isolation

Every hotel-scoped model (`Table`, `MenuCategory`, `MenuItem`, `OrderSession`, `Payment`, `Subscription`, `HotelPaymentSetting`) uses the `BelongsToHotel` trait (`app/Models/Concerns/BelongsToHotel.php`): a global Eloquent scope that automatically restricts every query to the logged-in user's own `hotel_id`, and auto-fills `hotel_id` on create. A Hotel Admin can't see or touch another hotel's data — not through the UI, not by editing a URL — and this is enforced at the model layer, not by remembering to add `where('hotel_id', ...)` in every controller. It's inert for Super Admin (`hotel_id` is `null`), which is exactly what makes the platform-wide Super Admin views (all hotels' tables/sales/customers) work with zero extra scoping code.

`User` is the one exception — staff identity isn't "tenant data" in the same sense, so it doesn't carry the trait. Cross-hotel staff access is blocked with an explicit check instead (see `StaffService::authorizeSameHotel()`).

---

## What's still demo / not real yet

Being upfront about the boundary, since a lot of this app *looks* fully functional but isn't wired to persist yet:

- **Cart & order placement**: the customer menu/cart pages are client-side JS only (`sessionStorage`), matching the finalized UI's interactive demo. "Place order" doesn't hit the database yet.
- **Waiter/Chef actions**: cancel item, transfer table, mark item status, toggle stock — all show a toast and update the DOM, but don't persist. The underlying data (tables, menu, staff, order history) is real; these specific actions aren't yet. ("Call Waiter" and "Mark attended" **are** real now — see below.)
- **Payments**: the Razorpay flow is a UI walkthrough only — no real Razorpay integration yet.
- **Realtime**: wired — see "Real-time order flow" below.

Real and persisting to MySQL: auth, role-based access control, multi-tenant isolation, the full CRUD for hotels/subscriptions/payment settings/tables/menu/staff, QR code generation (any hotel, from Super Admin or Hotel Admin), the visual theme, the Home page's demo-request lead capture, and:

- **"Call Waiter"**: a customer tapping it creates a real `WaiterCall` row. Any open Waiter screen polls every ~8s and plays an audible bell (synthesized via the Web Audio API — no sound file to manage) the moment a genuinely new call arrives; "Mark attended" on the Waiter Calls page is a real status update, not a demo toggle.
- **Super Admin at scale**: Tables & QR codes is a searchable hotel picker rather than one flat list — built for the 100+ hotel case, not just the single demo hotel. Sales and Active Customers get a `?hotel=` filter for the same reason.

## Real-time order flow

Customer places an order → **Chef** hears a ~2s tone and sees the card appear → chef taps *Start preparing / Mark ready* → **Waiter** hears a chime when items are ready and sees table colours/counts update → **Customer**'s status page updates and chimes. No page refreshes.

- **Start the socket server**: run `start-realtime.bat` (or `php artisan reverb:start`) and keep it open. `.env` needs `BROADCAST_CONNECTION=reverb` plus the `REVERB_*` values (see `.env.example`).
- **Fallback**: every live screen also polls every ~10s, so if Reverb is stopped the screens still update (a red dot bottom-left shows "offline"; green = live).
- **Sound**: browsers block audio until you tap the page once — a "Tap to enable sound" chip stays visible until that has happened; tap it afterwards to mute/unmute.
- **Channels**: staff use the private channel `hotel.{id}.orders` (only that hotel's chef/waiter/manager/admin); customers use a public channel keyed by an HMAC of their session id (never the session token).
- **Code**: events `OrderPlaced` / `OrderItemStatusChanged`; Livewire `Chef\QueueBoard`, `Waiter\TablesGrid`, `Waiter\TableDetail`, `Customer\TrackOrder`; JS `public/js/realtime.js` + `notify.js`.
---

## Tests

```bash
php artisan test
```

28 feature tests covering: role-based route access (a waiter hitting a chef route gets a 403, etc.), multi-tenant isolation (a Hotel Admin only ever sees their own hotel's data, proven with two real hotels in the test), full create→edit→delete cycles with activity-log verification, the QR-scan session-resolution flow (cookie-based, member-number reuse), the forgot-password flow's role-aware behavior, the real waiter-call → polling → attend cycle (including a different-hotel waiter correctly getting 403'd), and Super Admin's hotel-scoped tables/sales/customers views plus the demo-request lead-capture flow. Tests run against an in-memory SQLite database (see `phpunit.xml`), independent of your local MySQL setup.

---

## Attribution

Built with [Claude Code](https://claude.com/claude-code).
