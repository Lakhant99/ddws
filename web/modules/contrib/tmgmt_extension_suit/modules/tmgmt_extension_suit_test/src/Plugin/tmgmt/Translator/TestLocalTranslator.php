<?php

namespace Drupal\tmgmt_extension_suit_test\Plugin\tmgmt\Translator;

use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt_local\Plugin\tmgmt\Translator\LocalTranslator;
use Drupal\tmgmt_extension_suit\ExtendedTranslatorPluginInterface;

/**
 * Drupal user provider.
 *
 * @TranslatorPlugin(
 *   id = "tes_local_test",
 *   label = @Translation("TES local translator"),
 *   description = @Translation("Allows local users to process translation jobs."),
 *   ui = "\Drupal\tmgmt_local\LocalTranslatorUi",
 *   default_settings = {},
 *   map_remote_languages = FALSE
 * )
 */
class TestLocalTranslator extends LocalTranslator implements ExtendedTranslatorPluginInterface {

  /**
   * Checks whether job is ready for download or not.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   Job object.
   *
   * @return bool
   *   TRUE if ready FALSE otherwise.
   */
  public function isReadyForDownload(JobInterface $job) {}

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
  public function downloadTranslation(
    JobInterface $job,
    JobItemInterface $jobItem = NULL
  ) {}

  /**
   * Returns file name for a given job.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   Job object.
   *
   * @return string
   *   Job file name.
   */
  public function getFileName(JobInterface $job) {}

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
  public function cancelTranslation(JobInterface $job) {}

  /**
   * Requests translation.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   TMGMT Job.
   * @param array $data
   *   Data from queue item.
   */
  public function requestTranslationExtended(JobInterface $job, array $data) {}
}
