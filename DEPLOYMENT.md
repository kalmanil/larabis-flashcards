# Deployment Guide for Flashcards Tenant

## Overview
This guide explains how to deploy the flashcards tenant to production server with testing workflow.

## Server Setup

### Production Server Requirements
- PHP 8.5.0+
- MySQL/MariaDB
- Composer
- Git
- Web server (Apache/Nginx)
- SSL certificate (for production domain)

### Directory Structure (Production Server)
```
/var/www/flashcards/
├── .env (production configuration)
├── current/ (symlink to current release)
├── releases/
│   ├── 20240101-120000/
│   ├── 20240102-140000/
│   └── ...
└── shared/
    ├── storage/
    └── .env
```

## Deployment Scripts

### Deploy Staging (Testing on Production Server)

Create script: `deploy-staging.sh`

```bash
#!/bin/bash
set -e

# Configuration
PROJECT_DIR="/var/www/flashcards-staging"
BRANCH="staging"
DEPLOY_USER="www-data"

echo "🚀 Deploying staging branch to production server (test environment)..."

cd "$PROJECT_DIR"

# Backup current deployment
echo "📦 Creating backup..."
if [ -d "current" ]; then
    BACKUP_DIR="backups/$(date +%Y%m%d-%H%M%S)"
    mkdir -p backups
    cp -r current "$BACKUP_DIR"
    echo "✅ Backup created: $BACKUP_DIR"
fi

# Fetch latest changes
echo "🔄 Fetching latest changes from origin/$BRANCH..."
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH

# Install dependencies
echo "📥 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force --database=mysql

# Clear and cache configuration
echo "⚙️ Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan cache:clear
php artisan optimize

# Set permissions
echo "🔐 Setting permissions..."
chown -R $DEPLOY_USER:$DEPLOY_USER storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Restart services (if needed)
echo "🔄 Restarting queue workers (if applicable)..."
php artisan queue:restart || true

echo "✅ Staging deployment completed successfully!"
echo "🌐 Test environment: staging-flashcards.yourdomain.com"
```

### Deploy Production (Main Branch)

Create script: `deploy-production.sh`

```bash
#!/bin/bash
set -e

# Configuration
PROJECT_DIR="/var/www/flashcards"
BRANCH="main"
DEPLOY_USER="www-data"
BACKUP_ENABLED=true

echo "🚀 Deploying main branch to production..."

cd "$PROJECT_DIR"

# Create backup before deployment
if [ "$BACKUP_ENABLED" = true ]; then
    echo "📦 Creating backup..."
    BACKUP_DIR="backups/$(date +%Y%m%d-%H%M%S)"
    mkdir -p backups
    
    # Backup database
    php artisan backup:run --only-db || echo "⚠️ Database backup failed, continuing..."
    
    # Backup files
    if [ -d "current" ]; then
        cp -r current "$BACKUP_DIR"
        echo "✅ Backup created: $BACKUP_DIR"
    fi
fi

# Fetch latest changes
echo "🔄 Fetching latest changes from origin/$BRANCH..."
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH

# Tag current release
RELEASE_TAG="release-$(date +%Y%m%d-%H%M%S)"
git tag -a "$RELEASE_TAG" -m "Production release: $(date +'%Y-%m-%d %H:%M:%S')"
git push origin "$RELEASE_TAG" || echo "⚠️ Tag push failed, continuing..."

# Install dependencies
echo "📥 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force --database=mysql

# Clear and cache everything
echo "⚙️ Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan cache:clear
php artisan optimize

# Set permissions
echo "🔐 Setting permissions..."
chown -R $DEPLOY_USER:$DEPLOY_USER storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Restart services
echo "🔄 Restarting services..."
php artisan queue:restart || true

# Health check
echo "🏥 Running health check..."
php artisan app:health || echo "⚠️ Health check failed"

echo "✅ Production deployment completed successfully!"
echo "🌐 Production: flashcards.yourdomain.com"
echo "📌 Release tag: $RELEASE_TAG"
```

## Quick Deployment Commands

