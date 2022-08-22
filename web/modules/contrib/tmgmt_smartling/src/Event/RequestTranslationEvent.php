<?php

namespace Drupal\tmgmt_smartling\Event;

use Symfony\Component\EventDispatcher\Event;

class RequestTranslationEvent extends Event implements JobBasedEventInterface {
  use JobBasedEventTrait;

  const REQUEST_TRANSLATION_EVENT = 'tmgmt_smartling.request_translation';
}
