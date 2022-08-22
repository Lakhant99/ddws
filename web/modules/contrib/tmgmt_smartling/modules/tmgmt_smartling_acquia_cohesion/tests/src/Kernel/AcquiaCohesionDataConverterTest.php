<?php

namespace Drupal\Tests\tmgmt_smartling_acquia_cohesion\Kernel;

use Drupal\Tests\tmgmt_smartling\Kernel\SmartlingTestBase;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt_smartling_acquia_cohesion\AcquiaCohesionDataConverter;

/**
 * Tests JSON <-> XML data transformation.
 *
 * @group tmgmt_smartling_acquia_cohesion
 */
class AcquiaCohesionDataConverterTest extends SmartlingTestBase {
  public function testJsonToXmlTransformation() {
    $input = [
      18 =>
        [
          'bMThdW3RpdGxlXVswXVt2YWx1ZQ' =>
            [
              '#text' => 'Test node LC #2 title',
              '#translate' => true,
              '#max_length' => 255,
              '#status' => 0,
              '#parent_label' =>
                [
                  0 => 'Title',
                ],
              'sl-variant' => 'node-18-title][0][value',
            ],
          'bMThdW2JvZHldWzBdW3ZhbHVl' =>
            [
              '#label' => 'Text',
              '#text' => '<p>Test node LC #2 body</p>
',
              '#translate' => true,
              '#format' => 'basic_html',
              '#status' => 0,
              '#parent_label' =>
                [
                  0 => 'Demo body',
                  1 => 'Text',
                ],
              'sl-variant' => 'node-18-body][0][value',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNdWzBdW2VudGl0eV1banNvbl92YWx1ZXNdWzBdW3ZhbHVl' =>
            [
              '#text' => '{"canvas":[{"uid":"cpt_editable_component","type":"component","title":"Editable component","enabled":true,"category":"category-3","componentId":"cpt_editable_component","componentType":"container","uuid":"0feebc76-4afe-4c77-afb8-5623739acf2f","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_half_editable_component","type":"component","title":"Half-editable component","enabled":true,"category":"category-3","componentId":"cpt_half_editable_component","componentType":"container","uuid":"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_not_editable_component","type":"component","title":"Not editable component","enabled":true,"category":"category-3","componentId":"cpt_not_editable_component","componentType":"container","uuid":"d8d0bf9b-0b4b-491b-a1d1-527233a6b713","parentUid":"root","isContainer":0,"children":[]}],"mapper":{},"model":{"0feebc76-4afe-4c77-afb8-5623739acf2f":{"settings":{"title":"Editable component"},"2224bd86-cce1-493a-a76d-9b067bd9d7af":{"text":"","textFormat":"cohesion"},"436b0877-8469-4455-a179-172dd6b75587":"Test node LC #2 LC#1 EC title"},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{"settings":{"title":"Half-editable component"},"904ba304-35f7-43c8-a498-33c77415422c":"Test node LC #2 LC#1 HEC title"},"d8d0bf9b-0b4b-491b-a1d1-527233a6b713":{}},"previewModel":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":{},"91081fd2-5274-418c-bd29-dcbc8ec69e2b":{},"0feebc76-4afe-4c77-afb8-5623739acf2f":{},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{},"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":{},"3c6e6767-4704-485d-a3ff-967ba190cab5":{},"346b7502-233c-47c0-abab-99b447769419":{},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{}},"variableFields":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":[],"91081fd2-5274-418c-bd29-dcbc8ec69e2b":[],"0feebc76-4afe-4c77-afb8-5623739acf2f":[],"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":[],"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":[],"3c6e6767-4704-485d-a3ff-967ba190cab5":[],"346b7502-233c-47c0-abab-99b447769419":[],"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":[]},"meta":{"fieldHistory":[]}}',
              '#translate' => true,
              '#status' => 0,
              '#parent_label' =>
                [
                  0 => 'layout_canvas',
                  1 => 'Values',
                ],
              'sl-variant' => 'node-18-field_layout_canvas][0][entity][json_values][0][value',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNfbmV3XVswXVtlbnRpdHldW2pzb25fdmFsdWVzXVswXVt2YWx1ZQ' =>
            [
              '#text' => '{"canvas":[{"uid":"cpt_editable_component","type":"component","title":"Editable component","enabled":true,"category":"category-3","componentId":"cpt_editable_component","componentType":"container","uuid":"346b7502-233c-47c0-abab-99b447769419","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_half_editable_component","type":"component","title":"Half-editable component","enabled":true,"category":"category-3","componentId":"cpt_half_editable_component","componentType":"container","uuid":"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_not_editable_component","type":"component","title":"Not editable component","enabled":true,"category":"category-3","componentId":"cpt_not_editable_component","componentType":"container","uuid":"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4","parentUid":"root","isContainer":0,"children":[]},{"title":"Component for making editable components","type":"component","componentContentId":"cc_4","uid":"cc_4","componentId":"cpt_component_for_making_editabl","category":"category-3","componentType":"container","uuid":"9dda786b-286a-4664-90c6-e7ebf2fa1b09","parentUid":"root","isContainer":0,"url":"\\/admin\\/cohesion\\/components\\/component_contents\\/4","children":[]}],"mapper":{},"model":{"346b7502-233c-47c0-abab-99b447769419":{"settings":{"title":"Editable component"},"2224bd86-cce1-493a-a76d-9b067bd9d7af":{"text":"","textFormat":"cohesion"},"436b0877-8469-4455-a179-172dd6b75587":"Test node LC #2 LC#2 EC title"},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{"settings":{"title":"Half-editable component"},"904ba304-35f7-43c8-a498-33c77415422c":"Test node LC #2 LC#2 HEC title"},"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4":{}},"previewModel":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":{},"91081fd2-5274-418c-bd29-dcbc8ec69e2b":{},"0feebc76-4afe-4c77-afb8-5623739acf2f":{},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{},"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":{},"3c6e6767-4704-485d-a3ff-967ba190cab5":{},"346b7502-233c-47c0-abab-99b447769419":{},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{}},"variableFields":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":[],"91081fd2-5274-418c-bd29-dcbc8ec69e2b":[],"0feebc76-4afe-4c77-afb8-5623739acf2f":[],"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":[],"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":[],"3c6e6767-4704-485d-a3ff-967ba190cab5":[],"346b7502-233c-47c0-abab-99b447769419":[],"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":[]},"meta":{"fieldHistory":[]}}',
              '#translate' => true,
              '#status' => 0,
              '#parent_label' =>
                [
                  0 => 'layout_canvas_new',
                  1 => 'Values',
                ],
              'sl-variant' => 'node-18-field_layout_canvas_new][0][entity][json_values][0][value',
            ],
        ]
    ];

    $expected = [
      18 =>
        [
          'bMThdW3RpdGxlXVswXVt2YWx1ZQ' =>
            [
              '#text' => 'Test node LC #2 title',
              '#translate' => true,
              '#max_length' => 255,
              '#status' => 0,
              '#parent_label' =>
                [
                  0 => 'Title',
                ],
              'sl-variant' => 'node-18-title][0][value',
            ],
          'bMThdW2JvZHldWzBdW3ZhbHVl' =>
            [
              '#label' => 'Text',
              '#text' => '<p>Test node LC #2 body</p>
',
              '#translate' => true,
              '#format' => 'basic_html',
              '#status' => 0,
              '#parent_label' =>
                [
                  0 => 'Demo body',
                  1 => 'Text',
                ],
              'sl-variant' => 'node-18-body][0][value',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNdWzBdW2VudGl0eV1banNvbl92YWx1ZXNdWzBdW3ZhbHVlLWFjcXVpYV9jb2hlc2lvbl9maWVsZDptb2RlbDowZmVlYmM3Ni00YWZlLTRjNzctYWZiOC01NjIzNzM5YWNmMmY6cHJvcGVydHk6MjIyNGJkODYtY2NlMS00OTNhLWE3NmQtOWIwNjdiZDlkN2Fm' =>
            [
              '#text' => '',
              '#format' => 'cohesion',
              'sl-variant' => 'node-18-field_layout_canvas][0][entity][json_values][0][value-acquia_cohesion_field:model:0feebc76-4afe-4c77-afb8-5623739acf2f:property:2224bd86-cce1-493a-a76d-9b067bd9d7af',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNdWzBdW2VudGl0eV1banNvbl92YWx1ZXNdWzBdW3ZhbHVlLWFjcXVpYV9jb2hlc2lvbl9maWVsZDptb2RlbDowZmVlYmM3Ni00YWZlLTRjNzctYWZiOC01NjIzNzM5YWNmMmY6cHJvcGVydHk6NDM2YjA4NzctODQ2OS00NDU1LWExNzktMTcyZGQ2Yjc1NTg3' =>
            [
              '#text' => 'Test node LC #2 LC#1 EC title',
              'sl-variant' => 'node-18-field_layout_canvas][0][entity][json_values][0][value-acquia_cohesion_field:model:0feebc76-4afe-4c77-afb8-5623739acf2f:property:436b0877-8469-4455-a179-172dd6b75587',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNdWzBdW2VudGl0eV1banNvbl92YWx1ZXNdWzBdW3ZhbHVlLWFjcXVpYV9jb2hlc2lvbl9maWVsZDptb2RlbDoyYTZiNDdiOS0xZWQwLTQ5NjItODIzNi03NmM4ZGJmYjFiNDA6cHJvcGVydHk6OTA0YmEzMDQtMzVmNy00M2M4LWE0OTgtMzNjNzc0MTU0MjJj' =>
            [
              '#text' => 'Test node LC #2 LC#1 HEC title',
              'sl-variant' => 'node-18-field_layout_canvas][0][entity][json_values][0][value-acquia_cohesion_field:model:2a6b47b9-1ed0-4962-8236-76c8dbfb1b40:property:904ba304-35f7-43c8-a498-33c77415422c',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNfbmV3XVswXVtlbnRpdHldW2pzb25fdmFsdWVzXVswXVt2YWx1ZS1hY3F1aWFfY29oZXNpb25fZmllbGQ6bW9kZWw6MzQ2Yjc1MDItMjMzYy00N2MwLWFiYWItOTliNDQ3NzY5NDE5OnByb3BlcnR5OjIyMjRiZDg2LWNjZTEtNDkzYS1hNzZkLTliMDY3YmQ5ZDdhZg' =>
            [
              '#text' => '',
              '#format' => 'cohesion',
              'sl-variant' => 'node-18-field_layout_canvas_new][0][entity][json_values][0][value-acquia_cohesion_field:model:346b7502-233c-47c0-abab-99b447769419:property:2224bd86-cce1-493a-a76d-9b067bd9d7af',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNfbmV3XVswXVtlbnRpdHldW2pzb25fdmFsdWVzXVswXVt2YWx1ZS1hY3F1aWFfY29oZXNpb25fZmllbGQ6bW9kZWw6MzQ2Yjc1MDItMjMzYy00N2MwLWFiYWItOTliNDQ3NzY5NDE5OnByb3BlcnR5OjQzNmIwODc3LTg0NjktNDQ1NS1hMTc5LTE3MmRkNmI3NTU4Nw' =>
            [
              '#text' => 'Test node LC #2 LC#2 EC title',
              'sl-variant' => 'node-18-field_layout_canvas_new][0][entity][json_values][0][value-acquia_cohesion_field:model:346b7502-233c-47c0-abab-99b447769419:property:436b0877-8469-4455-a179-172dd6b75587',
            ],
          'bMThdW2ZpZWxkX2xheW91dF9jYW52YXNfbmV3XVswXVtlbnRpdHldW2pzb25fdmFsdWVzXVswXVt2YWx1ZS1hY3F1aWFfY29oZXNpb25fZmllbGQ6bW9kZWw6MzI4ZjhiOWQtYzcxYy00ZWM2LThjYWItYzZhNmQ5NDNhNmZhOnByb3BlcnR5OjkwNGJhMzA0LTM1ZjctNDNjOC1hNDk4LTMzYzc3NDE1NDIyYw' =>
            [
              '#text' => 'Test node LC #2 LC#2 HEC title',
              'sl-variant' => 'node-18-field_layout_canvas_new][0][entity][json_values][0][value-acquia_cohesion_field:model:328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa:property:904ba304-35f7-43c8-a498-33c77415422c',
            ],
        ]
    ];

    $acquiaDataConverter = new AcquiaCohesionDataConverter($this->loggerMock);

    $this->assertEqual(
      $acquiaDataConverter->findAndConvertCohesionJsonDataToCohesionXmlData($input),
      $expected
    );
  }

  public function testXmlToJsonTransformation() {
    $jobItemMock = $this->getMockBuilder(JobItem::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getData'
      ])
    ->getMock();

    $jobItemMock->expects($this->exactly(2))
      ->method('getData')
      ->withConsecutive([['field_layout_canvas']], [['field_layout_canvas_new']])
      ->willReturnOnConsecutiveCalls([
        0 => [
          'entity' => [
            'json_values' => [
              0 => [
                'value' => [
                  '#text' => '{"canvas":[{"uid":"cpt_editable_component","type":"component","title":"Editable component","enabled":true,"category":"category-3","componentId":"cpt_editable_component","componentType":"container","uuid":"0feebc76-4afe-4c77-afb8-5623739acf2f","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_half_editable_component","type":"component","title":"Half-editable component","enabled":true,"category":"category-3","componentId":"cpt_half_editable_component","componentType":"container","uuid":"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_not_editable_component","type":"component","title":"Not editable component","enabled":true,"category":"category-3","componentId":"cpt_not_editable_component","componentType":"container","uuid":"d8d0bf9b-0b4b-491b-a1d1-527233a6b713","parentUid":"root","isContainer":0,"children":[]}],"mapper":{},"model":{"0feebc76-4afe-4c77-afb8-5623739acf2f":{"settings":{"title":"Editable component"},"2224bd86-cce1-493a-a76d-9b067bd9d7af":{"text":"","textFormat":"cohesion"},"436b0877-8469-4455-a179-172dd6b75587":"Test node LC #2 LC#1 EC title"},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{"settings":{"title":"Half-editable component"},"904ba304-35f7-43c8-a498-33c77415422c":"Test node LC #2 LC#1 HEC title"},"d8d0bf9b-0b4b-491b-a1d1-527233a6b713":{}},"previewModel":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":{},"91081fd2-5274-418c-bd29-dcbc8ec69e2b":{},"0feebc76-4afe-4c77-afb8-5623739acf2f":{},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{},"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":{},"3c6e6767-4704-485d-a3ff-967ba190cab5":{},"346b7502-233c-47c0-abab-99b447769419":{},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{}},"variableFields":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":[],"91081fd2-5274-418c-bd29-dcbc8ec69e2b":[],"0feebc76-4afe-4c77-afb8-5623739acf2f":[],"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":[],"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":[],"3c6e6767-4704-485d-a3ff-967ba190cab5":[],"346b7502-233c-47c0-abab-99b447769419":[],"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":[]},"meta":{"fieldHistory":[]}}'
                ]
              ]
            ]
          ]
        ]
      ], [
        0 => [
          'entity' => [
            'json_values' => [
              0 => [
                'value' => [
                  '#text' => '{"canvas":[{"uid":"cpt_editable_component","type":"component","title":"Editable component","enabled":true,"category":"category-3","componentId":"cpt_editable_component","componentType":"container","uuid":"346b7502-233c-47c0-abab-99b447769419","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_half_editable_component","type":"component","title":"Half-editable component","enabled":true,"category":"category-3","componentId":"cpt_half_editable_component","componentType":"container","uuid":"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_not_editable_component","type":"component","title":"Not editable component","enabled":true,"category":"category-3","componentId":"cpt_not_editable_component","componentType":"container","uuid":"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4","parentUid":"root","isContainer":0,"children":[]},{"title":"Component for making editable components","type":"component","componentContentId":"cc_4","uid":"cc_4","componentId":"cpt_component_for_making_editabl","category":"category-3","componentType":"container","uuid":"9dda786b-286a-4664-90c6-e7ebf2fa1b09","parentUid":"root","isContainer":0,"url":"\\/admin\\/cohesion\\/components\\/component_contents\\/4","children":[]}],"mapper":{},"model":{"346b7502-233c-47c0-abab-99b447769419":{"settings":{"title":"Editable component"},"2224bd86-cce1-493a-a76d-9b067bd9d7af":{"text":"","textFormat":"cohesion"},"436b0877-8469-4455-a179-172dd6b75587":"Test node LC #2 LC#2 EC title"},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{"settings":{"title":"Half-editable component"},"904ba304-35f7-43c8-a498-33c77415422c":"Test node LC #2 LC#2 HEC title"},"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4":{}},"previewModel":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":{},"91081fd2-5274-418c-bd29-dcbc8ec69e2b":{},"0feebc76-4afe-4c77-afb8-5623739acf2f":{},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{},"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":{},"3c6e6767-4704-485d-a3ff-967ba190cab5":{},"346b7502-233c-47c0-abab-99b447769419":{},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{}},"variableFields":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":[],"91081fd2-5274-418c-bd29-dcbc8ec69e2b":[],"0feebc76-4afe-4c77-afb8-5623739acf2f":[],"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":[],"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":[],"3c6e6767-4704-485d-a3ff-967ba190cab5":[],"346b7502-233c-47c0-abab-99b447769419":[],"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":[]},"meta":{"fieldHistory":[]}}'
                ]
              ]
            ]
          ]
        ]
      ]);

    $acquiaDataConverter = $this->getMockBuilder(AcquiaCohesionDataConverter::class)
      ->setMethods([
        'getJobItemFromTmgmtKey'
      ])
      ->setConstructorArgs([$this->loggerMock])
      ->getMock();

    $acquiaDataConverter->expects($this->exactly(2))
      ->method('getJobItemFromTmgmtKey')
      ->willReturn($jobItemMock);

    $input = [
      '18][title][0][value' =>
        [
          '#text' => 'Test node LC #2 title FR',
        ],
      '18][body][0][value' =>
        [
          '#text' => '<p>Test node LC #2 body FR</p>
',
        ],
      '18][field_layout_canvas][0][entity][json_values][0][value-acquia_cohesion_field:model:0feebc76-4afe-4c77-afb8-5623739acf2f:property:2224bd86-cce1-493a-a76d-9b067bd9d7af' =>
        [
          '#text' => '',
        ],
      '18][field_layout_canvas][0][entity][json_values][0][value-acquia_cohesion_field:model:0feebc76-4afe-4c77-afb8-5623739acf2f:property:436b0877-8469-4455-a179-172dd6b75587' =>
        [
          '#text' => 'Test node LC #2 LC#1 EC title FR',
        ],
      '18][field_layout_canvas][0][entity][json_values][0][value-acquia_cohesion_field:model:2a6b47b9-1ed0-4962-8236-76c8dbfb1b40:property:904ba304-35f7-43c8-a498-33c77415422c' =>
        [
          '#text' => 'Test node LC #2 LC#1 HEC title FR',
        ],
      '18][field_layout_canvas_new][0][entity][json_values][0][value-acquia_cohesion_field:model:346b7502-233c-47c0-abab-99b447769419:property:2224bd86-cce1-493a-a76d-9b067bd9d7af' =>
        [
          '#text' => '',
        ],
      '18][field_layout_canvas_new][0][entity][json_values][0][value-acquia_cohesion_field:model:346b7502-233c-47c0-abab-99b447769419:property:436b0877-8469-4455-a179-172dd6b75587' =>
        [
          '#text' => 'Test node LC #2 LC#2 EC title FR',
        ],
      '18][field_layout_canvas_new][0][entity][json_values][0][value-acquia_cohesion_field:model:328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa:property:904ba304-35f7-43c8-a498-33c77415422c' =>
        [
          '#text' => 'Test node LC #2 LC#2 HEC title FR',
        ]
    ];

    $expected = [
      '18][title][0][value' =>
        [
          '#text' => 'Test node LC #2 title FR',
        ],
      '18][body][0][value' =>
        [
          '#text' => '<p>Test node LC #2 body FR</p>
',
        ],
      '18][field_layout_canvas][0][entity][json_values][0][value' =>
        [
          '#text' => '{"canvas":[{"uid":"cpt_editable_component","type":"component","title":"Editable component","enabled":true,"category":"category-3","componentId":"cpt_editable_component","componentType":"container","uuid":"0feebc76-4afe-4c77-afb8-5623739acf2f","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_half_editable_component","type":"component","title":"Half-editable component","enabled":true,"category":"category-3","componentId":"cpt_half_editable_component","componentType":"container","uuid":"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_not_editable_component","type":"component","title":"Not editable component","enabled":true,"category":"category-3","componentId":"cpt_not_editable_component","componentType":"container","uuid":"d8d0bf9b-0b4b-491b-a1d1-527233a6b713","parentUid":"root","isContainer":0,"children":[]}],"mapper":{},"model":{"0feebc76-4afe-4c77-afb8-5623739acf2f":{"settings":{"title":"Editable component"},"2224bd86-cce1-493a-a76d-9b067bd9d7af":{"text":"","textFormat":"cohesion"},"436b0877-8469-4455-a179-172dd6b75587":"Test node LC #2 LC#1 EC title FR"},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{"settings":{"title":"Half-editable component"},"904ba304-35f7-43c8-a498-33c77415422c":"Test node LC #2 LC#1 HEC title FR"},"d8d0bf9b-0b4b-491b-a1d1-527233a6b713":{}},"previewModel":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":{},"91081fd2-5274-418c-bd29-dcbc8ec69e2b":{},"0feebc76-4afe-4c77-afb8-5623739acf2f":{},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{},"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":{},"3c6e6767-4704-485d-a3ff-967ba190cab5":{},"346b7502-233c-47c0-abab-99b447769419":{},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{}},"variableFields":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":[],"91081fd2-5274-418c-bd29-dcbc8ec69e2b":[],"0feebc76-4afe-4c77-afb8-5623739acf2f":[],"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":[],"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":[],"3c6e6767-4704-485d-a3ff-967ba190cab5":[],"346b7502-233c-47c0-abab-99b447769419":[],"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":[]},"meta":{"fieldHistory":[]}}',
        ],
      '18][field_layout_canvas_new][0][entity][json_values][0][value' =>
        [
          '#text' => '{"canvas":[{"uid":"cpt_editable_component","type":"component","title":"Editable component","enabled":true,"category":"category-3","componentId":"cpt_editable_component","componentType":"container","uuid":"346b7502-233c-47c0-abab-99b447769419","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_half_editable_component","type":"component","title":"Half-editable component","enabled":true,"category":"category-3","componentId":"cpt_half_editable_component","componentType":"container","uuid":"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa","parentUid":"root","isContainer":0,"children":[]},{"uid":"cpt_not_editable_component","type":"component","title":"Not editable component","enabled":true,"category":"category-3","componentId":"cpt_not_editable_component","componentType":"container","uuid":"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4","parentUid":"root","isContainer":0,"children":[]},{"title":"Component for making editable components","type":"component","componentContentId":"cc_4","uid":"cc_4","componentId":"cpt_component_for_making_editabl","category":"category-3","componentType":"container","uuid":"9dda786b-286a-4664-90c6-e7ebf2fa1b09","parentUid":"root","isContainer":0,"url":"\\/admin\\/cohesion\\/components\\/component_contents\\/4","children":[]}],"mapper":{},"model":{"346b7502-233c-47c0-abab-99b447769419":{"settings":{"title":"Editable component"},"2224bd86-cce1-493a-a76d-9b067bd9d7af":{"text":"","textFormat":"cohesion"},"436b0877-8469-4455-a179-172dd6b75587":"Test node LC #2 LC#2 EC title FR"},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{"settings":{"title":"Half-editable component"},"904ba304-35f7-43c8-a498-33c77415422c":"Test node LC #2 LC#2 HEC title FR"},"39e8b32f-a7d7-41ee-9f70-99a2e6ad3af4":{}},"previewModel":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":{},"91081fd2-5274-418c-bd29-dcbc8ec69e2b":{},"0feebc76-4afe-4c77-afb8-5623739acf2f":{},"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":{},"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":{},"3c6e6767-4704-485d-a3ff-967ba190cab5":{},"346b7502-233c-47c0-abab-99b447769419":{},"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":{}},"variableFields":{"fd48ba89-3951-4b10-8d47-40c9795c63a6":[],"91081fd2-5274-418c-bd29-dcbc8ec69e2b":[],"0feebc76-4afe-4c77-afb8-5623739acf2f":[],"2a6b47b9-1ed0-4962-8236-76c8dbfb1b40":[],"d5ab2bcb-f5c0-4e76-9344-9c3a20cbe8d8":[],"3c6e6767-4704-485d-a3ff-967ba190cab5":[],"346b7502-233c-47c0-abab-99b447769419":[],"328f8b9d-c71c-4ec6-8cab-c6a6d943a6fa":[]},"meta":{"fieldHistory":[]}}',
        ]
    ];

    $this->assertEqual(
      $acquiaDataConverter->findAndConvertCohesionXmlDataToCohesionJsonData(
        $input
      ),
      $expected
    );
  }
}
