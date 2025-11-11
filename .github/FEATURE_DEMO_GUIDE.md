# 🎬 Feature Demonstration Workflow Guide

## Overview

The **Feature Demonstration** workflow provides comprehensive visual verification of all Beta Tender module features running in a live Drupal 11 instance. Unlike basic installation tests, this workflow:

- ✅ Sets up a complete Drupal environment with test data
- ✅ Demonstrates **all major features** in action
- ✅ Captures detailed screenshots of every step
- ✅ Verifies UI rendering and functionality
- ✅ Tests real-world workflows and behaviors

## What This Workflow Does

### 1. Environment Setup (5-7 minutes)

```
┌─────────────────────────────────────────┐
│ Install Drupal 11 + MySQL              │
│ Enable Beta Tender module               │
│ Install dependencies                    │
└─────────────────────────────────────────┘
```

### 2. Test Data Creation (1-2 minutes)

Creates realistic test data:

- **3 Taxonomy Vocabularies**: Sources, Categories, Regions
- **15+ Taxonomy Terms**: Sample sources, categories, and regions
- **5 Sample Images**: Simulated scanned tender documents
- **3 Tender Nodes**: Example tender content with OCR text

### 3. Visual Feature Walkthrough (3-5 minutes)

Captures **15+ screenshots** demonstrating:

1. **User Authentication** - Login functionality
2. **Admin Dashboard** - Drupal admin interface with module
3. **Module List** - Beta Tender enabled and configured
4. **Content Management** - Tender nodes in content list
5. **Content Type Configuration** - Tender type structure
6. **Field Configuration** - All tender fields
7. **Image Processing Dashboard** - Main tender workflow interface
8. **Module Configuration** - Settings and OCR backend selection
9. **Proofreading Dashboard** - Editorial workflow interface
10. **Taxonomy: Sources** - Tender source management
11. **Taxonomy: Categories** - Category classification
12. **Taxonomy: Regions** - Geographic regions
13. **Permissions** - Role-based access control
14. **Status Report** - System health check
15. **Log Messages** - Error tracking and debugging

### 4. Report Generation (< 1 minute)

Generates comprehensive documentation:

- **Feature checklist** - What works, what's verified
- **Screenshot index** - Guide to all captured images
- **Test data summary** - What was created
- **Verification report** - Complete analysis

## When It Runs

The workflow triggers automatically on:

- ✅ **Push** to `main`, `develop`, or `copilot/**` branches
- ✅ **Pull requests** to `main` or `develop`
- ✅ **Manual dispatch** - Run on-demand from Actions tab

## How to Use the Results

### For Developers

1. **Push your changes** to trigger the workflow
2. **Wait ~15 minutes** for completion
3. **Check Actions tab** → Select the workflow run
4. **Download artifacts**:
   - `feature-demonstration-screenshots.zip` - All UI screenshots
   - `feature-demonstration-report.zip` - Analysis report
   - `feature-demo-logs.zip` - Execution logs

### For Code Reviewers

1. **Open the pull request**
2. **Check for automated comment** with results summary
3. **Download screenshot artifact**
4. **Review each screenshot** to verify:
   - UI renders correctly
   - No visual errors or broken layouts
   - Features are accessible
   - Behavior matches documentation
5. **Check the report** for feature checklist
6. **Approve or request changes**

## Screenshot Tour

Here's what each screenshot shows:

### Administrative Interface

```
01-login-page.png
└─> User authentication interface

02-admin-dashboard.png
└─> Drupal admin overview with Beta Tender integration

03-module-list.png
└─> Beta Tender in the modules list (enabled)
```

### Content Management

```
04-content-list.png
└─> Tender nodes in content overview

05-tender-content-type.png
└─> Tender content type configuration

06-tender-fields.png
└─> Complete field structure for tenders
```

### Feature Dashboards

```
07-tender-dashboard.png
└─> Image processing dashboard (main feature)

08-module-config.png
└─> Beta Tender settings page (OCR backend selection)

09-proofread-dashboard.png
└─> Editorial workflow interface
```

### Taxonomy Management

```
10-taxonomy-sources.png
└─> Tender sources (newspapers, gazettes, etc.)

11-taxonomy-categories.png
└─> Tender categories (construction, IT, healthcare)

12-taxonomy-regions.png
└─> Geographic regions for tender classification
```

### System Integration

```
13-permissions.png
└─> Beta Tender permission set

14-status-report.png
└─> System health with module information

15-log-messages.png
└─> Recent log entries and errors
```

## Understanding the Report

The generated `feature-demonstration-report.md` includes:

### Feature Verification Checklist

```markdown
- [x] Module appears in the Extend list
- [x] Module can be enabled without errors
- [x] Tender content type exists
- [x] All three taxonomies are created
- [x] Dashboard pages are accessible
- [x] Configuration page loads
- [x] Permissions are defined
- [ ] No PHP errors in logs
```

Review this checklist to ensure all features are working.

### Demonstrated Workflows

The report documents these workflows:

1. **Content Management Workflow**
   - Creating and managing tender content
   - Filtering and searching tenders

2. **Administrative Interface**
   - Configuring OCR backends
   - Managing module settings

3. **Taxonomy Management**
   - Creating and organizing terms
   - Hierarchical structures

4. **Dashboard Features**
   - Image processing workflow
   - Proofreading assignments

5. **Integration Points**
   - Drupal core integration
   - Permission system
   - Logging and monitoring

## Comparing with Basic Tests

