# flashcards Tenant

Tenant-specific code for flashcards.

## Structure
- `app/` - Tenant-specific PHP classes and traits
- `resources/views/` - Tenant-specific Blade templates

## Git Workflow

This repository uses a **staging branch workflow** for safe testing on production server:

1. **Development**: Create feature branches from `staging`
2. **Testing**: Merge features to `staging` → Deploy to production server (test environment)
3. **Production**: After successful testing, merge `staging` to `main` → Deploy to production

### Quick Start

```bash
# Start new feature
git checkout staging
git pull origin staging
git checkout -b feature/my-feature-name

# After development, create PR: feature/* → staging
# After testing on staging, create PR: staging → main
```

See [WORKFLOW.md](WORKFLOW.md) for detailed workflow documentation.
See [DEPLOYMENT.md](DEPLOYMENT.md) for deployment procedures.

## Branches

- **`main`**: Production-ready code (deployed to production)
- **`staging`**: Testing branch (deployed to production server test environment)
- **`feature/*`**: Feature development branches
- **`hotfix/*`**: Critical production fixes
