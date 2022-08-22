<?php

namespace Drupal\Tests\optimizedb\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Testing the performance of operations on tables.
 *
 * @link admin/config/development/optimizedb/list_tables @endlink
 *
 * @group optimizedb
 */
class ListTablesOperationExecuteTest extends BrowserTestBase {

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
   * Performing operations on tables.
   */
  public function testListTablesOperationExecute() {
    $this->drupalPostForm('admin/config/development/optimizedb/list_tables', [], t('Check tables'));
    $this->assertText(t('To execute, you must select at least one table from the list.'));

    // Output all database tables.
    $tables = _optimizedb_tables_list();
    $table_name = key($tables);

    $edit = [];
    // Selected first table in list.
    $edit['tables[' . $table_name . ']'] = $table_name;

    $this->drupalPostForm('admin/config/development/optimizedb/list_tables', $edit, t('Check tables'));
    $this->assertText(t('The operation completed successfully.'));
  }

}
