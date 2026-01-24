# Authentication Tests

This directory contains tests for the authentication system.

## Test Structure

```
tests/
├── bootstrap.php         # Test bootstrap - sets up tenant context
├── TestCase.php          # Base test class for tenant tests
├── Unit/
│   └── Auth/
│       └── Models/
│           ├── UserTest.php
│           └── SocialAccountTest.php
└── Feature/
    └── Auth/
        ├── LoginTest.php
        ├── RegisterTest.php
        └── LogoutTest.php
```

## Running Tests

### From Tenant Folder (Recommended):

```bash
# Navigate to tenant folder
cd tenants/flashcards

# Run all tests
../../vendor/bin/phpunit

# Run with colors
../../vendor/bin/phpunit --colors=always

# Run unit tests only
../../vendor/bin/phpunit --testsuite Unit

# Run feature tests only
../../vendor/bin/phpunit --testsuite Feature

# Run specific test class
../../vendor/bin/phpunit tests/Unit/Auth/Models/UserTest.php

# Run with filter
../../vendor/bin/phpunit --filter UserTest

# Run with verbose output
../../vendor/bin/phpunit -v
```

### From Larabis Root (Alternative):

```bash
# Run tenant tests from Larabis root
php artisan test tenants/flashcards/tests/ --filter Auth
```

**Note:** Running from Larabis root may have autoloading issues. The recommended approach is running from the tenant folder.

## Test Coverage

### Unit Tests (Models)

**UserTest:**
- User creation
- Social accounts relationship
- Social account lookup methods
- Password hashing
- Password hiding from serialization

**SocialAccountTest:**
- Social account creation
- User relationship
- Token expiration checking
- Unique constraints
- Cascade deletion

### Feature Tests (Controllers)

**LoginTest:**
- Login form display
- Successful login
- Failed login with invalid credentials
- Validation errors
- Remember me functionality

**RegisterTest:**
- Registration form display
- Successful registration
- Password hashing
- Auto-login after registration
- All validation rules

**LogoutTest:**
- Successful logout
- Session invalidation
- Guest access protection

## Dependencies

Tests require:
- User model with migrations
- SocialAccount model with migrations
- Auth routes registered
- Test database configured

## Notes

- OAuth tests (SocialAuthController) are not included yet as they require Socialite mocking
- Tests assume routes are registered in Larabis main app routing
- Tests use RefreshDatabase trait for clean state between tests
