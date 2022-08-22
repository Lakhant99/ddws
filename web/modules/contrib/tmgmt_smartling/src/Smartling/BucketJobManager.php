<?php

/**
 * @file
 * BucketJobManager.
 */

namespace Drupal\tmgmt_smartling\Smartling;

use Drupal;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\tmgmt\Entity\Translator;
use Drupal\tmgmt_extension_suit\Utils\UniqueQueueItem;
use Psr\Log\LoggerInterface;
use Smartling\Jobs\JobStatus;

/**
 * Class BucketJobManager.
 */
class BucketJobManager {

  /**
   * @var \Drupal\tmgmt_smartling\Smartling\SmartlingApiWrapper
   */
  private $apiWrapper;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * @var \Drupal\tmgmt_extension_suit\Utils\UniqueQueueItem
   */
  private $uniqueQueueItemUtil;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * @var array
   */
  private $settings = [];

  /**
   * BucketJobManager constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\tmgmt_smartling\Smartling\SmartlingApiWrapper $api_wrapper
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\tmgmt_extension_suit\Utils\UniqueQueueItem $unique_queue_item_util
   */
  public function __construct(
    LoggerInterface $logger,
    SmartlingApiWrapper $api_wrapper,
    ModuleHandlerInterface $module_handler,
    UniqueQueueItem $unique_queue_item_util
  ) {
    $this->logger = $logger;
    $this->apiWrapper = $api_wrapper;
    $this->moduleHandler = $module_handler;
    $this->uniqueQueueItemUtil = $unique_queue_item_util;
  }

  /**
   * @param string $suffix
   * @return string
   */
  private function getName($suffix = '') {
    $date = date('m/d/Y');
    $name = "Daily Bucket Job $date";
    $this->moduleHandler->alter('tmgmt_smartling_bucket_job_name', $name);

    return $name . $suffix;
  }

  /**
   * @param array $jobs
   * @param Translator $translator
   *
   * @return array
   */
  public function handle(array $jobs, Translator $translator) {
    $this->settings = $translator->getSettings();
    $this->apiWrapper->setSettings($this->settings);

    $job_uid = NULL;
    $job_name = $this->getName();
    $response = $this->apiWrapper->listJobs($job_name, [
      JobStatus::AWAITING_AUTHORIZATION,
      JobStatus::IN_PROGRESS,
      JobStatus::COMPLETED,
    ]);

    // Try to find the latest created bucket job.
    if (!empty($response['items'])) {
      $job_uid = $response['items'][0]['translationJobUid'];
    }

    // If there is no existing bucket job then create new one.
    if (empty($job_uid)) {
      $job_uid = $this->apiWrapper->createJob($job_name, (string) t('Bucket job: contains updated content.'));

      // If there is a CANCELED/CLOSED bucket job then we have to come with new
      // job name in order to avoid "Job name is already taken" error.
      if (empty($job_uid)) {
        $job_name = $this->getName(' ' . date('H:i:s'));
        $job_uid = $this->apiWrapper->createJob($job_name, (string) t('Bucket job: contains updated content.'));
      }
    }

    if (empty($job_uid)) {
      $this->logger->error(t("Queueing file upload into the bucket job failed: can't find/create job.")->render());

      return [
        'batch_uid' => FALSE,
        'batch_execute_on_job' => FALSE,
      ];
    }

    $batch_uid = $this->apiWrapper->createBatch($job_uid, $this->settings['auto_authorize_locales']);

    if (empty($batch_uid)) {
      $this->logger->error(t("Queueing file upload into the bucket job failed: can't create batch.")->render());

      return [
        'batch_uid' => FALSE,
        'batch_execute_on_job' => FALSE,
      ];
    }

    $jobs_in_batch = [];
    $last_job = end($jobs);

    foreach ($jobs as $log_job) {
      $jobs_in_batch[] = $log_job->id();
    }

    Drupal::getContainer()
      ->get('logger.channel.smartling')
      ->info(t('Batch info (track entity changes): uid = "@batch_uid", jobs count = "@jobs_count", jobs = "@jobs_in_batch", execute on job = "@batch_execute_on_job"', [
        '@batch_uid' => $batch_uid,
        '@jobs_count' => count($jobs_in_batch),
        '@jobs_in_batch' => implode(', ', $jobs_in_batch),
        '@batch_execute_on_job' => $last_job->id(),
      ])->render());

    return [
      'batch_uid' => $batch_uid,
      'batch_execute_on_job' => $last_job->id(),
    ];
  }

}
