# GitHub Actions Workflows for Beta Tender Module

This directory contains automated testing workflows for the Beta Tender Drupal module.

## 🔄 Workflows

### 1. Quick Checks (`quick-checks.yml`)

**Fast validation workflow** for quick feedback on syntax and common issues.

- PHP syntax checking
- YAML validation
- Detection of debug statements (var_dump, print_r)
- TODO/FIXME comment scanning
- File permission checks

Runs quickly on every push and pull request to provide immediate feedback.

### 2. Feature Demonstration (`feature-demonstration.yml`) ⭐

**Comprehensive feature walkthrough** that demonstrates all module features in action with visual verification.

#### What It Does:

1. **Full Environment Setup** 🚀
   - Installs Drupal 11 with MySQL
   - Enables Beta Tender module
   - Creates complete test data environment

2. **Test Data Creation** 📊
   - Creates 3 taxonomy vocabularies (Sources, Categories, Regions)
   - Generates 15+ taxonomy terms
   - Creates 5 sample scanned images
   - Generates 3 sample tender nodes with OCR text

3. **Comprehensive Visual Walkthrough** 📸
   - Captures 15+ detailed screenshots
   - Demonstrates all major features:
     - User authentication
     - Admin dashboard integration
     - Content management
     - Tender content type configuration
     - Image processing dashboard
     - Proofreading workflow
     - Module configuration
     - Taxonomy management
     - Permissions and security
     - System integration
   - Tests real-world workflows and behaviors

4. **Detailed Reporting** 📝
   - Generates comprehensive feature report
   - Includes verification checklist
   - Documents all demonstrated workflows
   - Provides screenshot index

#### Artifacts Generated:

- `feature-demonstration-screenshots/` - 15+ UI screenshots showing all features
- `feature-demonstration-report/` - Comprehensive analysis and verification checklist
- `feature-demo-logs/` - Execution logs and test data setup details

#### When to Use:

- ✅ **Pull Requests** - Verify all features work correctly
- ✅ **Major Changes** - Visual proof of functionality
- ✅ **Documentation** - Screenshots for user guides
- ✅ **Stakeholder Demos** - Show features in action

## 📊 Status Badges

Add these badges to your README.md to show CI status:

```markdown
![Quick Checks](https://github.com/BenTade/beta_tender/workflows/Quick%20Checks/badge.svg)
![Feature Demo](https://github.com/BenTade/beta_tender/workflows/Feature%20Demonstration%20with%20Visual%20Verification/badge.svg)
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
3. **Check installation logs** if module won't enable

### Common Issues:

- **Coding standard violations**: Review PHPCS reports and fix formatting
- **Module won't install**: Check dependencies in `.info.yml`
- **Screenshots missing**: Check Drupal installation succeeded

## 📚 Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards)
- [Playwright Documentation](https://playwright.dev/)

---

**Note**: These workflows require GitHub Actions to be enabled for your repository.
