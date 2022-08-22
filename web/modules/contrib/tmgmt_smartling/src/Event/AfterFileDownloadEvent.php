<?php

namespace Drupal\tmgmt_smartling\Event;

use Symfony\Component\EventDispatcher\Event;

class AfterFileDownloadEvent extends Event implements JobBasedEventInterface {
  use JobBasedEventTrait;

  const AFTER_FILE_DOWNLOAD_EVENT = 'tmgmt_smartling.after_file_download';
}
