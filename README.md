# Beta Tender Module

[![Drupal Module Tests](https://github.com/BenTade/beta_tender/workflows/Drupal%20Module%20Tests/badge.svg)](https://github.com/BenTade/beta_tender/actions/workflows/drupal-module-tests.yml)
[![Quick Checks](https://github.com/BenTade/beta_tender/workflows/Quick%20Checks/badge.svg)](https://github.com/BenTade/beta_tender/actions/workflows/quick-checks.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://www.php.net/)
[![Drupal](https://img.shields.io/badge/Drupal-11.x-blue.svg)](https://www.drupal.org/)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](LICENSE.txt)

A comprehensive Drupal 11 module for managing the creation of Tender content from scanned images.

## Features

- **Unified Tenders Overview**: Consolidated view of all tenders organized by dateline (source + date)
- **Source Upload with Dateline**: Upload scanned media with source and publication date metadata
- **OCR Integration**: Extract text from images using configurable OCR backends (document_ocr or ocr_image)
- **Batch Processing**: Process multiple tender groups with OCR using Drupal's Batch API
- **Comprehensive Tender Tracking**: View author, creation date, last update, and moderation state indicators
- **Backup & Restore Helper**: Dedicated admin screen for manual export/import utilities

## Requirements

- Drupal ^11
- PHP 8.3 or higher
- One of the following OCR modules:
  - document_ocr
  - ocr_image

## Installation

1. Place this module in your Drupal installation's `modules/custom` directory
2. Install one of the OCR modules (document_ocr or ocr_image)
3. Enable the Beta Tender module: `drush en beta_tender`
4. Configure the OCR backend at `/admin/config/beta_tender`

## Configuration

### OCR Backend Setup

1. Navigate to **Configuration > Content authoring > Beta Tender Settings** (`/admin/config/beta_tender`)
2. Select your preferred OCR backend (document_ocr or ocr_image)
3. Save the configuration

### Permissions

Assign the following permissions to appropriate roles:

- **Administer Beta Tender**: Configure module settings
- **Access Tender Dashboard**: Access the unified tenders overview
- **Process Tender Images**: Upload source media and run batch processing

## Usage

### Unified Tenders Dashboard

1. **Access the Tenders Page**:
   - Navigate to **Content > Tender** tab
   - View a unified dashboard showing all tenders organized by dateline (source + date)

2. **Upload Scanned Images**:
   - Use the upload form at the top of the page
   - Select the source (newspaper or publication)
   - Enter the publication date (dateline)
   - Upload one or more scanned image files
   - Images are automatically grouped by the specified dateline

3. **Browse Datelines**:
   - View a table of all datelines with tender counts
   - See the source name, publication date, and number of tenders
   - Datelines are sorted by publication date (newest first), then by source name

4. **View Dateline Details**:
   - Click "View Tenders" to see all tenders for a specific dateline
    - View comprehensive information for each tender:
       - Title and link to full tender
       - Author who created the tender
       - Created date and time
       - Last updated date and time
       - Moderation state (Needs Review, In Review, Reviewed)
       - Share/sync status

### Source Upload & Batch Processing

1. **Upload Source Media**:
   - Navigate to **Content > Tender > Upload Source**
   - Upload images or PDFs, tagging each upload with its source and publish date
   - Files are saved as media entities for later processing

2. **Process with OCR**:
   - Visit **Content > Tender > Process Tender Batch**
   - Select the media groups you want to process
   - Run the batch to extract text and create draft tender nodes

### Proofreading Workflow

1. **Review Tenders**:
   - From the dateline detail view, click "Edit" on any tender
   - Verify OCR accuracy
   - Correct any errors in extracted fields
   - Advance the moderation state (Needs Review → In Review → Reviewed) when complete

2. **Track Progress**:
   - View moderation state badges in the dateline detail view
   - Monitor share/sync status for published tenders

## Content Types and Fields

### Tender Content Type

- **Title**: Auto-generated from OCR text
- **Source Media**: Media references to uploaded images/documents
- **Tender Announcement Number**: Plain text identifier from the publication
- **Source**: Reference to tender source taxonomy
- **Publish Date**: Date-only field representing the print/announce date
- **Opening Date**: Tender opening date
- **Closing Date**: Tender closing date
- **Region**: Geographical region
- **Categories**: Multiple tender category terms
- **Tender Contractor**: Contractor taxonomy selection
- **Tender Consultancy**: Consultancy taxonomy selection
- **Inviter Company Name**: Plain text organization label
- **Tender Body**: Rich text with required summary (stores OCR output)
- **Content Moderation**: Custom "Tender Proofreading" workflow (Needs Review, In Review, Reviewed)

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

- **TendersController**: Unified tender listing and dateline detail pages
- **BackupRestoreController**: Simple export/import helper for tenders

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