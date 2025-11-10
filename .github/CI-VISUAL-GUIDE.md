# 📸 Visual CI/CD Guide for Beta Tender Module

This guide shows what the GitHub Actions workflows will do when testing your code, with visual examples of the reports and screenshots generated.

## 🔄 Workflow Overview

```
┌──────────────────────────────────────────────────────────────┐
│                    Push/Pull Request                         │
└────────────────────┬─────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
┌───────────────┐         ┌─────────────────┐
│ Quick Checks  │         │ Full Test Suite │
│  (2-3 min)    │         │   (10-15 min)   │
└───────────────┘         └─────────────────┘
        │                         │
        ▼                         ▼
   Fast Feedback          Comprehensive Reports
                                  │
                          ┌───────┴────────┐
                          │                │
                          ▼                ▼
                    Screenshots       Test Reports
```

## 📋 Test Jobs Breakdown

### 1. PHP Syntax Check ✅

```bash
✓ Checking src/Service/OcrService.php
✓ Checking src/Service/TenderCreationService.php
✓ Checking src/Controller/DashboardController.php
...
✅ All PHP files passed syntax check
```

**Purpose**: Ensures all PHP files are syntactically valid
**Time**: ~30 seconds
**Fails if**: Any PHP file has syntax errors

### 2. Drupal Coding Standards 📋

```
FILE: src/Service/OcrService.php
────────────────────────────────────────────────────
FOUND 0 ERRORS AND 2 WARNINGS AFFECTING 2 LINES
────────────────────────────────────────────────────
 45 | WARNING | Line exceeds 80 characters
 67 | WARNING | Missing function comment
────────────────────────────────────────────────────
```

**Purpose**: Validates code follows Drupal standards
**Time**: ~1-2 minutes
**Generates**: Detailed PHPCS reports (downloadable)
**Warning Level**: Continues even with warnings

### 3. YAML Validation 📄

```yaml
✓ beta_tender.info.yml
✓ beta_tender.routing.yml
✓ config/install/*.yml
...
✅ All YAML files are valid
```

**Purpose**: Validates configuration file syntax
**Time**: ~20 seconds
**Fails if**: YAML syntax errors found

### 4. Module Installation 🚀

```
Installing Drupal 11...
✓ Drupal installed successfully
✓ Copying Beta Tender module
✓ Running: drush en beta_tender -y

Module 'beta_tender' has been enabled.

✅ Module installation successful
```

**Purpose**: Tests module can be enabled in Drupal 11
**Time**: ~5-7 minutes
**Generates**: Installation logs and module status report
**Critical**: Must pass for merge

### 5. PHPUnit Tests 🧪

```
Beta Tender (Drupal\Tests\beta_tender\Kernel)
 ✓ OCR service exists
 ✓ OCR availability check

Beta Tender (Drupal\Tests\beta_tender\Functional)
 ✓ Dashboard access control
 ✓ Dashboard page rendering

Time: 00:02.456, Memory: 128.00 MB

OK (4 tests, 8 assertions)
```

**Purpose**: Runs automated test suite
**Time**: ~3-5 minutes
**Generates**: Test results with pass/fail details

### 6. Visual Verification 📸

**This is the most important feature for visual code review!**

#### Screenshots Captured:

1. **01-admin-dashboard.png**
   ```
   ┌─────────────────────────────────────────┐
   │  Drupal Admin Dashboard                 │
   │  ┌────────┬──────────┬──────────┐      │
   │  │Content │Structure │Appearance│      │
   │  └────────┴──────────┴──────────┘      │
   │                                         │
   │  Shows: Drupal is installed correctly   │
   └─────────────────────────────────────────┘
   ```

2. **02-module-list.png**
   ```
   ┌─────────────────────────────────────────┐
   │  Extend - Module List                   │
   │  ┌──────────────────────────────────┐   │
   │  │ [✓] Beta Tender     ●Enabled    │   │
   │  │     Manages tender content...    │   │
   │  │                                  │   │
   │  └──────────────────────────────────┘   │
   │                                         │
   │  Shows: Module appears and is enabled   │
   └─────────────────────────────────────────┘
   ```

3. **03-tender-dashboard.png**
   ```
   ┌─────────────────────────────────────────┐
   │  Image Processing Dashboard             │
   │  ┌────────────────────────────────────┐ │
   │  │ ▼ 2025-11-10                      │ │
   │  │   • The Daily Chronicle (3/5)     │ │
   │  │   • Government Gazette (1/2)      │ │
   │  └────────────────────────────────────┘ │
   │                                         │
   │  Shows: Main dashboard is accessible    │
   └─────────────────────────────────────────┘
   ```

4. **04-module-config.png**
   ```
   ┌─────────────────────────────────────────┐
   │  Beta Tender Settings                   │
   │  ┌────────────────────────────────────┐ │
   │  │ OCR Backend:                       │ │
   │  │ ○ Document OCR                     │ │
   │  │ ● OCR Image                        │ │
   │  │                                    │ │
   │  │ [Save configuration]               │ │
   │  └────────────────────────────────────┘ │
   │                                         │
   │  Shows: Configuration page works        │
   └─────────────────────────────────────────┘
   ```

5. **05-content-types.png**
   ```
   ┌─────────────────────────────────────────┐
   │  Content Types                          │
   │  ┌────────────────────────────────────┐ │
   │  │ Article                            │ │
   │  │ Basic Page                         │ │
   │  │ Tender              [Manage]       │ │
   │  └────────────────────────────────────┘ │
   │                                         │
   │  Shows: Tender content type created     │
   └─────────────────────────────────────────┘
   ```

