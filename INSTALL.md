# Beta Tender Module - Installation Guide

This guide will walk you through the complete installation and configuration process for the Beta Tender module.

## Prerequisites

Before installing the Beta Tender module, ensure your environment meets these requirements:

### System Requirements
- **Drupal**: Version 11.x
- **PHP**: 8.1 or higher
- **Database**: MySQL 5.7.8+, MariaDB 10.3.7+, PostgreSQL 10+, or SQLite 3.26+
- **Web Server**: Apache 2.4+ or Nginx 1.16+

### Required Drupal Modules
The following core modules are required and will be automatically enabled:
- node
- file
- image
- datetime
- options
- taxonomy
- media
- text
- user

### Required Contributed Modules
- **entity_share** - For content synchronization features
  ```bash
  composer require drupal/entity_share
  drush en entity_share entity_share_client
  ```

### OCR Backend (Choose One)
You must install at least one of these OCR modules:
- **document_ocr** - Recommended for multi-page documents
- **ocr_image** - Suitable for single images

## Installation Steps

### 1. Download the Module

#### Option A: Using Composer (Recommended)
```bash
# Add the module to your project
composer require drupal/beta_tender

# Or if installing from a custom repository
composer require drupal/beta_tender --prefer-source
```

#### Option B: Manual Installation
1. Download the module from the repository
2. Extract to `modules/custom/beta_tender`
3. Ensure the directory structure matches the module layout

### 2. Enable the Module

Using Drush:
```bash
drush en beta_tender -y
```

Using the Drupal UI:
1. Navigate to **Extend** (`/admin/modules`)
2. Search for "Beta Tender"
3. Check the box next to "Beta Tender"
4. Click "Install"
5. Confirm installation

### 3. Install Dependencies

If not already installed, enable required contributed modules:

```bash
# Install and enable Entity Share
composer require drupal/entity_share
drush en entity_share entity_share_client -y

# Install one of the OCR backends
composer require drupal/document_ocr
# OR
composer require drupal/ocr_image

# Enable the OCR module
drush en document_ocr -y
# OR
drush en ocr_image -y
```

### 4. Verify Installation

Check that all components were installed correctly:

```bash
# Check module status
drush pm:list --type=module --filter=beta_tender

# Verify configuration
drush cget beta_tender.settings

# Check content types
drush eval "print_r(\Drupal::entityTypeManager()->getStorage('node_type')->load('tender'));"
```

## Post-Installation Configuration

### 1. Configure OCR Backend

1. Navigate to **Configuration > Content authoring > Beta Tender Settings**
   - URL: `/admin/config/beta_tender`

2. Select your preferred OCR backend:
   - **Document OCR**: Best for multi-page PDF scans
   - **OCR Image**: Best for individual image files

3. Click "Save configuration"

### 2. Set Up Permissions

Navigate to **People > Permissions** (`/admin/people/permissions`) and assign appropriate permissions:

#### Content Manager Role
- ✓ Access Tender Dashboard
- ✓ Process Tender Images
- ✓ Proofread Tenders
- ✓ Assign Tenders
- ✓ Create and edit tender content
- ✓ Create and edit scanned image media

#### Administrator Role
- ✓ All Beta Tender permissions
- ✓ Administer Beta Tender

#### Editor Role
- ✓ Proofread Tenders
- ✓ Edit tender content

Example Drush commands:
```bash
# Grant permissions to a role
drush role:perm:add content_manager "access tender dashboard"
drush role:perm:add content_manager "process tender images"
drush role:perm:add content_manager "proofread tenders"
drush role:perm:add editor "proofread tenders"
```

### 3. Configure Taxonomy Terms

Create initial taxonomy terms for your tenders:

#### Tender Categories
Navigate to **Structure > Taxonomy > Tender Categories** (`/admin/structure/taxonomy/manage/tender_categories/add`)

Example terms:
- Construction
- IT Services
- Healthcare
- Education
- Transportation

#### Regions
Navigate to **Structure > Taxonomy > Regions** (`/admin/structure/taxonomy/manage/regions/add`)

Example terms:
- North Region
- South Region
- East Region
- West Region
- Central

#### Tender Sources
Navigate to **Structure > Taxonomy > Tender Sources** (`/admin/structure/taxonomy/manage/tender_sources/add`)

