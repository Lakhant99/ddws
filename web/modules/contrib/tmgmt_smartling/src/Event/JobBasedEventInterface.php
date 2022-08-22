<?php

namespace Drupal\tmgmt_smartling\Event;

interface JobBasedEventInterface {

  /**
   * Returns the TMGMT job in question.
   *
   * @return \Drupal\tmgmt\JobInterface
   */
  public function getJob();

}
