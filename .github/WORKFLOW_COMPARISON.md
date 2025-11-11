# GitHub Actions Workflow Comparison

This document compares the different CI/CD workflows available for the Beta Tender module.

## Workflow Overview

The Beta Tender module now has **three complementary workflows** that work together to ensure code quality and feature functionality:

```
┌─────────────────────────────────────────────────────────────────────┐
│                        GitHub Push / Pull Request                    │
└────────────────┬──────────────────────────┬─────────────────────────┘
                 │                          │
    ┌────────────┼──────────────────────────┼───────────────────┐
    │            │                          │                   │
    ▼            ▼                          ▼                   ▼
┌─────────┐  ┌──────────────┐  ┌───────────────────┐  ┌────────────────┐
│ Quick   │  │   Module     │  │    Feature        │  │   Manual       │
│ Checks  │  │   Tests      │  │  Demonstration    │  │   Trigger      │
│         │  │              │  │                   │  │                │
│ 2-3 min │  │  10-15 min   │  │    15-20 min      │  │   Any time     │
└─────────┘  └──────────────┘  └───────────────────┘  └────────────────┘
     │               │                    │                     │
     └───────────────┴────────────────────┴─────────────────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │  Comprehensive       │
                    │  Verification        │
                    │  Complete            │
                    └──────────────────────┘
```

## Quick Reference Table