Example terms:
- The Daily Chronicle
- Government Gazette
- Business Times
- Regional News

Or use Drush:
```bash
# Create terms programmatically
drush php-eval "
\$term = \Drupal\taxonomy\Entity\Term::create([
  'vid' => 'tender_sources',
  'name' => 'The Daily Chronicle',
]);
\$term->save();
"
```

### 4. Configure Entity Share (Optional)

If using content synchronization features:

1. Navigate to **Configuration > Web services > Entity Share**
   - URL: `/admin/config/services/entity_share`

2. Create a channel for tender content:
   - Channel ID: `tender_channel`
   - Entities: Select "Tender" content type
   - Fields: Include all tender fields

3. Configure client settings for the production site

For detailed Entity Share configuration, refer to the [Entity Share documentation](https://www.drupal.org/docs/contributed-modules/entity-share).

### 5. Configure File System (If Needed)

Ensure proper permissions for file directories:

```bash
# Check file directory permissions
ls -la sites/default/files/

# Create tender-specific directories with proper permissions
mkdir -p sites/default/files/tender/scanned
mkdir -p sites/default/files/scanned_images
chmod 775 sites/default/files/tender/scanned
chmod 775 sites/default/files/scanned_images

# If using Apache, verify .htaccess exists
ls -la sites/default/files/.htaccess
```

### 6. Clear Caches

After configuration, clear all caches:

```bash
drush cr
```

Or via UI: **Configuration > Development > Performance** > "Clear all caches"

## Verification Tests

### Test 1: Access Dashboard
1. Log in as a user with "access tender dashboard" permission
2. Navigate to **Content > Tender** tab
3. Verify the dashboard loads without errors

### Test 2: Create Test Media
1. Navigate to **Content > Media > Add media > Scanned Image**
2. Upload a test image
3. Select a source from the dropdown
4. Save the media entity

### Test 3: View Dashboard
1. Return to **Content > Tender > Image Processing Dashboard**
2. Verify your uploaded image appears organized by date
3. Click on the source to view the image arrangement page

### Test 4: Test OCR (Optional)
If OCR backend is configured:
1. On the image arrangement page, select test images
2. Click "Process OCR and Create Tenders"
3. Monitor the batch processing
4. Verify a tender node was created

## Troubleshooting

### Module Won't Enable
- Check PHP version: `php -v`
- Verify all dependencies are installed: `drush pm:list`
- Check error logs: `drush watchdog:show --severity=Error`

### Dashboard Not Accessible
- Clear cache: `drush cr`
- Verify permissions: Check user role has "access tender dashboard"
- Check routing: `drush router:rebuild`

### OCR Not Working
- Verify OCR module is enabled: `drush pm:list --type=module --filter=ocr`
- Check configuration: Visit `/admin/config/beta_tender`
- Review logs: **Reports > Recent log messages**

### Images Not Appearing
- Check file permissions: `ls -la sites/default/files/`
- Verify media entities have the source field populated
- Clear image cache: `drush image-flush --all`

### Performance Issues
- Increase PHP memory limit in `php.ini`:
  ```ini
  memory_limit = 512M
  max_execution_time = 300
  ```
- Enable Drupal caching: **Configuration > Performance**
- Consider using a production-grade database

## Next Steps

After successful installation:

1. **Read the User Guide**: See README.md for detailed usage instructions
2. **Create Sample Data**: Add taxonomy terms and upload test images
3. **Test Workflow**: Process a complete tender from image to publication
4. **Configure Backups**: Set up regular database and file backups
5. **Monitor Performance**: Use Performance module or New Relic for monitoring

## Getting Help

- **Documentation**: See README.md and inline code comments
- **Issue Queue**: Report bugs at the project repository
- **Community**: Join Drupal Slack #contribute channel

## Uninstallation

To completely remove the module:

```bash
# Disable the module
drush pm:uninstall beta_tender -y

# Remove configuration (if desired)
drush config:delete node.type.tender
drush config:delete media.type.scanned_image
drush config:delete taxonomy.vocabulary.tender_categories
drush config:delete taxonomy.vocabulary.regions
drush config:delete taxonomy.vocabulary.tender_sources

# Remove via Composer
composer remove drupal/beta_tender
```

**Warning**: Uninstalling will delete all tender content and configuration. Back up your database first!
