<?php

/**
 * @file
 * Hooks provided by the TMGMT Extension Suite module.
 */

/**
 * Info about reopened jobs of updated entity.
 *
 * @param array $job_ids
 *   Array of job ids.
 * @param string $translator_id
 *   Passed jobs were submitted through this translator.
 */
function hook_tmgmt_extension_suit_updated_entity_jobs(array $job_ids, $translator_id) {
  // Array $job_ids contains reopened job ids.
}
