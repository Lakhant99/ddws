<?php

namespace Drupal\optimizedb\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Page hide notification.
 */
class HideNotificationController extends ControllerBase {

  /**
   * Page hide notification.
   *
   * @return string
   *   Result hide notification.
   */
  public function hide() {
    $time = \Drupal::time()->getRequestTime();
    $config = \Drupal::configFactory()->getEditable('optimizedb.settings');

    $notify_optimize = $config->get('notify_optimize');

    // There is a need to disable the notification?
    if ($notify_optimize) {
      $config
        ->set('notify_optimize', FALSE)
        // If the notification of the need to optimize hiding, so she runs.
        ->set('last_optimization', $time)
        ->save();

      $optimization_period = (int) $config->get('optimization_period');
      $time_next_optimization = strtotime('+ ' . $optimization_period . ' day', $time);

      $output = $this->t('The following message on the need to perform optimization, you get - @date.', [
        '@date' => \Drupal::service('date.formatter')
          ->format($time_next_optimization),
      ]);
    }
    else {
      $output = $this->t('Alerts are not available.');
    }

    return [
      '#type' => 'markup',
      '#markup' => $output,
    ];
  }

}
