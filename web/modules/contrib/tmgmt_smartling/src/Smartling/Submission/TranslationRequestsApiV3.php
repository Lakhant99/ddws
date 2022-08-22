<?php

namespace Drupal\tmgmt_smartling\Smartling\Submission;

use Smartling\TranslationRequests\TranslationRequestsApi;

/**
 * Class TranslationRequestsApiV3
 * @package Smartling\TranslationRequests
 */
class TranslationRequestsApiV3 extends TranslationRequestsApi
{
    const ENDPOINT_URL = 'https://api.smartling.com/submission-service-api/v3/projects';
}