| Feature | Quick Checks | Module Tests | **Feature Demo** |
|---------|--------------|--------------|------------------|
| PHP Syntax | ✅ | ✅ | ✅ |
| Coding Standards | ❌ | ✅ | ✅ |
| Module Install | ❌ | ✅ | ✅ |
| Test Data | ❌ | ❌ | ✅ |
| UI Screenshots | ❌ | 5 basic | **15+ comprehensive** |
| Feature Walkthrough | ❌ | ❌ | **✅ Complete** |
| Workflow Demo | ❌ | ❌ | **✅ All workflows** |
| Report Generation | ❌ | Basic | **✅ Detailed** |
| Run Time | 2-3 min | 10-15 min | **15-20 min** |

## Running Manually

You can trigger the workflow manually:

1. Go to **Actions** tab in GitHub
2. Select **Feature Demonstration with Visual Verification**
3. Click **Run workflow**
4. Choose the branch
5. Click **Run workflow** button

This is useful for:
- ✅ Testing before pushing
- ✅ Demonstrating features to stakeholders
- ✅ Creating visual documentation
- ✅ Verifying after major changes

## Troubleshooting

### Workflow Fails to Start

- **Check**: GitHub Actions enabled for repository
- **Check**: Workflow file syntax is valid
- **Solution**: Validate YAML with `yamllint feature-demonstration.yml`

### Module Install Fails

- **Check**: `module-enable.log` in artifacts
- **Check**: Dependencies in `.info.yml` file
- **Solution**: Review installation logs for specific errors

### Screenshots Missing

- **Check**: Web server started successfully
- **Check**: Playwright installed correctly
- **Check**: `feature-demo.log` for errors
- **Solution**: Ensure Drupal is accessible on localhost:8080

### Test Data Not Created

- **Check**: `test-data-setup.log` in artifacts
- **Check**: Content types exist
- **Solution**: Verify module installation succeeded first

## Best Practices

### For Pull Requests

1. ✅ **Always wait** for feature demo to complete
2. ✅ **Review screenshots** before merging
3. ✅ **Check logs** if anything looks wrong
4. ✅ **Compare** screenshots with expected behavior

### For Major Changes

1. ✅ **Run manually** after significant updates
2. ✅ **Download artifacts** and archive for documentation
3. ✅ **Update README** if UI changes significantly
4. ✅ **Share screenshots** with team for feedback

### For Releases

1. ✅ **Ensure all checks pass** including feature demo
2. ✅ **Archive screenshots** for release documentation
3. ✅ **Include in release notes** visual proof of features
4. ✅ **Use for marketing** material and documentation

## Integration with Other Workflows

The Feature Demonstration workflow complements existing workflows:

```
┌──────────────────────────────────────────────────────┐
│                 Code Push / PR                       │
└───────────────┬──────────────────────────────────────┘
                │
    ┌───────────┼───────────┬────────────────┐
    │           │           │                │
    ▼           ▼           ▼                ▼
┌─────────┐ ┌──────┐ ┌──────────┐ ┌────────────────┐
│ Quick   │ │Module│ │  Unit    │ │    Feature     │
│ Checks  │ │Tests │ │  Tests   │ │ Demonstration  │
└─────────┘ └──────┘ └──────────┘ └────────────────┘
    2min      15min      10min          20min
    │           │           │                │
    └───────────┴───────────┴────────────────┘
                     │
                     ▼
            ┌────────────────┐
            │  All Verified  │
            │  Ready to Merge│
            └────────────────┘
```

## Advanced Usage

### Custom Test Data

Modify `setup-test-data.php` in the workflow to create:

- Different types of content
- More complex relationships
- Edge cases
- Performance testing data

### Additional Screenshots

Add more capture steps in `feature-demo.js` to screenshot:

- Specific user interactions
- Form submissions
- Ajax operations
- Modal dialogs

### Performance Metrics

Extend the workflow to capture:

- Page load times
- Database query counts
- Memory usage
- Network requests

## FAQ

### Q: Why does it take longer than other workflows?

**A**: This workflow does much more - it sets up a complete environment, creates test data, and captures detailed screenshots of every feature. The extra time provides much more comprehensive verification.

### Q: Can I use these screenshots in documentation?

**A**: Yes! The screenshots are high-quality (1920x1080) and perfect for:
- README files
- User guides
- Marketing materials
- Training documentation

### Q: How long are artifacts kept?

**A**: Screenshots and reports: **90 days**. Logs: **30 days**. Download important artifacts for long-term storage.

### Q: Can I run this locally?

**A**: The workflow is designed for CI/CD, but you can adapt the scripts to run locally with Docker and Playwright.

### Q: What if a feature isn't accessible?

**A**: The workflow continues even if some pages fail. Check the report and logs to see which features were verified and which weren't.

## Contributing

To improve the feature demonstration workflow:

1. **Add more test scenarios** to `feature-demo.js`
2. **Enhance test data** in `setup-test-data.php`
3. **Improve report formatting** in the report generation step
4. **Add performance metrics** for optimization insights

## Support

For issues with the feature demonstration workflow:

1. **Check this guide** first
2. **Review workflow logs** in Actions tab
3. **Download artifacts** for detailed information
4. **Open an issue** with logs and screenshots attached

---

**Pro Tip**: The feature demonstration workflow is the **most comprehensive** way to verify your module works correctly. Always review the screenshots before merging! 📸

**Generated**: 2025-11-11
**Version**: 1.0
**Workflow**: feature-demonstration.yml
