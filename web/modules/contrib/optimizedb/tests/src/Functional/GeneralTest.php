<?php

namespace Drupal\Tests\optimizedb\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Test the module functions.
 *
 * @group optimizedb
 */
class GeneralTest extends BrowserTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'seven';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['optimizedb', 'locale'];

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
   * Sizes tables.
   */
  public function testTablesList() {
    $this->config('optimizedb.settings')
      ->set('tables_size', 0)
      ->save();

    // Function for output all database tables and update their total size.
    _optimizedb_tables_list();

    $this->assertNotEqual($this->config('optimizedb.settings')
      ->get('tables_size'), 0);
  }

  /**
   * Testing module admin page buttons.
   */
  public function testButtonsExecutingCommands() {
    $this->drupalPostForm('admin/config/development/optimizedb', [], t('Optimize tables'));
    $this->assertText(t('The operation completed successfully.'));
  }

  /**
   * Test notify optimize in optimizedb_cron() function.
   */
  public function testCronNotifyOptimize() {
    $config = $this->config('optimizedb.settings');

    $config
      ->set('optimization_period', 1)
      ->set('last_optimization', \Drupal::time()
          ->getRequestTime() - ((3600 * 24) * 2))
      ->set('notify_optimize', FALSE)
      ->save();

    $this->cronRun();
    $this->assertTrue($this->config('optimizedb.settings')
      ->get('notify_optimize'));
  }

}
