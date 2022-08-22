<?php

namespace Drupal\tmgmt_smartling\Event;

use Drupal\tmgmt\JobInterface;

trait JobBasedEventTrait {

  /**
   * New TMGMT Job.
   *
   * @var \Drupal\tmgmt\JobInterface
   */
  protected $job;

  /**
   * Constructs an AfterFileDownloadEvent object.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   The translation job for the file that was downloaded.
   */
  public function __construct(JobInterface $job) {
    $this->job = $job;
  }

  /**
   * @inheritdoc
   */
  public function getJob() {
    return $this->job;
  }

}