**Purpose**: Visual proof that UI works correctly
**Time**: ~3-5 minutes
**Generates**: 5+ PNG screenshots (downloadable)
**Best for**: Reviewers to see actual UI

### 7. Test Summary 📊

```markdown
# 🔬 Beta Tender Module - Test Results Summary

## 📊 Test Status Overview

| Test Category       | Status     |
|---------------------|------------|
| PHP Syntax          | ✅ Passed  |
| Coding Standards    | ⚠️ Warnings|
| YAML Validation     | ✅ Passed  |
| Module Installation | ✅ Passed  |
| PHPUnit Tests       | ✅ Passed  |

## 📝 Detailed Reports

- [PHPCS Reports](phpcs-reports/)
- [Installation Report](installation-report/)
- [PHPUnit Report](phpunit-report/)

## 🎯 Recommendations

- Review coding standard warnings
- All critical tests passed
- Ready for merge ✅
```

**Purpose**: Single-page summary of all tests
**Posted**: As comment on pull requests
**Includes**: Links to all detailed reports

## 📦 Downloadable Artifacts

After each workflow run, download these from the Actions tab:

```
Artifacts (Available for 90 days)
├── phpcs-reports.zip (2.1 KB)
│   ├── phpcs-drupal.txt
│   ├── phpcs-practice.txt
│   └── coding-standards-summary.md
│
├── installation-report.zip (5.3 KB)
│   └── installation-report.md
│
├── phpunit-report.zip (3.7 KB)
│   └── phpunit-report.md
│
├── visual-screenshots.zip (247 KB) ⭐ MOST USEFUL
│   ├── 01-admin-dashboard.png
│   ├── 02-module-list.png
│   ├── 03-tender-dashboard.png
│   ├── 04-module-config.png
│   └── 05-content-types.png
│
├── visual-report.zip (1.8 KB)
│   └── visual-verification.md
│
└── test-summary.zip (2.4 KB)
    ├── test-summary.md
    └── README.md
```

## 🎯 How to Use the Visual Reports

### For PR Authors:

1. **Push your changes** → workflows run automatically
2. **Wait 10-15 minutes** for completion
3. **Check Actions tab** for status
4. **Download visual-screenshots.zip** to see UI
5. **Verify screenshots** match your expectations

### For PR Reviewers:

1. **Check status badges** in PR description
2. **Read automated comment** for summary
3. **Download visual-screenshots.zip** ⭐
4. **Review screenshots** to verify UI changes
5. **Check coding standards** if needed
6. **Approve or request changes**

## 🖼️ Example Visual Review Process

```
1. See PR notification
   ↓
2. Click "Actions" tab
   ↓
3. Find latest "Drupal Module Tests" run
   ↓
4. Scroll to "Artifacts" section
   ↓
5. Download "visual-screenshots"
   ↓
6. Extract and review PNG files
   ↓
7. Verify:
   ✓ Module appears in module list
   ✓ Dashboard UI looks correct
   ✓ Configuration page loads
   ✓ Content types exist
   ↓
8. Approve or request changes
```

## 🚀 Quick Start

### Run CI Checks Locally (Before Pushing):

```bash
# Make script executable (first time only)
chmod +x .github/scripts/local-test.sh

# Run local tests
./.github/scripts/local-test.sh

# You'll see:
╔═══════════════════════════════════════════════════╗
║     Beta Tender Module - Local Test Script       ║
╚═══════════════════════════════════════════════════╝

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. PHP Syntax Check
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  All PHP files passed syntax check
✅ PASSED

...

╔═══════════════════════════════════════════════════╗
║                Test Summary                       ║
╚═══════════════════════════════════════════════════╝

  ✅ Passed: 5
  ❌ Failed: 0
  ⚠️  Warnings: 0

✅ All tests passed! Ready to push.
```

## 📊 CI Status Badges

Add these to your PR description or README:

```markdown
[![Drupal Module Tests](https://github.com/BenTade/beta_tender/workflows/Drupal%20Module%20Tests/badge.svg)](https://github.com/BenTade/beta_tender/actions/workflows/drupal-module-tests.yml)
[![Quick Checks](https://github.com/BenTade/beta_tender/workflows/Quick%20Checks/badge.svg)](https://github.com/BenTade/beta_tender/actions/workflows/quick-checks.yml)
```

Result:
![Drupal Module Tests](https://github.com/BenTade/beta_tender/workflows/Drupal%20Module%20Tests/badge.svg)
![Quick Checks](https://github.com/BenTade/beta_tender/workflows/Quick%20Checks/badge.svg)

## 🐛 Troubleshooting

### Tests Failing?

1. **Check Actions tab** → Click on failed job
2. **Read error logs** → Expand failed step
3. **Download artifacts** → Get detailed reports
4. **Fix issues** → Push again

### Common Issues:

| Issue | Cause | Solution |
|-------|-------|----------|
| Syntax errors | PHP parse error | Run local test script |
| Coding standards | Style violations | Run PHPCS locally |
| Module won't install | Dependency issue | Check .info.yml |
| Tests failing | Logic error | Fix and run locally |
| No screenshots | Drupal install failed | Check installation logs |

## 📚 Additional Resources

- [GitHub Actions Docs](https://docs.github.com/en/actions)
- [Drupal Testing Guide](https://www.drupal.org/docs/testing)
- [Playwright Docs](https://playwright.dev/) (for screenshots)
- [PHPCS Drupal Coder](https://www.drupal.org/project/coder)

---

**Pro Tip**: The visual screenshots are the **fastest way** to verify your module is working correctly! 📸
