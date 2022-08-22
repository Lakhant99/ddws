<?php

namespace Drupal\Tests\tmgmt_smartling\Kernel;

use Drupal\Core\Field\FieldItemList;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\JobItemInterface;
use Smartling\AuditLog\Params\CreateRecordParameters;

/**
 * Tests file download flow.
 *
 * @group tmgmt_smartling
 */
class DownloadFlowTest extends SmartlingTestBase {

  /**
   * Download success full flow.
   */
  public function testDownloadSuccessFullFlow() {
    $translate_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitError');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->with($translate_job)
      ->willReturn(['translationRequestUid' => 'test', 'fileUri' => 'test_file_uri.xml']);

    $this->apiWrapperMock->expects($this->once())
      ->method('getApi')
      ->with('file')
      ->willReturn($this->fileApiMock);

    $this->apiWrapperMock->expects($this->once())
      ->method('createAuditLogRecord')
      ->with(
        $translate_job,
        NULL,
        \Drupal::currentUser(),
        CreateRecordParameters::ACTION_TYPE_DOWNLOAD
      );

    $this->fileApiMock->expects($this->once())
      ->method('downloadFile')
      ->with(
        'test_file_uri.xml',
        'de',
        $this->callback(function($subject) {
          $params = $subject->exportToArray();

          return $params['retrievalType'] == 'published';
        })
      )
      ->willReturn('xml');

    $this->pluginMock->expects($this->once())
      ->method('validateImport')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn(TRUE);

    $this->pluginMock->expects($this->once())
      ->method('import')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn([]);

    $this->translationRequestManagerMock->expects($this->once())
      ->method('commitSuccessfulDownload')
      ->with($translate_job)
      ->willReturn(FALSE);

    $this->apiWrapperMock->expects($this->at(2))
      ->method('createFirebaseRecord')
      ->with('tmgmt_smartling', 'notifications', 10, [
        "message" => 'Translation for "public://tmgmt_smartling_translations/test_file_uri.xml" (job id = 1) was successfully downloaded and imported.',
        "type" => "status",
      ]);

    $this->apiWrapperMock->expects($this->at(3))
      ->method('createFirebaseRecord')
      ->with('tmgmt_smartling', 'notifications', 10, [
        "message" => 'Can\'t update update state and exported date for translation request. See logs for more info.',
        "type" => "warning",
      ]);

    tmgmt_smartling_download_file($translate_job);
  }

  /**
   * Download fail flow: get translation request failed.
   */
  public function testDownloadFailFlowGetTranslationRequestFailed() {
    $translate_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitError');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->with($translate_job)
      ->willReturn(FALSE);

    $this->apiWrapperMock->expects($this->never())
      ->method('getApi');

    $this->fileApiMock->expects($this->never())
      ->method('downloadFile');

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitSuccessfulDownload');

    $this->pluginMock->expects($this->never())
      ->method('validateImport');

    $this->pluginMock->expects($this->never())
      ->method('import');

    $this->apiWrapperMock->expects($this->once())
      ->method('createFirebaseRecord')
      ->with('tmgmt_smartling', 'notifications', 10, [
        "message" => 'File JobID1_en_de.xml (job id = 1) wasn\'t downloaded: can\'t find related translation request. See logs for more info.',
        "type" => "error",
      ]);

    tmgmt_smartling_download_file($translate_job);
  }

  /**
   * Download success partial flow: import skipped.
   */
  public function testDownloadSuccessPartialFlowImportSkipped() {
    $translate_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);
    $translate_job->set('job_file_content_hash', '0f635d0e0f3874fff8b581c132e6c7a7');

    // Remove job items in order to not to force import. We need to avoid
    // Drupal::entityTypeManager mocking. See tmgmt_smartling_download_file,
    // line 94.
    foreach ($translate_job->getItems() as $item) {
      $item->delete();
    }

    $translate_job->save();

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitError');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->willReturn(['translationRequestUid' => 'test', 'fileUri' => 'test_file_uri.xml']);

    $this->apiWrapperMock->expects($this->once())
      ->method('getApi')
      ->with('file')
      ->willReturn($this->fileApiMock);

    $this->apiWrapperMock->expects($this->once())
      ->method('createAuditLogRecord')
      ->with(
        $translate_job,
        NULL,
        \Drupal::currentUser(),
        CreateRecordParameters::ACTION_TYPE_DOWNLOAD
      );

    $this->fileApiMock->expects($this->once())
      ->method('downloadFile')
      ->with(
        'test_file_uri.xml',
        'de',
        $this->callback(function($subject) {
          $params = $subject->exportToArray();

          return $params['retrievalType'] == 'published';
        })
      )
      ->willReturn('xml');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('commitSuccessfulDownload')
      ->with($translate_job)
      ->willReturn(TRUE);

