<?php

namespace Drupal\Tests\optimizedb\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the page hide notification.
 *
 * @group optimizedb
 */
class HideNotificationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'seven';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['optimizedb'];

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * A user with permission the settings module.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * @inheritDoc
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(['administer optimizedb settings']);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Display notification of the need to perform optimization.
   */
  public function testHideNotification() {
    $config = $this->config('optimizedb.settings');

    $config
      ->set('notify_optimize', FALSE)
      ->save();

    $this->drupalGet('admin/config/development/optimizedb/hide');
    $this->assertText(t('Alerts are not available.'));

    $config
      ->set('notify_optimize', TRUE)
      ->save();

    $this->drupalGet('admin/config/development/optimizedb/hide');
    $this->assertNoText(t('Alerts are not available.'));

    $notify_optimize = $this->config('optimizedb.settings')
      ->get('notify_optimize');
    $this->assertFalse($notify_optimize);
  }

}
