# Changelog

All notable changes to the Beta Tender module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-10

### Added
- Initial release of Beta Tender module
- Multi-level image processing dashboard
  - Level 1: Date and source organization
  - Level 2: Image arrangement with tabledrag functionality
- OCR integration with configurable backend (document_ocr or ocr_image)
- Batch processing for creating tenders from image groups
- Proofreading dashboard with status filtering
- Entity Share integration for content synchronization
- Tender content type with comprehensive field structure
- Scanned Image media type
- Three taxonomy vocabularies (Categories, Regions, Sources)
- Comprehensive permission system
- Template files with Twig
- CSS and JavaScript for enhanced UX
- Full inline documentation
- Basic test suite (Kernel and Functional tests)

### Features
- **Image Management**: Upload and organize scanned images by date and source
- **OCR Processing**: Extract text from images with batch processing
- **Drag-and-Drop**: Group multiple images into single tenders
- **Editorial Workflow**: Track proofreading status via moderation states
- **Content Sync**: Push approved tenders to production sites

### Technical Details
- Drupal 11 compatible
- Service-based architecture with dependency injection
- PSR-12 coding standards
- Future-proof design for Drupal 12+
- No deprecated code or procedural patterns

## [Unreleased]

### Added
- Unified Tenders dashboard consolidating three separate pages into one
- Dateline detail view showing comprehensive tender information:
  - Author information
  - Created and last updated timestamps
  - Moderation status indicators
  - Share/sync status
- New TendersController with mainPage() and datelineDetail() methods
- New templates: tender-main-page.html.twig and tender-dateline-detail.html.twig
- Sorting by publication date, then by creation date
- Test coverage for unified tenders page

### Changed
- Menu structure now shows single "Tender" tab instead of multiple sub-tabs
- Main tenders page now at /admin/content/tender
- Updated documentation to reflect consolidated interface

### Removed
- Inline UploadScannedImagesForm and dashboard upload widget

### Planned
- Enhanced OCR text parsing with NLP
- Advanced image preprocessing options
- Automated category and region detection
- REST API endpoints for external integrations
- Drush commands for batch operations
- Enhanced test coverage
- Performance optimizations for large image sets
