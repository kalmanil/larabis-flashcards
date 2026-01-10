# Flashcards Tenant Git Workflow

## Overview
This workflow enables safe testing on production server with a staging environment before releasing to production.

## Branch Strategy

```
main (Production)
  └── staging (Testing on Production Server)
      ├── feature/add-home-views
      ├── feature/auth-system
      └── hotfix/critical-fix
```

### Branches Explained

- **`main`**: Production-ready code that runs on live production server
- **`staging`**: Testing branch that deploys to production server (test environment/subdomain)
- **`feature/*`**: Development branches for new features
- **`hotfix/*`**: Critical fixes that need immediate production deployment

## Workflow Process

### 1. Development Workflow

#### Starting a New Feature
```bash
# Ensure staging is up to date
git checkout staging
git pull origin staging

# Create feature branch from staging
git checkout -b feature/my-feature-name

# Make changes and commit frequently
git add .
git commit -m "feat(scope): description of changes"
git push -u origin feature/my-feature-name
```

#### Completing a Feature
```bash
# Ensure feature branch is up to date with staging
git checkout feature/my-feature-name
git pull origin staging
git rebase staging  # or merge: git merge staging

# Push updated feature branch
git push origin feature/my-feature-name
# (use --force-with-lease if rebased, but be careful)

# Create Pull Request: feature/my-feature-name → staging
```

### 2. Testing on Production Server (Staging Branch)

```bash
# Merge feature to staging (after PR approval)
git checkout staging
git pull origin staging
git merge feature/my-feature-name
git push origin staging

# Deploy staging to production server (test environment)
# This deploys to production server but in a safe test environment/subdomain
```

**Staging Environment Setup:**
- Deploy `staging` branch to: `staging.yourdomain.com` or `test.yourdomain.com`
- Use same production server but different subdomain/port
- Same database structure but separate test database
- All features tested here before moving to `main`

### 3. Production Release Workflow

After testing on staging (production server test environment):

```bash
# Merge staging to main (after successful testing)
git checkout main
git pull origin main
git merge staging
git push origin main

# Tag the release
git tag -a v1.0.0 -m "Release version 1.0.0: description"
git push origin main --tags

# Deploy main to actual production
# This deploys to production domain: yourdomain.com
```

### 4. Hotfix Workflow (Emergency Fixes)

For critical production issues:

```bash
# Create hotfix from main
git checkout main
git pull origin main
git checkout -b hotfix/critical-bug-description

# Make fix, commit, and push
git add .
git commit -m "fix(scope): critical bug fix"
git push -u origin hotfix/critical-bug-description

# Create PR: hotfix/critical-bug-description → main
# After merge, also merge to staging
git checkout staging
git merge main
git push origin staging
```

## Testing on Production Server

### Setup Options

**Option 1: Staging Subdomain (Recommended)**
```
Production Server:
- Main domain: flashcards.yourdomain.com (uses `main` branch)
- Staging domain: staging-flashcards.yourdomain.com (uses `staging` branch)
```

**Option 2: Port-based Staging**
```
Production Server:
- Main: yourdomain.com:80 (uses `main` branch)
- Staging: yourdomain.com:8000 (uses `staging` branch)
```

**Option 3: Environment Variable Toggle**
```
Production Server with APP_ENV:
- APP_ENV=production → uses `main` branch
- APP_ENV=staging → uses `staging` branch
```

### Deployment Commands (Example)

**Deploy Staging to Production Server:**
```bash
# On production server
cd /path/to/application
git fetch origin
git checkout staging
git pull origin staging
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**Deploy Main to Production:**
```bash
# On production server
cd /path/to/application
git fetch origin
git checkout main
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Pre-Deployment Checklist

Before merging `staging` → `main`:

- [ ] All features tested on staging environment (production server)
- [ ] Database migrations tested
- [ ] No hardcoded URLs or environment-specific values
- [ ] All routes working correctly
- [ ] Views rendering properly
- [ ] Database connections verified
- [ ] No console errors or warnings
- [ ] Performance acceptable
- [ ] Security review completed (if applicable)
- [ ] Code review completed

## Commit Message Convention

Follow this format:
```
type(scope): brief description

Detailed explanation if needed

Closes #issue-number
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(views): add admin and default home page views

Add tenant-specific Blade templates with system status display
and database connection checking.

fix(db): resolve connection timeout issue

chore(deps): update Laravel to latest version
```

## Branch Protection Rules (Recommended)

Enable on GitHub/GitLab:

**For `main` branch:**
- Require pull request reviews before merging
- Require status checks to pass
- Require branches to be up to date before merging
- Restrict force pushes
- Require linear history (no merge commits)

**For `staging` branch:**
- Require pull request reviews (optional, but recommended)
- Allow force pushes only for maintainers (if needed)
- Require status checks (if you have CI/CD)

## Daily Workflow Example

```bash
# Morning: Start new feature
git checkout staging
git pull origin staging
git checkout -b feature/add-user-profile

# Work, commit frequently
git add .
git commit -m "feat(profile): add user profile view"
git push -u origin feature/add-user-profile

# Afternoon: Finish feature, create PR
git checkout staging
git pull origin staging
# Create PR on GitHub/GitLab: feature/add-user-profile → staging

# After PR merge: Test on production server (staging)
git checkout staging
git pull origin staging
# Deploy staging to production server test environment
# Test thoroughly

# After successful testing: Release to production
git checkout main
git pull origin main
git merge staging
git push origin main
# Deploy main to production
```

## Troubleshooting

### Merge Conflicts
```bash
git checkout staging
git pull origin staging
git checkout feature/my-feature
git rebase staging
# Resolve conflicts, then:
git add .
git rebase --continue
```

### Reverting a Bad Deployment
```bash
# Find the last good commit
git log --oneline

# Revert to that commit
git checkout main
git reset --hard <commit-hash>
git push origin main --force  # Only if absolutely necessary
```

### Emergency Rollback
```bash
# Revert last merge
git checkout main
git revert -m 1 HEAD
git push origin main
```

## Best Practices

1. **Never commit directly to `main`** - Always use feature branches
2. **Test on staging first** - Always test on production server staging environment
3. **Small, frequent commits** - Easier to review and rollback
4. **Descriptive commit messages** - Helps with debugging and history
5. **Keep branches up to date** - Regularly sync with staging/main
6. **Delete merged branches** - Keep repository clean
7. **Use tags for releases** - Easier to track production versions
8. **Document breaking changes** - In commit messages and release notes
