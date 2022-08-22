<?php

namespace Drupal\tmgmt_extension_suit\Plugin\QueueWorker;

use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt_extension_suit\ExtendedTranslatorPluginInterface;

/**
 * Executes interface translation queue tasks.
 *
 * @QueueWorker(
 *   id = "tmgmt_extension_suit_upload",
 *   title = @Translation("Job translation upload"),
 *   cron = {"time" = 30}
 * )
 */
class JobUpload extends QueueWorkerLockedBase {

  /**
   * {@inheritdoc}
   */
  protected function doProcessItem(array $data) {
    $id = $data['id'];

    try {
      $job = Job::load($id);

      if (empty($job)) {
        $this->logger->error(t('Requesting translation for a job :job_id is failed: non-existent job. This job has been deleted from admin UI but queue item is still in the queue.', [
          ':job_id' => $id,
        ])->render());

        return;
      }

      $plugin = $job->getTranslator()->getPlugin();

      if ($plugin instanceof ExtendedTranslatorPluginInterface) {
        $plugin->requestTranslationExtended($job, $data);
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