| Feature | Quick Checks | Module Tests | Feature Demo |
|---------|--------------|--------------|--------------|
| **Trigger** | copilot/** branches | main, develop, PRs | main, develop, PRs, manual |
| **Run Time** | 2-3 minutes | 10-15 minutes | 15-20 minutes |
| **PHP Syntax** | ✅ | ✅ | ✅ |
| **YAML Validation** | ✅ | ✅ | ✅ |
| **Coding Standards** | ❌ | ✅ | ✅ |
| **Module Install** | ❌ | ✅ | ✅ |
| **PHPUnit Tests** | ❌ | ✅ | ❌ |
| **Test Data** | ❌ | ❌ | ✅ 3 vocabularies, 15+ terms, 5 images, 3 nodes |
| **Screenshots** | ❌ | 5 basic | **15+ comprehensive** |
| **Feature Walkthrough** | ❌ | ❌ | ✅ Complete |
| **Visual Reports** | ❌ | Basic | **Detailed** |
| **PR Comments** | ❌ | ✅ | ✅ |
| **Best For** | Quick feedback | Code quality | Feature verification |

## Detailed Comparison

### 1. Quick Checks Workflow ⚡

**Purpose**: Fast validation for immediate feedback

**When to use**:
- Working on a feature branch
- Want quick syntax validation
- Need fast feedback loop
- Before creating a PR

**What it checks**:
```
✓ PHP syntax (all .php files)
✓ YAML syntax (all .yml files)
✓ Debug statements (var_dump, print_r)
✓ TODO/FIXME comments
✓ File permissions
```

**Artifacts**: None

**Time**: 2-3 minutes

**Ideal for**: Rapid development iteration

---

### 2. Drupal Module Tests Workflow 🧪

**Purpose**: Comprehensive code quality and module functionality testing

**When to use**:
- Creating a pull request
- Merging to main/develop
- Release preparation
- Code review process

**What it checks**:
```
✓ PHP syntax validation
✓ Drupal coding standards (PHPCS)
✓ YAML configuration validation
✓ Module installation in Drupal 11
✓ PHPUnit test suite (Kernel + Functional)
✓ Basic visual verification (5 screenshots)
✓ Test report generation
```

**Artifacts**:
- `phpcs-reports/` - Coding standards analysis
- `installation-report/` - Module installation logs
- `phpunit-report/` - Test results
- `visual-screenshots/` - Basic UI screenshots
- `visual-report/` - Visual verification summary
- `test-summary/` - Overall test summary

**Time**: 10-15 minutes

**Ideal for**: Code quality assurance and basic functionality testing

---

### 3. Feature Demonstration Workflow 🎬 ⭐ NEW

**Purpose**: Comprehensive visual verification of all features in action

**When to use**:
- Verifying feature completeness
- Creating documentation
- Stakeholder demonstrations
- Visual regression testing
- Major releases
- Feature-complete PRs

**What it demonstrates**:
```
✓ Complete Drupal 11 setup with test data
✓ Module installation and configuration
✓ User authentication workflow
✓ Admin dashboard integration
✓ Content management features
✓ Tender content type with all fields
✓ Image processing dashboard
✓ Proofreading workflow interface
✓ Module configuration page
✓ All 3 taxonomy vocabularies
✓ Permission system integration
✓ System status and logging
✓ Real-world feature behaviors
```

**Test Data Created**:
```
📊 3 Taxonomy Vocabularies:
   - Tender Sources (3 terms)
   - Tender Categories (5 terms)
   - Regions (5 terms)

🖼️ 5 Sample Images:
   - Simulated scanned tender documents

📄 3 Sample Nodes:
   - Tender content with OCR text
   - Proper field assignments
   - Realistic data
```

**Artifacts**:
- `feature-demonstration-screenshots/` - **15+ comprehensive screenshots**
  - 01-login-page.png
  - 02-admin-dashboard.png
  - 03-module-list.png
  - 04-content-list.png
  - 05-tender-content-type.png
  - 06-tender-fields.png
  - 07-tender-dashboard.png
  - 08-module-config.png
  - 09-proofread-dashboard.png
  - 10-taxonomy-sources.png
  - 11-taxonomy-categories.png
  - 12-taxonomy-regions.png
  - 13-permissions.png
  - 14-status-report.png
  - 15-log-messages.png
- `feature-demonstration-report/` - Detailed feature analysis
- `feature-demo-logs/` - Complete execution logs

**Time**: 15-20 minutes

**Ideal for**: Visual verification and comprehensive feature documentation

## When to Use Which Workflow

### Development Phase

```
During Active Development
├── Quick Checks (every push to copilot/**)
│   └── Fast feedback on syntax and basic issues
│
└── Feature Demo (manual, as needed)
    └── Verify specific features work end-to-end
```

### Pull Request Phase

```
Pull Request Created
├── Quick Checks (immediate)
│   └── Quick validation
│
├── Module Tests (automatic)
│   └── Code quality + basic functionality
│
└── Feature Demo (automatic)
    └── Complete visual verification
```

### Review Phase

```
Code Review
├── Check Module Tests results
│   └── Code quality, test coverage
│
└── Download Feature Demo screenshots
    └── Visual verification of features
```

### Release Phase

```
Release Preparation
├── All workflows must pass
│
├── Review Feature Demo screenshots
│   └── Verify all features work correctly
│
└── Archive artifacts for documentation
```

## Artifact Usage Guide

### For Developers

1. **Quick Checks** - No artifacts, just pass/fail
2. **Module Tests** - Download if tests fail to debug
3. **Feature Demo** - Download screenshots to verify visual changes

### For Code Reviewers

1. **Check status badges** in PR
2. **Download Feature Demo screenshots** ⭐ MOST IMPORTANT
3. **Review screenshots** to verify UI and behavior
4. **Check Module Tests** for code quality issues
5. **Review logs** if something looks wrong

### For Documentation Writers

1. **Download Feature Demo screenshots** (high quality, 1920x1080)
2. **Use in user guides** and README files
3. **Reference in tutorials** and walkthroughs
4. **Include in release notes**

### For Stakeholders

1. **View Feature Demo results** - visual proof of functionality
2. **No technical knowledge required** - screenshots speak for themselves
3. **Verify requirements met** using the feature checklist

## Workflow Triggers

### Quick Checks
```yaml
on:
  push:
    branches: [copilot/**]
  pull_request:
```

### Module Tests
```yaml
on:
  push:
    branches: [main, develop, copilot/**]
  pull_request:
    branches: [main, develop]
```

### Feature Demonstration
```yaml
on:
  push:
    branches: [main, develop, copilot/**]
  pull_request:
    branches: [main, develop]
  workflow_dispatch:  # Manual trigger available!
```

## Cost Analysis

### Time Investment

| Workflow | Setup | Execution | Total |
|----------|-------|-----------|-------|
| Quick Checks | < 1 min | 2-3 min | ~3 min |
| Module Tests | 5-7 min | 5-8 min | ~12 min |
| Feature Demo | 7-10 min | 8-10 min | ~18 min |
| **Total** | | | **~33 min** |

### Value Provided

| Workflow | Value Score | ROI |
|----------|-------------|-----|
| Quick Checks | ⭐⭐⭐ | High - fast feedback |
| Module Tests | ⭐⭐⭐⭐ | Very High - comprehensive |
| Feature Demo | ⭐⭐⭐⭐⭐ | Exceptional - visual proof |

## Best Practices

### ✅ DO

- Run Quick Checks frequently during development
- Review Module Tests before requesting review
- Download Feature Demo screenshots for all PRs
- Archive Feature Demo artifacts for releases
- Use manual workflow dispatch for demos
- Reference screenshots in documentation

### ❌ DON'T

- Don't skip visual verification
- Don't merge without checking Feature Demo results
- Don't ignore coding standard warnings
- Don't delete artifacts before review is complete
- Don't assume tests pass without checking

## Troubleshooting

### Quick Checks Fails
```
Issue: Syntax error or debug statement
Solution: Fix the specific file mentioned in logs
Time: < 5 minutes
```

### Module Tests Fails
```
Issue: Coding standards, installation, or tests fail
Solution: Download artifacts, review specific reports
Time: 15-30 minutes
```

### Feature Demo Fails
```
Issue: Module doesn't install or pages don't load
Solution: Check feature-demo-logs, fix installation issues
Time: 30-60 minutes
```

## Future Enhancements

Potential improvements for workflows:

### Quick Checks
- [ ] Add security scanning
- [ ] Check for deprecated APIs
- [ ] Validate composer.json

### Module Tests
- [ ] Add performance benchmarks
- [ ] Test with multiple PHP versions
- [ ] Add code coverage reports

### Feature Demo
- [ ] Add interaction testing (click buttons, fill forms)
- [ ] Test OCR processing with real images
- [ ] Add performance metrics
- [ ] Create video walkthrough
- [ ] Test Entity Share integration
- [ ] Add accessibility checks

## Conclusion

The three workflows work together to provide:

1. **Quick Checks** ⚡ - Fast feedback during development
2. **Module Tests** 🧪 - Comprehensive code quality assurance
3. **Feature Demo** 🎬 - Visual proof that everything works

**Recommended Approach**: Use all three for complete confidence in your code quality and feature functionality.

---

**See Also**:
- [Feature Demo Guide](FEATURE_DEMO_GUIDE.md) - Detailed usage guide
- [Workflows README](workflows/README.md) - Complete workflow documentation
- [CI Visual Guide](CI-VISUAL-GUIDE.md) - Visual examples

**Last Updated**: 2025-11-11
