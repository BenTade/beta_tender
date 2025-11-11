<?php

namespace Drupal\Tests\beta_tender\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests for the tender dashboard.
 *
 * @group beta_tender
 */
class DashboardTest extends BrowserTestBase {

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
   * A user with permissions to access the dashboard.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $dashboardUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a user with dashboard access.
    $this->dashboardUser = $this->drupalCreateUser([
      'access tender dashboard',
      'access content',
    ]);
  }

  /**
   * Test that the dashboard page is accessible.
   */
  public function testDashboardAccess(): void {
    // Anonymous users should not have access.
    $this->drupalGet('/admin/content/tender/dashboard');
    $this->assertSession()->statusCodeEquals(403);

    // Logged in user with permission should have access.
    $this->drupalLogin($this->dashboardUser);
    $this->drupalGet('/admin/content/tender/dashboard');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Image Processing Dashboard');
  }

}