    $this->pluginMock->expects($this->once())
      ->method('validateImport')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn(TRUE);

    $this->pluginMock->expects($this->never())
      ->method('import');

    $this->apiWrapperMock->expects($this->once())
      ->method('createFirebaseRecord')
      ->with('tmgmt_smartling', 'notifications', 10, [
        "message" => 'Translation for "public://tmgmt_smartling_translations/test_file_uri.xml" (job id = 1) was successfully downloaded but import was skipped: downloaded and existing translations are equal.',
        "type" => "warning",
      ]);

    tmgmt_smartling_download_file($translate_job);
  }

  /**
   * Download fail flow.
   */
  public function testDownloadFailFlow() {
    $exception = new \Exception("Test");
    $translation_request = ['translationRequestUid' => 'test', 'fileUri' => 'test_file_uri.xml'];
    $translate_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->with($translate_job)
      ->willReturn($translation_request);

    $this->apiWrapperMock->expects($this->once())
      ->method('getApi')
      ->with('file')
      ->willReturn($this->fileApiMock);

    $this->apiWrapperMock->expects($this->once())
      ->method('createAuditLogRecord')
      ->with(
        $translate_job,
        NULL,
        \Drupal::currentUser(),
        CreateRecordParameters::ACTION_TYPE_DOWNLOAD
      );

    $this->fileApiMock->expects($this->once())
      ->method('downloadFile')
      ->with(
        'test_file_uri.xml',
        'de',
        $this->callback(function($subject) {
          $params = $subject->exportToArray();

          return $params['retrievalType'] == 'published';
        })
      )
      ->will($this->throwException($exception));

    $this->translationRequestManagerMock->expects($this->once())
      ->method('commitError')
      ->with($translate_job, $translation_request, $exception);

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitSuccessfulDownload');

    $this->pluginMock->expects($this->never())
      ->method('validateImport');

    $this->pluginMock->expects($this->never())
      ->method('import');

    $this->apiWrapperMock->expects($this->once())
      ->method('createFirebaseRecord')
      ->with('tmgmt_smartling', 'notifications', 10, [
        "message" => 'File test_file_uri.xml (job id = 1) wasn\'t downloaded. Please see logs for more info.',
        "type" => "error",
      ]);

    tmgmt_smartling_download_file($translate_job);
  }

  /**
   * Download fail flow: validation failed.
   */
  public function testDownloadFailFlowValidationFailed() {
    $translate_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitError');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->with($translate_job)
      ->willReturn(['translationRequestUid' => 'test', 'fileUri' => 'test_file_uri.xml']);

    $this->apiWrapperMock->expects($this->once())
      ->method('getApi')
      ->with('file')
      ->willReturn($this->fileApiMock);

    $this->apiWrapperMock->expects($this->once())
      ->method('createAuditLogRecord')
      ->with(
        $translate_job,
        NULL,
        \Drupal::currentUser(),
        CreateRecordParameters::ACTION_TYPE_DOWNLOAD
      );

    $this->fileApiMock->expects($this->once())
      ->method('downloadFile')
      ->with(
        'test_file_uri.xml',
        'de',
        $this->callback(function($subject) {
          $params = $subject->exportToArray();

          return $params['retrievalType'] == 'published';
        })
      )
      ->willReturn('xml');

    $this->pluginMock->expects($this->once())
      ->method('validateImport')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn(FALSE);

    $this->pluginMock->expects($this->never())
      ->method('import');

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitSuccessfulDownload');

    $this->apiWrapperMock->expects($this->once())
      ->method('createFirebaseRecord')
      ->with('tmgmt_smartling', 'notifications', 10, [
        "message" => 'Translation for "public://tmgmt_smartling_translations/test_file_uri.xml" (job id = 1) was successfully downloaded but validation failed. See logs for more info.',
        "type" => "error",
      ]);

    tmgmt_smartling_download_file($translate_job);
  }

  /**
   * Download fail flow: import failed.
   */
  public function testDownloadFailFlowImportFailed() {
    $exception = new \Exception("Test");
    $translation_request = ['translationRequestUid' => 'test', 'fileUri' => 'test_file_uri.xml'];
    $translate_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->with($translate_job)
      ->willReturn($translation_request);

    $this->apiWrapperMock->expects($this->once())
      ->method('getApi')
      ->with('file')
      ->willReturn($this->fileApiMock);

    $this->apiWrapperMock->expects($this->once())
      ->method('createAuditLogRecord')
      ->with(
        $translate_job,
        NULL,
        \Drupal::currentUser(),
        CreateRecordParameters::ACTION_TYPE_DOWNLOAD
      );

    $this->fileApiMock->expects($this->once())
      ->method('downloadFile')
      ->with(
        'test_file_uri.xml',
        'de',
        $this->callback(function($subject) {
          $params = $subject->exportToArray();

          return $params['retrievalType'] == 'published';
        })
      )
      ->willReturn('xml');

    $this->pluginMock->expects($this->once())
      ->method('validateImport')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn(TRUE);

    $this->pluginMock->expects($this->once())
      ->method('import')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->will($this->throwException($exception));

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitSuccessfulDownload');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('commitError')
      ->with($translate_job, $translation_request, $exception);

    $this->apiWrapperMock->expects($this->once())
      ->method('createFirebaseRecord')
      ->with('tmgmt_smartling', 'notifications', 10, [
        "message" => 'Translation for "public://tmgmt_smartling_translations/test_file_uri.xml" (job id = 1) was successfully downloaded but import failed. See logs for more info.',
        "type" => "error",
      ]);

    tmgmt_smartling_download_file($translate_job);
  }

  /**
   * Download success full flow: download by TMGMT Job (all TMGMT Job Items).
   */
  public function testDownloadByTmgmtJob() {
    $real_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);

    $field_item_list = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getValue'
      ])
      ->getMock();

    $field_item_list->expects($this->any())
      ->method('getValue')
      ->willReturn([
        0 => [
          'value' => NULL
        ]
      ]);

    $job_item_mock_1 = $this->getMockBuilder(JobItem::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'save',
        'id',
        'setState',
        'addTranslatedData',
        'getItemId',
        'getItemType'
      ])
      ->getMock();

    $job_item_mock_1->expects($this->any())
      ->method('id')
      ->willReturn(1);

    $job_item_mock_1->expects($this->any())
      ->method('getItemId')
      ->willReturn(1);

    $job_item_mock_1->expects($this->any())
      ->method('getItemType')
      ->willReturn('node');

    $job_item_mock_1->expects($this->once())
      ->method('setState')
      ->with(JobItemInterface::STATE_ACTIVE);

    $job_item_mock_1->expects($this->once())
      ->method('addTranslatedData');

    $job_item_mock_2 = $this->getMockBuilder(JobItem::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'id',
        'setState',
        'addTranslatedData',
        'getItemId',
        'getItemType'
      ])
      ->getMock();

    $job_item_mock_2->expects($this->any())
      ->method('id')
      ->willReturn(2);

    $job_item_mock_1->expects($this->any())
      ->method('getItemId')
      ->willReturn(1);

    $job_item_mock_1->expects($this->any())
      ->method('getItemType')
      ->willReturn('node');

    $job_item_mock_2->expects($this->once())
      ->method('setState')
      ->with(JobItemInterface::STATE_ACTIVE);

    $job_item_mock_2->expects($this->once())
      ->method('addTranslatedData');

    $translate_job = $this->getMockBuilder(Job::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'id',
        'getItems',
        'getTranslator',
        'getTranslatorPlugin',
        'getSetting',
        'getSourceLangcode',
        'getTargetLangcode',
        'save',
        'set',
        'get',
        'addMessage',
        'getFieldDefinitions'
      ])
      ->getMock();

    $translate_job->expects($this->any())
      ->method('id')
      ->willReturn(1);

    $translate_job->expects($this->any())
      ->method('getSetting')
      ->willReturn('public');

    $translate_job->expects($this->any())
      ->method('getTranslator')
      ->willReturn($real_job->getTranslator());

    $translate_job->expects($this->any())
      ->method('getTranslatorPlugin')
      ->willReturn($real_job->getTranslatorPlugin());

    $translate_job->expects($this->any())
      ->method('getSourceLangcode')
      ->willReturn('en');

    $translate_job->expects($this->any())
      ->method('getTargetLangcode')
      ->willReturn('de');

    $translate_job->expects($this->any())
      ->method('get')
      ->willReturn($field_item_list);

    $translate_job->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn([]);

    $translate_job->expects($this->once())
      ->method('getItems')
      ->willReturn([
        1 => $job_item_mock_1,
        2 => $job_item_mock_2
      ]);

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitError');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->with($translate_job)
      ->willReturn(['translationRequestUid' => 'test', 'fileUri' => 'test_file_uri.xml']);

    $this->apiWrapperMock->expects($this->once())
      ->method('getApi')
      ->with('file')
      ->willReturn($this->fileApiMock);

    $this->fileApiMock->expects($this->once())
      ->method('downloadFile')
      ->with(
        'test_file_uri.xml',
        'de',
        $this->callback(function($subject) {
          $params = $subject->exportToArray();

          return $params['retrievalType'] == 'published';
        })
      )
      ->willReturn('xml');

    $this->pluginMock->expects($this->once())
      ->method('validateImport')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn(TRUE);

    $this->pluginMock->expects($this->once())
      ->method('import')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn([
        1 => [],
        2 => []
      ]);

    $this->translationRequestManagerMock->expects($this->once())
      ->method('commitSuccessfulDownload')
      ->with($translate_job)
      ->willReturn(TRUE);

    tmgmt_smartling_download_file($translate_job);
  }

  /**
   * Download success full flow: download by TMGMT Job and TMGMT Job Item.
   */
  public function testDownloadByTmgmtJobAndTmgmtJobItem() {
    $real_job = $this->createJobWithItems([
      'batch_uid' => 'uid',
      'batch_execute_on_job' => 1,
    ]);

    $field_item_list = $this->getMockBuilder(FieldItemList::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getValue'
      ])
      ->getMock();

    $field_item_list->expects($this->any())
      ->method('getValue')
      ->willReturn([
        0 => [
          'value' => NULL
        ]
      ]);

    $job_item_mock_1 = $this->getMockBuilder(JobItem::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'save',
        'id',
        'setState',
        'addTranslatedData',
        'getItemId',
        'getItemType'
      ])
      ->getMock();

    $job_item_mock_1->expects($this->any())
      ->method('id')
      ->willReturn(1);

    $job_item_mock_1->expects($this->any())
      ->method('getItemId')
      ->willReturn(1);

    $job_item_mock_1->expects($this->any())
      ->method('getItemType')
      ->willReturn('node');

    $job_item_mock_1->expects($this->never())
      ->method('setState')
      ->with(JobItemInterface::STATE_ACTIVE);

    $job_item_mock_1->expects($this->never())
      ->method('addTranslatedData');

    $job_item_mock_2 = $this->getMockBuilder(JobItem::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'id',
        'setState',
        'addTranslatedData',
        'getItemId',
        'getItemType'
      ])
      ->getMock();

    $job_item_mock_2->expects($this->any())
      ->method('id')
      ->willReturn(2);

    $job_item_mock_1->expects($this->any())
      ->method('getItemId')
      ->willReturn(1);

    $job_item_mock_1->expects($this->any())
      ->method('getItemType')
      ->willReturn('node');

    $job_item_mock_2->expects($this->once())
      ->method('setState')
      ->with(JobItemInterface::STATE_ACTIVE);

    $job_item_mock_2->expects($this->once())
      ->method('addTranslatedData');

    $translate_job = $this->getMockBuilder(Job::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'id',
        'getItems',
        'getTranslator',
        'getTranslatorPlugin',
        'getSetting',
        'getSourceLangcode',
        'getTargetLangcode',
        'save',
        'set',
        'get',
        'addMessage',
        'getFieldDefinitions'
      ])
      ->getMock();

    $translate_job->expects($this->any())
      ->method('id')
      ->willReturn(1);

    $translate_job->expects($this->any())
      ->method('getSetting')
      ->willReturn('public');

    $translate_job->expects($this->any())
      ->method('getTranslator')
      ->willReturn($real_job->getTranslator());

    $translate_job->expects($this->any())
      ->method('getTranslatorPlugin')
      ->willReturn($real_job->getTranslatorPlugin());

    $translate_job->expects($this->any())
      ->method('getSourceLangcode')
      ->willReturn('en');

    $translate_job->expects($this->any())
      ->method('getTargetLangcode')
      ->willReturn('de');

    $translate_job->expects($this->any())
      ->method('get')
      ->willReturn($field_item_list);

    $translate_job->expects($this->any())
      ->method('getFieldDefinitions')
      ->willReturn([]);

    $translate_job->expects($this->once())
      ->method('getItems')
      ->willReturn([
        1 => $job_item_mock_1,
        2 => $job_item_mock_2
      ]);

    $this->translationRequestManagerMock->expects($this->never())
      ->method('commitError');

    $this->translationRequestManagerMock->expects($this->once())
      ->method('getTranslationRequest')
      ->with($translate_job)
      ->willReturn(['translationRequestUid' => 'test', 'fileUri' => 'test_file_uri.xml']);

    $this->apiWrapperMock->expects($this->once())
      ->method('getApi')
      ->with('file')
      ->willReturn($this->fileApiMock);

    $this->fileApiMock->expects($this->once())
      ->method('downloadFile')
      ->with(
        'test_file_uri.xml',
        'de',
        $this->callback(function($subject) {
          $params = $subject->exportToArray();

          return $params['retrievalType'] == 'published';
        })
      )
      ->willReturn('xml');

    $this->pluginMock->expects($this->once())
      ->method('validateImport')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn(TRUE);

    $this->pluginMock->expects($this->once())
      ->method('import')
      ->with(
        'public://tmgmt_smartling_translations/test_file_uri.xml',
        $translate_job
      )
      ->willReturn([
        1 => [],
        2 => []
      ]);

    $this->translationRequestManagerMock->expects($this->once())
      ->method('commitSuccessfulDownload')
      ->with($translate_job)
      ->willReturn(TRUE);

    tmgmt_smartling_download_file($translate_job, $job_item_mock_2);
  }
}
