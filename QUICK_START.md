# Quick Start Guide - Production Testing Workflow

## 🚀 Setup (One-Time)

### 1. Create Staging Branch (Already done if you see this)
```bash
git checkout main
git pull origin main
git checkout -b staging
git push -u origin staging
```

### 2. Configure Git (If not already done)
```bash
git config core.autocrlf true  # For Windows line endings
```

## 📋 Daily Workflow

### Starting a New Feature
```bash
# 1. Update staging branch
git checkout staging
git pull origin staging

# 2. Create feature branch
git checkout -b feature/description-of-feature

# 3. Make changes, commit frequently
git add .
git commit -m "feat(scope): what you did"
git push -u origin feature/description-of-feature

# 4. Create Pull Request on GitHub/GitLab
#    From: feature/description-of-feature
#    To: staging
```

### Testing on Production Server

After your PR is merged to `staging`:

```bash
# On production server (staging environment)
cd /var/www/flashcards-staging
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

**Test on:** `staging-flashcards.yourdomain.com` or your staging URL

### Releasing to Production

After successful testing on staging:

```bash
# 1. Merge staging to main
git checkout main
git pull origin main
git merge staging
git push origin main

# 2. Tag the release (optional but recommended)
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin main --tags

# 3. Deploy to production (on production server)
cd /var/www/flashcards
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

## 🔥 Hotfix Workflow (Emergency Fixes)

```bash
# 1. Create hotfix from main
git checkout main
git pull origin main
git checkout -b hotfix/critical-bug-fix

# 2. Fix the issue, commit
git add .
git commit -m "fix(scope): critical bug fix"
git push -u origin hotfix/critical-bug-fix

# 3. Create PR: hotfix/* → main
# 4. After merge, also update staging
git checkout staging
git merge main
git push origin staging
```

## 📊 Workflow Summary

```
Feature Development:
feature/* → (PR) → staging → (Test on Production Server) → (PR) → main → (Deploy Production)

Hotfix:
hotfix/* → (PR) → main → (Merge to) → staging
```

## ✅ Pre-Deployment Checklist

Before deploying staging to production:
- [ ] Code tested locally
- [ ] All tests passing (if you have tests)
- [ ] No hardcoded URLs
- [ ] Database migrations tested
- [ ] Views rendering correctly
- [ ] Routes working
- [ ] No console errors
- [ ] Code reviewed (if team project)

Before deploying main to production:
- [ ] ✅ All above checklist items
- [ ] ✅ Tested on staging (production server test environment)
- [ ] ✅ All features working on staging
- [ ] ✅ Performance acceptable
- [ ] ✅ Backup created
- [ ] ✅ Rollback plan ready

## 🆘 Quick Commands

```bash
# See current branch
git branch

# See all branches
git branch -a

# Switch to staging
git checkout staging
git pull origin staging

# See what's different between staging and main
git log main..staging

# Delete local feature branch after merge
git branch -d feature/my-feature

# Delete remote feature branch (after PR merge)
git push origin --delete feature/my-feature

# Undo last commit (keep changes)
git reset --soft HEAD~1

# See commit history
git log --oneline -10
```

## 📚 More Information

- **Full Workflow:** See [WORKFLOW.md](WORKFLOW.md)
- **Deployment Details:** See [DEPLOYMENT.md](DEPLOYMENT.md)
- **Commit Messages:** Follow convention in WORKFLOW.md
