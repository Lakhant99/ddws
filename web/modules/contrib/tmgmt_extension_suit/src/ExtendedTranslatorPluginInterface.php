<?php

namespace Drupal\tmgmt_extension_suit;

use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\TranslatorPluginInterface;

/**
 * Interface for service plugin controllers.
 */
interface ExtendedTranslatorPluginInterface extends TranslatorPluginInterface {

  /**
   * Checks whether job is ready for download or not.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   Job object.
   *
   * @return bool
   *   TRUE if ready FALSE otherwise.
   */
  public function isReadyForDownload(JobInterface $job);

  /**
   * Downloads translation.
   *
   * Applies translation for all the job items inside the job if $jobitem is
   * NULL. Applies translation to only one job item if $jobItem is passed.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   Job object.
   * @param JobItemInterface $jobItem
   *
   * @return bool
   *   TRUE if download process completed successfully
   *   FALSE otherwise.
   */
  public function downloadTranslation(JobInterface $job, JobItemInterface $jobItem = NULL);

  /**
   * Returns file name for a given job.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   Job object.
   *
   * @return string
   *   Job file name.
   */
  public function getFileName(JobInterface $job);

  /**
   * Cancels translation.
   *
   * This method cancels not Drupal translation but translation in
   * 3rd party service instead.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   Job object.
   *
   * @return bool
   *   TRUE if canceled FALSE otherwise.
   */
  public function cancelTranslation(JobInterface $job);

  /**
   * Requests translation.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   TMGMT Job.
   * @param array $data
   *   Data from queue item.
   */
  public function requestTranslationExtended(JobInterface $job, array $data);

}
