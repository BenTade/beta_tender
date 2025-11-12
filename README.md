# Beta Tender Module

[![Drupal Module Tests](https://github.com/BenTade/beta_tender/workflows/Drupal%20Module%20Tests/badge.svg)](https://github.com/BenTade/beta_tender/actions/workflows/drupal-module-tests.yml)
[![Quick Checks](https://github.com/BenTade/beta_tender/workflows/Quick%20Checks/badge.svg)](https://github.com/BenTade/beta_tender/actions/workflows/quick-checks.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://www.php.net/)
[![Drupal](https://img.shields.io/badge/Drupal-11.x-blue.svg)](https://www.drupal.org/)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](LICENSE.txt)

A comprehensive Drupal 11 module for managing the creation of Tender content from scanned images.

## Features

- **Multi-level Image Processing Dashboard**: Organize scanned images by date and source
- **OCR Integration**: Extract text from images using configurable OCR backends (document_ocr or ocr_image)
- **Drag-and-Drop Image Arrangement**: Group multiple images into single tenders using Drupal's tabledrag
- **Batch Processing**: Process multiple tender groups with OCR using Drupal's Batch API
- **Proofreading Workflow**: Assign tenders to editors for review with status tracking
- **Content Synchronization**: Integration with Entity Share for pushing content to production

## Requirements

- Drupal ^11
- PHP 8.3 or higher
- One of the following OCR modules:
  - document_ocr
  - ocr_image
- Entity Share module (for content synchronization features)

## Installation

1. Place this module in your Drupal installation's `modules/custom` directory
2. Install the required dependencies (Entity Share module)
3. Install one of the OCR modules (document_ocr or ocr_image)
4. Enable the Beta Tender module: `drush en beta_tender`
5. Configure the OCR backend at `/admin/config/beta_tender`

## Configuration

### OCR Backend Setup

1. Navigate to **Configuration > Content authoring > Beta Tender Settings** (`/admin/config/beta_tender`)
2. Select your preferred OCR backend (document_ocr or ocr_image)
3. Save the configuration

### Permissions

Assign the following permissions to appropriate roles:

- **Administer Beta Tender**: Configure module settings
- **Access Tender Dashboard**: View the image processing dashboard
- **Process Tender Images**: Upload and process scanned images
- **Proofread Tenders**: Access proofreading dashboard and review tenders
- **Assign Tenders**: Assign tenders to editors
- **Share Tenders**: Synchronize tenders to production site

## Usage

### Image Processing Workflow

1. **Upload Scanned Images**:
   - Create "Scanned Image" media entities with the source field populated
   - Images are automatically grouped by upload date

2. **Access the Dashboard**:
   - Navigate to **Content > Tender** tab
   - View images organized by date and source

3. **Arrange Images**:
   - Click on a source to access the image arrangement page
   - Use drag-and-drop to group related images
   - Indent images under a parent to create multi-image tenders

4. **Process with OCR**:
   - Select parent images (tender groups) using checkboxes
   - Click "Process OCR and Create Tenders"
   - The batch process will extract text and create tender nodes

### Proofreading Workflow

1. Navigate to **Content > Tender > Proofread Tenders**
2. View tenders filtered by status:
   - **Needs Review**: Newly created tenders awaiting assignment
   - **In Review**: Tenders currently being proofread
   - **Reviewed**: Completed tenders ready for publication

3. Click "Assign Next Tender" to:
   - Automatically assign the oldest unreviewed tender to yourself
   - Change status to "In Review"
   - Redirect to the tender edit page

4. Review and edit the tender:
   - Verify OCR accuracy
   - Correct any errors in extracted fields
   - Update status to "Reviewed" when complete

### Content Synchronization

1. Navigate to **Content > Tender > Share Tenders**
2. Use Entity Share interface to select and push reviewed tenders to production
3. Monitor synchronization status from the proofreading dashboard

## Content Types and Fields

### Tender Content Type

- **Title**: Auto-generated from OCR text
- **Scanned Images**: Multiple image files
- **Source**: Reference to tender source taxonomy
- **OCR Text**: Extracted text from images
- **Opening Date**: Tender opening date
- **Closing Date**: Tender closing date
- **Categories**: Multiple tender category terms
- **Region**: Geographical region
- **Proofreading Status**: needs_review, in_review, reviewed
- **Assigned Editor**: User reference

### Scanned Image Media Type

- **Image**: The source image file
- **Source**: Reference to tender source taxonomy
- **Upload Date**: Auto-populated creation date
- **Processed Status**: Boolean flag

### Taxonomy Vocabularies

- **Tender Categories**: e.g., Construction, IT Services, Healthcare
- **Regions**: Geographical classifications
- **Tender Sources**: e.g., The Daily Chronicle, Government Gazette

## Architecture

### Services

- **OcrService**: Adapter for OCR backends (document_ocr or ocr_image)
- **TenderCreationService**: Creates tender nodes from image groups
- **TenderBatchService**: Manages batch processing operations

### Controllers

- **DashboardController**: Main and image arrangement dashboards
- **ProofreadController**: Proofreading dashboard and assignment

### Forms

- **BetaTenderSettingsForm**: Module configuration
- **ImageArrangementForm**: Tabledrag interface for grouping images
- **ProcessTenderBatchForm**: Batch processing form

## Development

### Code Standards

This module follows Drupal coding standards and PSR-12. All business logic is implemented in services with dependency injection for better testability and maintainability.

### Future-Proofing

The module is designed for easy upgrading to Drupal 12 and beyond:
- Uses current Drupal APIs exclusively
- Service-based architecture with dependency injection
- No deprecated code or procedural patterns
- API-first design for potential REST endpoints

### Testing

Basic test suite structure is included for:
- Kernel tests for services
- Functional tests for workflows

Run tests with: `drush test-run beta_tender`

### Continuous Integration

The module includes comprehensive GitHub Actions workflows for automated testing:

- ✅ **PHP Syntax Validation** - Checks all PHP files for syntax errors
- 📋 **Drupal Coding Standards** - PHPCS validation with Drupal standards
- 📄 **YAML Validation** - Configuration file syntax checking
- 🚀 **Module Installation** - Tests module enables successfully in Drupal 11
- 🧪 **PHPUnit Tests** - Runs complete test suite
- 📸 **Visual Verification** - Captures screenshots of module UI
- 🎬 **Feature Demonstration** - Complete walkthrough of all features with 15+ screenshots
- 📊 **Test Reports** - Generates detailed reports with artifacts

The **Feature Demonstration** workflow provides comprehensive visual verification by:
- Setting up a complete Drupal instance with test data
- Demonstrating all major features (dashboards, OCR, proofreading, etc.)
- Capturing detailed screenshots of every workflow step
- Generating a comprehensive feature verification report

All tests run automatically on push and pull requests. Check the [Actions tab](../../actions) for results, or see [`.github/workflows/README.md`](.github/workflows/README.md) for details.

## Troubleshooting

### OCR Not Working

1. Verify OCR backend module is enabled
2. Check module configuration at `/admin/config/beta_tender`
3. Review logs at `/admin/reports/dblog`

### Images Not Appearing

1. Verify file permissions in `sites/default/files`
2. Check that Scanned Image media entities have the source field populated
3. Ensure correct date filtering

### Batch Processing Fails

1. Increase PHP memory limit and execution time
2. Check logs for specific errors
3. Process smaller batches

## Support

For issues, feature requests, or contributions, please visit the project repository.

## License

GPL-2.0-or-later

## Credits

- **Module Developer**: BenTade
- **Generated**: 2025-11-10 13:38:23 UTC