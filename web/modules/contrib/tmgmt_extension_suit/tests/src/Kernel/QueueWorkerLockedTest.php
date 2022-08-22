<?php

namespace Drupal\Tests\tmgmt_extension_suit\Kernel;

use Drupal\Core\Lock\PersistentDatabaseLockBackend;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Queue\RequeueException;
use Drupal\KernelTests\KernelTestBase;
use Drupal\tmgmt_extension_suit\Plugin\QueueWorker\JobDownload;
use Drupal\tmgmt_extension_suit\Plugin\QueueWorker\JobUpload;
use Exception;

/**
 * Tests locked queues.
 *
 * @group tmgmt_extension_suit
 */
class QueueWorkerLockedTest extends KernelTestBase {

  /**
   * Logger channel mock.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $loggerMock;

  /**
   * Lock service mock.
   *
   * @var \Drupal\Core\Lock\PersistentDatabaseLockBackend
   */
  protected $lockMock;

  /**
   * Upload queue worker.
   *
   * @var \Drupal\tmgmt_extension_suit\Plugin\QueueWorker\JobUpload
   */
  protected $uploadQueueWorker;

  /**
   * Download queue worker.
   *
   * @var \Drupal\tmgmt_extension_suit\Plugin\QueueWorker\JobDownload
   */
  protected $downloadQueueWorker;

  /**
   * Tests set up.
   */
  public function setUp() {
    parent::setUp();

    $this->loggerMock = $this->getMockBuilder(LoggerChannel::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $this->lockMock = $this->getMockBuilder(PersistentDatabaseLockBackend::class)
      ->setMethods([
        'acquire',
        'release',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $this->uploadQueueWorker = $this->getMockBuilder(JobUpload::class)
      ->setMethods([
        'doProcessItem',
      ])
      ->setConstructorArgs([
        [],
        "test_upload_queue_worker_id", [
          "cron" => [
            "time" => 27,
          ],
        ],
        $this->lockMock,
        $this->loggerMock,
      ])
      ->getMock();

    $this->downloadQueueWorker = $this->getMockBuilder(JobDownload::class)
      ->setMethods([
        'doProcessItem',
      ])
      ->setConstructorArgs([
        [],
        "test_download_queue_worker_id", [
          "cron" => [
            "time" => 29,
          ],
        ],
        $this->lockMock,
        $this->loggerMock,
      ])
      ->getMock();
  }

  /**
   * Upload queue worker: lock acquired and queue item is processed.
   */
  public function testUploadLockedQueueAcquireLockAndProcess() {
    $lockId = get_class($this->uploadQueueWorker) . ':processItem';

    $this->uploadQueueWorker
      ->expects($this->once())
      ->method('doProcessItem')
      ->with(["foo" => "bar"]);

    $this->lockMock
      ->expects($this->once())
      ->method('acquire')
      ->with($lockId, 27)
      ->willReturn(TRUE);

    $this->lockMock
      ->expects($this->once())
      ->method('release');

    $this->uploadQueueWorker->processItem(["foo" => "bar"]);
  }

  /**
   * Upload queue worker: lock acquired and item is processed with exception.
   */
  public function testUploadLockedQueueAcquireLockAndProcessWithException() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Failed to process item");

    $lockId = get_class($this->uploadQueueWorker) . ':processItem';

    $this->uploadQueueWorker
      ->expects($this->once())
      ->method('doProcessItem')
      ->with(["foo" => "bar"])
      ->willThrowException(new Exception("Failed to process item"));

    $this->lockMock
      ->expects($this->once())
      ->method('acquire')
      ->with($lockId, 27)
      ->willReturn(TRUE);

    $this->lockMock
      ->expects($this->once())
      ->method('release');

    $this->uploadQueueWorker->processItem(["foo" => "bar"]);
  }

  /**
   * Upload queue worker: do not process queue if lock is already acquired.
   */
  public function testUploadLockedDoNotProcessQueueIfLockIsAlreadyAcquired() {
    $lockId = get_class($this->uploadQueueWorker) . ':processItem';

    $this->uploadQueueWorker
      ->expects($this->never())
      ->method('doProcessItem');

    $this->lockMock
      ->expects($this->once())
      ->method('acquire')
      ->with($lockId, 27)
      ->willReturn(FALSE);

    $this->lockMock
      ->expects($this->never())
      ->method('release');

    try {
      $this->uploadQueueWorker->processItem(["foo" => "bar"]);
    }
    catch (RequeueException $e) {
      $this->assertEquals("Attempting to re-acquire $lockId.", $e->getMessage());
    }
  }

  /**
   * Download queue worker: lock acquired and queue item is processed.
   */
  public function testDownloadLockedQueueAcquireLockAndProcess() {
    $lockId = get_class($this->downloadQueueWorker) . ':processItem';

    $this->downloadQueueWorker
      ->expects($this->once())
      ->method('doProcessItem')
      ->with(["foo" => "bar"]);

    $this->lockMock
      ->expects($this->once())
      ->method('acquire')
      ->with($lockId, 29)
      ->willReturn(TRUE);

    $this->lockMock
      ->expects($this->once())
      ->method('release');

    $this->downloadQueueWorker->processItem(["foo" => "bar"]);
  }

  /**
   * Download queue worker: lock acquired and item is processed with exception.
   */
  public function testDownloadLockedQueueAcquireLockAndProcessWithException() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Failed to process item");

    $lockId = get_class($this->downloadQueueWorker) . ':processItem';

    $this->downloadQueueWorker
      ->expects($this->once())
      ->method('doProcessItem')
      ->with(["foo" => "bar"])
      ->willThrowException(new Exception("Failed to process item"));

    $this->lockMock
      ->expects($this->once())
      ->method('acquire')
      ->with($lockId, 29)
      ->willReturn(TRUE);

    $this->lockMock
      ->expects($this->once())
      ->method('release');

    $this->downloadQueueWorker->processItem(["foo" => "bar"]);
  }

  /**
   * Download queue worker: do not process queue if lock is already acquired.
   */
  public function testDownloadLockedDoNotProcessQueueIfLockIsAlreadyAcquired() {
    $lockId = get_class($this->downloadQueueWorker) . ':processItem';

    $this->downloadQueueWorker
      ->expects($this->never())
      ->method('doProcessItem');

    $this->lockMock
      ->expects($this->once())
      ->method('acquire')
      ->with($lockId, 29)
      ->willReturn(FALSE);

    $this->lockMock
      ->expects($this->never())
      ->method('release');

    try {
      $this->downloadQueueWorker->processItem(["foo" => "bar"]);
    }
    catch (RequeueException $e) {
      $this->assertEquals("Attempting to re-acquire $lockId.", $e->getMessage());
    }
  }

}
