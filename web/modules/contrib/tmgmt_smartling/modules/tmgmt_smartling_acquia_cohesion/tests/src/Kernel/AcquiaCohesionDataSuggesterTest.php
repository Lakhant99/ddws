<?php

namespace Drupal\Tests\tmgmt_smartling_acquia_cohesion\Kernel;

use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Tests\tmgmt_smartling\Kernel\SmartlingTestBase;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt_smartling_acquia_cohesion\AcquiaCohesionDataSuggester;

/**
 * Tests job items suggestions.
 *
 * @group tmgmt_smartling_acquia_cohesion
 */
class AcquiaCohesionDataSuggesterTest extends SmartlingTestBase {
  /**
   * Returns component content job items from job items data as suggestions.
   */
  public function testJobItemsSuggestions() {
    $componentContentMock = $this->getMockBuilder(ComponentContent::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getEntityTypeId',
        'id'
      ])
      ->getMock();

    $componentContentMock->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn('component_content');

    $componentContentMock->expects($this->once())
      ->method('id')
      ->willReturn(4);

    $acquiaDataSuggester = $this->getMockBuilder(AcquiaCohesionDataSuggester::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'loadComponentContent'
      ])
      ->getMock();

    $acquiaDataSuggester->expects($this->once())
      ->method('loadComponentContent')
      ->with(4)
      ->willReturn($componentContentMock);

    $jobItemMock = $this->getMockBuilder(JobItem::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getData',
        'id'
      ])
      ->getMock();

    $jobItemMock->expects($this->once())
      ->method('getData')
      ->willReturn([
        'field_layout_canvas_new' => [
          0 => [
            'entity' => [
              'json_values' => [
                0 => [
                  'value' => [
                    '#text' => '{"canvas":[{"uid":"cpt_editable_component","type":"component","title":"Editable component","enabled":true,"category":"category-3","componentId":"cpt_editable_component","componentType":"container","uuid":"346b7502-233c-47c0-abab-99b447769419","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_half_editable_component","type":"component","title":"Half-editable component","enabled":true,"category":"category-3","componentId":"cpt_half_editable_component","componentType":"container","uuid":"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_not_editable_component","type":"component","title":"Not editable component","enabled":true,"category":"category-3","componentId":"cpt_not_editable_component","componentType":"container","uuid":"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4","parentUid":"root","isContainer":0,"children":[]},{"title":"Component for making editable components","type":"component","componentContentId":"cc_4","uid":"cc_4","componentId":"cpt_component_for_making_editabl","category":"category-3","componentType":"container","uuid":"9dda786b-286a-4664-90c6-e7ebf2fa1b09","parentUid":"root","isContainer":0,"url":"\\/admin\\/cohesion\\/components\\/component_contents\\/4","children":[]}],"mapper":{},"model":{"346b7502-233c-47c0-abab-99b447769419":{"settings":{"title":"Editable component"},"2224bd86-cce1-493a-a76d-9b067bd9d7af":{"text":"","textFormat":"cohesion"},"436b0877-8469-4455-a179-172dd6b75587":"Test node LC #2 LC#2 EC title FR"},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{"settings":{"title":"Half-editable component"},"904ba304-35f7-43c8-a498-33c77415422c":"Test node LC #2 LC#2 HEC title FR"},"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4":{}},"previewModel":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":{},"91081fd2-5274-418c-bd29-dcbc8ec69e2b":{},"0feebc76-4afe-4c77-afb8-5623739acf2f":{},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{},"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":{},"3c6e6767-4704-485d-a3ff-967ba190cab5":{},"346b7502-233c-47c0-abab-99b447769419":{},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{}},"variableFields":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":[],"91081fd2-5274-418c-bd29-dcbc8ec69e2b":[],"0feebc76-4afe-4c77-afb8-5623739acf2f":[],"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":[],"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":[],"3c6e6767-4704-485d-a3ff-967ba190cab5":[],"346b7502-233c-47c0-abab-99b447769419":[],"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":[]},"meta":{"fieldHistory":[]}}'
                  ]
                ]
              ]
            ]
          ]
        ]
      ]);

    $jobItemMock->expects($this->once())
      ->method('id')
      ->willReturn(1);

    $result = $acquiaDataSuggester->suggestCohesionContentComponents([$jobItemMock]);

    $this->assertEqual(
      count($result),
      1
    );

    $this->assertEqual(
      $result[0]["from_item"],
      1
    );

    $this->assertEqual(
      $result[0]["job_item"] instanceof JobItemInterface,
      true
    );

    $this->assertEqual(
      (string) $result[0]["reason"],
      "Referenced content component"
    );
  }
}
