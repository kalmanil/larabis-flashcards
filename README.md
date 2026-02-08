# Flashcards Tenant

Flashcards tenant for **Larabis**, a multi-tenant Laravel application. This package provides authentication and tenant-specific behavior for the flashcards product.

## Overview

- **Package:** `tenants/flashcards`
- **Type:** Library (loaded by Larabis when the flashcards tenant is active)
- **Features:** Email/password auth, registration, logout, social login (Facebook, Google), user avatars, and tenant-specific page data (default + admin views)

## Requirements

- Larabis host application (root project). Align with Larabis: PHP ^8.2, Laravel ^12.0.
- [Laravel Socialite](https://laravel.com/docs/socialite) for OAuth (Facebook, Google)

## Project Structure

```
tenants/flashcards/
├── app/Features/
│   ├── Auth/
│   │   ├── Controllers/     # Login, Logout, Register, SocialAuth
│   │   └── Models/          # User, SocialAccount
│   └── Pages/
│       ├── Admin/
│       │   └── PageDataService.php   # Admin view page data (extends default)
│       └── Default/
│           └── PageDataService.php   # Default/landing view page data, DB check, config
├── database/migrations/    # Tenant DB: social_accounts, users.avatar
├── resources/views/         # default + admin Blade views (login, register, home)
├── routes/web.php          # Auth + social routes
├── tests/
│   ├── Unit/Auth/Models/
│   └── Feature/Auth/
├── composer.json
├── phpunit.xml
└── README.md
```

## Setup

This tenant does not run standalone. Install and use it from the **Larabis root**:

1. Ensure the tenant is registered in Larabis (tenant ID: `flashcards`).
2. Run tenant migrations from Larabis root:

   ```bash
   php artisan tenants:migrate --tenants=flashcards --path=tenants/flashcards/database/migrations
   ```

3. Configure Socialite in Larabis for Facebook/Google if using social login.

## Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/login` | `login.form` | Login form |
| POST | `/login` | `login` | Submit login |
| POST | `/logout` | `logout` | Logout |
| GET | `/register` | `register` | Registration form |
| POST | `/register` | — | Submit registration |
| GET | `/auth/{provider}/redirect` | `social.redirect` | Redirect to OAuth provider |
| GET | `/auth/{provider}/callback` | `social.callback` | OAuth callback |

`{provider}` is restricted to `facebook` or `google`.

## Page Data

Follows Larabis page-data architecture: **service classes** (not traits), implementing `PageDataServiceInterface`. Resolved by `PageDataServiceFactory` from tenant + view. Base classes live in Larabis (`app/Features/Pages/Base/Default/`, `Base/Admin/`); this tenant extends them with same class name `PageDataService` in `Default/` and `Admin/`.

- **Default** (`Pages/Default/PageDataService.php`): Landing view — `flashcardsConfig` (name, version, description) and `dbConnection` (tenant DB status).
- **Admin** (`Pages/Admin/PageDataService.php`): Admin view — merges default page data with admin config (`view_type`, `requires_auth`) and adds `getAdminDashboardData()` (DB status, panel flag, stats, activity, notifications).

**View naming (Larabis rule):** View names must NOT include the view code. Use `'home'`, `'login'`, `'register'` (not `'admin.home'`). Larabis builds the full view path as `tenants.{tenant_id}.{code}.{view_name}` (e.g. `tenants.flashcards.admin.home`).

## Database (Tenant)

- **users:** Extended with nullable `avatar` (migration in this repo).
- **social_accounts:** `user_id`, `provider`, `provider_id`, tokens, `expires_at`; unique on `(provider, provider_id)`; cascade delete from user.

Migrations are intended to be run via `tenants:migrate` from Larabis with the path above.

## Testing

Tests run from the **tenant directory** and bootstrap the Larabis app. They use an in-memory SQLite database and tenant env vars (`DOMAIN_TENANT_ID=flashcards`, etc.).

From `tenants/flashcards`:

```bash
# All tests
../../vendor/bin/phpunit

# Suites
../../vendor/bin/phpunit --testsuite Unit
../../vendor/bin/phpunit --testsuite Feature

# Single file or filter
../../vendor/bin/phpunit tests/Unit/Auth/Models/UserTest.php
../../vendor/bin/phpunit --filter LoginTest
```

See `tests/README.md` for test layout and coverage notes.

## License

Proprietary.
