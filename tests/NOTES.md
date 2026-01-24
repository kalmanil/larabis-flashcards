# Test Notes

## Test Status

| Test Type | Status | Notes |
|-----------|--------|-------|
| Unit Tests (Models) | **Ready** | Uses SQLite for fast testing |
| Feature Tests | **Skipped** | Waiting for auth routes (Phase 5) |

## Running Tests

```bash
cd tenants/flashcards
../../vendor/bin/phpunit
```

Expected output:
- Unit tests should **pass**
- Feature tests will show as **SKIPPED** (not failed)

## Testing Strategy

### Production vs Testing

| Environment | Database | Tables |
|-------------|----------|--------|
| **Production** | MySQL `tenant_flashcards` | users, social_accounts |
| **Testing** | SQLite in-memory | Same tables (created by TestCase) |

### How It Works

1. PHPUnit uses SQLite in-memory for fast, isolated tests
2. `RefreshDatabase` trait creates fresh database each test
3. `TestCase::createTenantTables()` creates `social_accounts` table
4. Tests run against SQLite (fast!)
5. Production uses MySQL (real tenant database)

## Unit Tests

Unit tests use helper methods instead of factories:

```php
// Helper methods in each test class
protected function createTestUser(array $attributes = []): User
protected function createSocialAccount(User $user, array $attributes = []): SocialAccount
```

## Feature Tests

Feature tests are **skipped** until auth routes are registered.

To enable feature tests:
1. Complete Phase 5 (Register auth routes in Larabis)
2. Remove `$this->skipUntilRoutesRegistered();` from each test method

## Troubleshooting

### "Table 'social_accounts' doesn't exist"
The TestCase should create this table automatically. Check `TestCase::createTenantTables()`.

### "Table 'users' doesn't exist"
Run Larabis migrations:
```bash
cd /path/to/larabis
php artisan migrate
```

### Feature tests still failing after Phase 5
Remove the skip call from each test method:
```php
// Remove this line from each test:
$this->skipUntilRoutesRegistered();
```
