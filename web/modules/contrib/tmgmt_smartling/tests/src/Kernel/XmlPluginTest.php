<?php

namespace Drupal\Tests\tmgmt_smartling\Kernel;

use Drupal\Core\File\FileSystemInterface;
use Drupal\tmgmt_smartling\Plugin\tmgmt_file\Format\Xml;

/**
 * Tests for smartling xml plugin.
 *
 * @group tmgmt_smartling
 */
class XmlPluginTest extends SmartlingTestBase {
  protected $xmlPlugin;

  public function setUp(): void {
    parent::setUp();

    require_once __DIR__ . '/../../../vendor/autoload.php';

    $this->xmlPlugin = new Xml();
  }

  /**
   * Test filtering unrelated directives.
   */
  public function testEscapingUnEscapingOfPluralStringDelimiterSymbolOnExportImportSteps() {
    $source_string = "1 new comment@count new comments\x03@count brand new comments";

    \Drupal::state()->set('tmgmt.test_source_data', [
      'dummy' => [
        'deep_nesting' => [
          '#text' => $source_string,
          '#label' => 'Label of deep nested item @id',
        ],
        '#label' => 'Dummy item',
      ],
    ]);

    $job = parent::createJob();
    $job->addItem('test_source', 'test', 1);

    $job->settings = [];
    $job->translator = 'smartling';

    $exported_content = $this->xmlPlugin->export($job) . PHP_EOL;

    $this->assertTrue(strstr($exported_content, "1 new comment!PLURAL_STRING_DELIMITER@count new comments!PLURAL_STRING_DELIMITER@count brand new comments") !== FALSE);

    $file = file_save_data($exported_content, "public://test.xml", FileSystemInterface::EXISTS_REPLACE);

    $imported_string = $this->xmlPlugin->import($file->getFileUri(), $job)[1]['dummy']['deep_nesting']['#text'];

    $this->assertEquals($source_string, $imported_string);
  }

  /**
   * Test sl-variant parsing: valid string.
   */
  public function testParseValidSlVariantKey() {
    $parsed_sl_variant = $this->invokeMethod(
      $this->xmlPlugin, 'parseSmartlingSlVariantKey', ['node-1-title][0][value']
    );

    $this->assertEqual($parsed_sl_variant['entity_type'], 'node');
    $this->assertEqual($parsed_sl_variant['entity_id'], 1);
    $this->assertEqual($parsed_sl_variant['field_name'], 'title');
    $this->assertEqual($parsed_sl_variant['field_index'], 0);
    $this->assertEqual($parsed_sl_variant['field_value_name'], 'value');
  }

  /**
   * Test sl-variant parsing: invalid string (entity data).
   */
  public function testParseInvalidSlVariantKeyEntityData() {
    $parsed_sl_variant = $this->invokeMethod(
      $this->xmlPlugin, 'parseSmartlingSlVariantKey', ['node-title][0][value']
    );

    $this->assertEqual($parsed_sl_variant, []);
  }

  /**
   * Test sl-variant parsing: invalid string (field data).
   */
  public function testParseInvalidSlVariantKeyFieldData() {
    $parsed_sl_variant = $this->invokeMethod(
      $this->xmlPlugin, 'parseSmartlingSlVariantKey', ['node-1-title][0]']
    );

    $this->assertEqual($parsed_sl_variant, []);
  }
}