### Manual Deployment (Simple)

**Deploy Staging:**
```bash
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

**Deploy Production:**
```bash
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

## Testing on Production Server

### Testing Workflow

1. **Deploy to Staging Environment**
   ```bash
   ./deploy-staging.sh
   ```

2. **Test on Staging URL**
   - Visit: `staging-flashcards.yourdomain.com` or `test.yourdomain.com`
   - Test all features
   - Check database connections
   - Verify all routes work
   - Test admin panel
   - Check system status

3. **If Issues Found**
   ```bash
   # Fix on feature branch
   git checkout feature/my-feature
   # ... fix issues ...
   git commit -m "fix(scope): fix issue description"
   git push origin feature/my-feature
   
   # Merge to staging again
   git checkout staging
   git merge feature/my-feature
   git push origin staging
   
   # Redeploy staging
   ./deploy-staging.sh
   ```

4. **After Successful Testing**
   ```bash
   # Merge staging to main
   git checkout main
   git merge staging
   git push origin main
   
   # Deploy to production
   ./deploy-production.sh
   ```

## Environment Configuration

### Staging Environment (.env)
```env
APP_NAME=Flashcards
APP_ENV=staging
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=https://staging-flashcards.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tenant_flashcards_staging
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Use same production server but separate database
```

### Production Environment (.env)
```env
APP_NAME=Flashcards
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://flashcards.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tenant_flashcards
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Rollback Procedure

### Quick Rollback
```bash
# Find previous release tag
git tag -l "release-*" | tail -5

# Checkout previous release
git checkout <previous-release-tag>

# Re-run deployment commands
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Rollback from Backup
```bash
cd /var/www/flashcards
BACKUP_DIR="backups/20240101-120000"  # Use your backup directory

# Restore from backup
rm -rf current
cp -r "$BACKUP_DIR" current

# Restore database if backed up
# (database restore commands depend on your backup method)
```

## Monitoring and Health Checks

### Create Health Check Command

Add to `app/Console/Commands/HealthCheck.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HealthCheck extends Command
{
    protected $signature = 'app:health';
    protected $description = 'Check application health';

    public function handle()
    {
        $this->info('Running health checks...');
        
        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $this->error('❌ Database connection: FAILED');
            return 1;
        }
        
        // Check storage
        if (is_writable(storage_path())) {
            $this->info('✅ Storage directory: Writable');
        } else {
            $this->error('❌ Storage directory: Not writable');
            return 1;
        }
        
        $this->info('✅ All health checks passed!');
        return 0;
    }
}
```

## Troubleshooting

### Common Issues

**Issue: Permission Denied**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/flashcards
sudo chmod -R 775 /var/www/flashcards/storage
sudo chmod -R 775 /var/www/flashcards/bootstrap/cache
```

**Issue: Database Migration Fails**
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Run migrations with verbose output
php artisan migrate --force -vvv
```

**Issue: Cache Problems**
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

# Then rebuild
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**Issue: Composer Memory Error**
```bash
# Increase PHP memory limit
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader
```

## Security Checklist

Before deploying to production:

- [ ] APP_DEBUG=false
- [ ] Strong APP_KEY set
- [ ] Secure database credentials
- [ ] HTTPS enabled
- [ ] Environment variables not exposed
- [ ] File permissions set correctly (775 for storage, 644 for files)
- [ ] .env file not in repository
- [ ] Vendor directory not in repository
- [ ] Logs directory properly secured
- [ ] Backup strategy in place

## Automated Deployment (Optional)

### GitHub Actions Example

Create `.github/workflows/deploy-staging.yml`:

```yaml
name: Deploy Staging

on:
  push:
    branches:
      - staging

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to Staging Server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          script: |
            cd /var/www/flashcards-staging
            git pull origin staging
            composer install --no-dev --optimize-autoloader
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan optimize
```

## Notes

- Always test on staging before deploying to production
- Keep backups before major deployments
- Monitor logs after deployment
- Test database migrations in staging first
- Have a rollback plan ready
