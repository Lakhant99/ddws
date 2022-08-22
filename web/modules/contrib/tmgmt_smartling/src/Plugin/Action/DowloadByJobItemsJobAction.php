<?php

namespace Drupal\tmgmt_smartling\Plugin\Action;

use Drupal\tmgmt_extension_suit\Plugin\Action\BaseJobAction;

/**
 * Translate entity.
 *
 * @Action(
 *   id = "tmgmt_smartling_download_by_job_items_job_action",
 *   label = @Translation("Download Translation (by job items)"),
 *   type = "tmgmt_job",
 *   confirm_form_route_name = "tmgmt_smartling.download_by_job_items_approve_action"
 * )
 */
class DowloadByJobItemsJobAction extends BaseJobAction {

  /**
   * Returns temp storage name.
   *
   * @inheritdoc
   */
  protected function getTempStoreName($entity_type = '') {
    return 'tmgmt_smartling_' . $entity_type . '_operations_download_by_job_items';
  }

}
