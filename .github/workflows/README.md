# GitHub Actions Workflows for Beta Tender Module

This directory contains automated testing workflows for the Beta Tender Drupal module.

## 🔄 Workflows

### 1. Drupal Module Tests (`drupal-module-tests.yml`)

**Comprehensive testing workflow** that runs on push and pull requests.

#### Test Jobs:

1. **PHP Syntax Check** ✓
   - Validates PHP syntax for all `.php` files
   - Ensures code can be parsed by PHP 8.1

2. **Drupal Coding Standards** 📋
   - Runs PHPCS with Drupal and DrupalPractice standards
   - Generates detailed reports on coding standard violations
   - Creates artifacts with full reports

3. **YAML Validation** 📄
   - Validates all `.yml` configuration files
   - Uses yamllint to catch syntax errors

4. **Drupal Module Installation** 🚀
   - Creates a fresh Drupal 11 installation
   - Attempts to enable the Beta Tender module
   - Generates installation logs and reports
   - Verifies module appears in module list

5. **PHPUnit Tests** 🧪
   - Runs the module's test suite
   - Includes both Kernel and Functional tests
   - Generates test results report

6. **Visual Verification** 📸
   - Installs Drupal with the module
   - Captures screenshots of key pages:
     - Admin dashboard
     - Module list
     - Tender dashboard
     - Configuration page
     - Content types
   - Uses Playwright for browser automation

7. **Test Summary** 📊
   - Aggregates results from all test jobs
   - Creates a visual summary with status badges
   - Comments on pull requests with results

#### Artifacts Generated:

- `phpcs-reports/` - Coding standards reports
- `installation-report/` - Module installation logs
- `phpunit-report/` - Test results
- `visual-screenshots/` - Screenshots of Drupal UI
- `visual-report/` - Visual verification report
- `test-summary/` - Overall test summary

### 2. Quick Checks (`quick-checks.yml`)

**Fast validation workflow** for quick feedback on syntax and common issues.

- PHP syntax checking
- YAML validation
- Detection of debug statements (var_dump, print_r)
- TODO/FIXME comment scanning
- File permission checks

Runs quickly on every push to provide immediate feedback.

## 📊 Status Badges

Add these badges to your README.md to show CI status:

```markdown
![Drupal Module Tests](https://github.com/BenTade/beta_tender/workflows/Drupal%20Module%20Tests/badge.svg)
![Quick Checks](https://github.com/BenTade/beta_tender/workflows/Quick%20Checks/badge.svg)
```

## 🎯 How to Use

### For Contributors:

1. **Push your changes** to any branch
2. **GitHub Actions will automatically run** the workflows
3. **Check the Actions tab** for results
4. **Download artifacts** to see detailed reports and screenshots
5. **Review the summary** posted as a PR comment

### For Reviewers:

1. **Check the status badges** in the PR
2. **Review the automated test summary** in PR comments
3. **Download screenshots** to visually verify the changes
4. **Check coding standard reports** for style issues

## 🔧 Local Testing

Before pushing, you can run similar checks locally:

```bash
# PHP Syntax Check
find . -name "*.php" -exec php -l {} \;

# Install and run PHPCS
composer global require drupal/coder
phpcs --standard=Drupal src/

# YAML validation
pip install yamllint
yamllint -d relaxed *.yml config/**/*.yml
```

## 📝 Configuration

### Customizing Tests

Edit the workflow files to:
- Change PHP versions tested
- Modify coding standard rules
- Add additional test steps
- Customize artifact retention

### Secrets Required

No secrets are required for basic testing. For advanced features:
- `GITHUB_TOKEN` - Automatically provided
- Additional secrets for deployment or notifications (if needed)

## 🐛 Troubleshooting

### Tests Failing?

1. **Check the Actions tab** for detailed logs
2. **Download artifacts** for full reports
3. **Review coding standard violations** in PHPCS reports
4. **Check installation logs** if module won't enable

### Common Issues:

- **Coding standard violations**: Review PHPCS reports and fix formatting
- **Module won't install**: Check dependencies in `.info.yml`
- **Tests failing**: Verify test database configuration
- **Screenshots missing**: Check Drupal installation succeeded

## 📚 Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards)
- [PHPUnit Testing](https://www.drupal.org/docs/testing/phpunit-in-drupal)
- [Playwright Documentation](https://playwright.dev/)

---

**Note**: These workflows require GitHub Actions to be enabled for your repository.
