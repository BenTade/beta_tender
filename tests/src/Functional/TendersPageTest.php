<?php

namespace Drupal\Tests\beta_tender\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests for the unified Tenders page.
 *
 * @group beta_tender
 */
class TendersPageTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'beta_tender',
    'node',
    'field',
    'file',
    'image',
    'media',
    'taxonomy',
    'datetime',
    'options',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permissions to access the tenders page.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $tendersUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a user with tenders access.
    $this->tendersUser = $this->drupalCreateUser([
      'access tender dashboard',
      'access content',
    ]);
  }

  /**
   * Test that the main tenders page is accessible.
   */
  public function testTendersPageAccess(): void {
    // Anonymous users should not have access.
    $this->drupalGet('/admin/content/tender');
    $this->assertSession()->statusCodeEquals(403);

    // Logged in user with permission should have access.
    $this->drupalLogin($this->tendersUser);
    $this->drupalGet('/admin/content/tender');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Tenders');
  }

  /**
   * Test that the dateline detail page is accessible.
   */
  public function testDatelineDetailAccess(): void {
    // Login as user with permission.
    $this->drupalLogin($this->tendersUser);
    
    // Try to access a dateline detail page (will show empty if no tenders).
    $this->drupalGet('/admin/content/tender/dateline/1/2024-01-01');
    $this->assertSession()->statusCodeEquals(200);
  }

}
