# Flashcards Tenant

Flashcards tenant for **Larabis**, a multi-tenant Laravel application. This package provides authentication and tenant-specific behavior for the flashcards product.

## Overview

- **Package:** `tenants/flashcards`
- **Type:** Library (loaded by Larabis when the flashcards tenant is active)
- **Features:** Email/password auth, registration, logout, social login (Facebook, Google), user avatars, and tenant-specific page logic

## Requirements

- Larabis host application (root project)
- PHP 8.x
- Laravel (version required by Larabis)
- [Laravel Socialite](https://laravel.com/docs/socialite) for OAuth (Facebook, Google)

## Project Structure

```
tenants/flashcards/
├── app/Features/
│   ├── Auth/
│   │   ├── Controllers/     # Login, Logout, Register, SocialAuth
│   │   └── Models/          # User, SocialAccount
│   └── Pages/
│       ├── Traits/          # Default tenant PageLogic
│       └── Views/admin/Traits/
├── database/migrations/     # Tenant DB: social_accounts, users.avatar
├── resources/views/         # default + admin Blade views (login, register, home)
├── routes/web.php           # Auth + social routes
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
