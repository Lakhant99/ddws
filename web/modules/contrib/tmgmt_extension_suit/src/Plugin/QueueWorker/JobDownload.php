<?php

namespace Drupal\tmgmt_extension_suit\Plugin\QueueWorker;

use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt_extension_suit\ExtendedTranslatorPluginInterface;

/**
 * Executes interface translation queue tasks.
 *
 * @QueueWorker(
 *   id = "tmgmt_extension_suit_download",
 *   title = @Translation("Job translation download"),
 *   cron = {"time" = 30}
 * )
 */
class JobDownload extends QueueWorkerLockedBase {

  /**
   * {@inheritdoc}
   */
  protected function doProcessItem(array $data) {
    $tjid = isset($data['tjid']) ? $data['tjid'] : $data['id'];
    $tjiid = isset($data['tjiid']) ? $data['tjiid'] : NULL;

    try {
      $job = Job::load($tjid);
      $jobItem = NULL;

      if (empty($job)) {
        $this->logger->error(t('Downloading translation for a job :job_id is failed: non-existent job. This job has been deleted from admin UI but queue item is still in the queue.', [
          ':job_id' => $tjid,
        ])->render());
        return;
      }

      if (!empty($tjiid)) {
        $jobItem = Jobitem::load($tjiid);

        if (empty($jobItem)) {
          $this->logger->error(t('Downloading translation for a job :job_id and jobItem :job_item_id is failed: non-existent job item. This job item has been deleted from admin UI but queue item is still in the queue.',
            [
              ':job_item_id' => $tjiid,
            ])->render());
          return;
        }
      }

      $plugin = $job->getTranslator()->getPlugin();

      if ($plugin instanceof ExtendedTranslatorPluginInterface &&
        $plugin->isReadyForDownload($job)
      ) {
        $plugin->downloadTranslation($job, $jobItem);
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
