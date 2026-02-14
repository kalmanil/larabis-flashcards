# Flashcards Tenant

Flashcards tenant for **Larabis**, a multi-tenant Laravel application. This package provides authentication and tenant-specific behavior for the flashcards product.

## Overview

- **Package:** `tenants/flashcards`
- **Type:** Library (loaded by Larabis when the flashcards tenant is active)
- **Features:** Email/password auth, registration, logout, social login (Facebook, Google), user avatars, tenant-specific page data, and **Flashcards** (Hebrew words, shoresh, transcriptions, RU/EN translations, learning sessions)

## Requirements

- Larabis host application (root project). Align with Larabis: PHP ^8.2, Laravel ^12.0.
- [Laravel Socialite](https://laravel.com/docs/socialite) for OAuth (Facebook, Google)

## Project Structure

```
tenants/flashcards/
├── bootstrap/
│   └── autoload.php       # Tenant autoloader; add new App\Features\* prefixes here
├── app/Features/
│   ├── Auth/
│   │   ├── Controllers/     # Login, Logout, Register, SocialAuth
│   │   └── Models/          # User, SocialAccount
│   ├── Flashcards/
│   │   ├── Http/Controllers/   # Dashboard, Word, Deck, Learn
│   │   ├── Http/Requests/     # StoreHebrewFormRequest, UpdateHebrewFormRequest
│   │   ├── Models/           # Language, Shoresh, HebrewForm, Translation, Deck, DeckCard, UserCardProgress
│   │   └── Policies/         # DeckPolicy
│   └── Pages/
│       ├── Admin/
│       │   └── PageDataService.php   # Admin view page data (extends default)
│       └── Default/
│           └── PageDataService.php   # Default/landing view page data, DB check, config
├── database/migrations/    # Tenant DB: social_accounts, users.avatar, flashcards tables
├── database/seeders/       # LanguageSeeder (he, ru, en)
├── resources/views/         # default + admin Blade views (login, register, home, flashcards/*)
├── routes/web.php          # Auth + social + flashcards routes
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

### Flashcards (auth required, prefix `/admin`)

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/admin` | `flashcards.dashboard` | Dashboard (Start learning, Add words, Browse words, My cards) |
| GET | `/admin/words` | `flashcards.words.index` | Browse tenant word pool |
| GET | `/admin/words/create` | `flashcards.words.create` | Add word form |
| POST | `/admin/words` | `flashcards.words.store` | Store word |
| GET | `/admin/words/{id}/edit` | `flashcards.words.edit` | Edit word |
| PUT | `/admin/words/{id}` | `flashcards.words.update` | Update word |
| DELETE | `/admin/words/{id}` | `flashcards.words.destroy` | Delete word |
| POST | `/admin/words/{id}/add-to-deck` | `flashcards.words.add-to-deck` | Add to my deck |
| GET | `/admin/decks` | `flashcards.decks.index` | Redirect to default deck |
| GET | `/admin/decks/{id}` | `flashcards.decks.show` | My cards |
| DELETE | `/admin/decks/{id}/cards/{id}` | `flashcards.decks.remove-card` | Remove from deck |
| GET | `/admin/learn` | `flashcards.learn.config` | Session config (lang, front type) |
| POST | `/admin/learn/start` | `flashcards.learn.start` | Start session |
| GET | `/admin/learn/session` | `flashcards.learn.session` | Current card |
| POST | `/admin/learn/answer` | `flashcards.learn.answer` | Submit known/not known |
| POST | `/admin/learn/reset` | `flashcards.progress.reset` | Reset all progress |

## Page Data

Follows Larabis page-data architecture: **service classes** (not traits), implementing `PageDataServiceInterface`. Resolved by `PageDataServiceFactory` from tenant + view. Base classes live in Larabis (`app/Features/Pages/Base/Default/`, `Base/Admin/`); this tenant extends them with same class name `PageDataService` in `Default/` and `Admin/`.

- **Default** (`Pages/Default/PageDataService.php`): Landing view — `flashcardsConfig` (name, version, description) and `dbConnection` (tenant DB status).
- **Admin** (`Pages/Admin/PageDataService.php`): Admin view — merges default page data with admin config (`view_type`, `requires_auth`) and adds `getAdminDashboardData()` (DB status, panel flag, stats, activity, notifications).

**View naming (Larabis rule):** View names must NOT include the view code. Use `'home'`, `'login'`, `'register'` (not `'admin.home'`). Larabis builds the full view path as `tenants.{tenant_id}.{code}.{view_name}` (e.g. `tenants.flashcards.admin.home`).

## Database (Tenant)

- **users:** Extended with nullable `avatar` (migration in this repo).
- **social_accounts:** `user_id`, `provider`, `provider_id`, tokens, `expires_at`; unique on `(provider, provider_id)`; cascade delete from user.
- **Flashcards:** `languages`, `shoresh`, `hebrew_forms` (with frequency_rank, frequency_per_million), `translations`, `hebrew_form_translation`, `decks`, `deck_cards`, `user_card_progress`.

Migrations are intended to be run via `tenants:migrate` from Larabis with the path above.

**Languages:** Seeded automatically by migration `2026_02_03_000009_seed_languages_table.php` (he, ru, en).

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
