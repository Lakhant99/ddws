<?php

namespace Drupal\Tests\tmgmt_smartling\Functional;

/**
 * Basic requirements tests.
 *
 * @group tmgmt_smartling
 */
class RequirementsTest extends SmartlingTestBase {

  /**
   * Test module requirements.
   */
  public function testRequirements() {
    $this->drupalGet("/admin/reports/status");
    $this->assertText("Smartling PHP max_execution_time");
    $this->assertText("PHP max_execution_time is recommended to be set at least 300. Current value is 30");
    $this->assertText("Background processes might take time to be done");
  }

}
