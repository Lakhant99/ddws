<?php

namespace Drupal\tmgmt_smartling\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt_extension_suit\ExtendedTranslatorPluginInterface;
use Drupal\tmgmt_extension_suit\Form\BaseTmgmtActionApproveForm;

/**
 * Provides a confirmation form for sending multiple content entities.
 */
class DownloadByJobItemsTmgmtActionApproveForm extends BaseTmgmtActionApproveForm {

  /**
   * Temp storage name we are saving entity_ids to.
   *
   * @var string
   */
  protected $tempStorageName = 'tmgmt_smartling_tmgmt_job_operations_download_by_job_items';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tmgmt_smartling_download_by_job_items_form_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Download Translation (by job items)');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to download translations for these jobs?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Downloading can take some time, do not close the browser');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_user_id = $this->currentUser()->id();
    $temp_storage_name = $this->getTempStorageName();

    // Clear out the accounts from the temp store.
    $this->tempStoreFactory->get($temp_storage_name)->delete($current_user_id);

    if (!$form_state->getValue('confirm')) {
      return;
    }

    $operations = [];

    foreach ($this->entityIds as $id => $entity_type) {
      $job = Job::load($id);

      if (empty($job)) {
        continue;
      }

      foreach ($job->getItems() as $item) {
        $batch_item_data = [
          'tjid' => $job->id(),
          'tjiid' => $item->id()
        ];

        $operations[] = [
          [get_class($this), 'processBatch'],
          [$batch_item_data],
        ];
      }
    }

    if (!empty($operations)) {
      $batch = [
        'title' => $this->getConfirmText(),
        'operations' => $operations,
        'finished' => [get_class($this), 'finishBatch'],
      ];

      batch_set($batch);
    }
    else {
      $form_state->setRedirect('system.admin_content');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function processBatch(array $data, array &$context) {
    if (!isset($context['results']['errors'])) {
      $context['results']['errors'] = [];
      $context['results']['count'] = 0;
    }

    $job = Job::load($data["tjid"]);
    $job_item = JobItem::load($data["tjiid"]);

    if (!$job) {
      $context['results']['errors'][] = t('TMGMT Job @id not found', [
        '@id' => $data["tjid"]
      ]);
    }

    if (!$job_item) {
      $context['results']['errors'][] = t('TMGMT Job Item @id not found', [
        '@id' => $data["tjiid"]
      ]);
    }

    if ($translator = $job->getTranslator()) {
      $plugin = $translator->getPlugin();

      if (
        $plugin instanceof ExtendedTranslatorPluginInterface &&
        $plugin->downloadTranslation($job, $job_item)
      ) {
        $context['results']['count']++;
      }
      else {
        $context['results']['errors'][] = new FormattableMarkup('Error downloading %name', [
          '%name' => $job->label(),
        ]);

        return;
      }

      $context['message'] = new FormattableMarkup('Processed %name.', [
        '%name' => $job->label(),
      ]);
    }
    else {
      $context['message'] = new FormattableMarkup('Skipped %name.', [
        '%name' => $job->label(),
      ]);
    }
  }

}
