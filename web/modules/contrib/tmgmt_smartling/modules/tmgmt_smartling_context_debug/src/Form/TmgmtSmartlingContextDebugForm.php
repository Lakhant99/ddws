<?php

namespace Drupal\tmgmt_smartling_context_debug\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form that helps to debug Smartling Context.
 */
class TmgmtSmartlingContextDebugForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tmgmt_smartling_context_debug_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $configs = [];

    $smartling_provider_configs = \Drupal::getContainer()
      ->get('tmgmt_smartling.smartling_config_manager')
      ->getAvailableConfigs();

    foreach ($smartling_provider_configs as $smartling_provider_config) {
      $configs[$smartling_provider_config->getName()] = $smartling_provider_config->get('label');
    }

    if (empty($configs)) {
      return [
        '#markup' => $this->t('No Smartling configs found. Please create Smartling config first')
      ];
    }

    $form['smartling_config'] = [
      '#type' => 'select',
      '#options' => $configs,
      '#title' => $this->t('Smartling configs'),
      '#default_value' => key($configs),
      '#description' => $this->t('Context related options will be read from specified config.'),
    ];

    $form['do_direct_output'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the context snapshot of the page in a browser.'),
      '#description' => 'If checked, the context will be shown on this page instead of being sent to Smartling.',
      '#default_value' => FALSE,
      '#required' => FALSE,
    ];

    $form['filename'] = [
      '#type' => 'textfield',
      '#title' => t('FileName'),
      '#description' => t('FileName of a Job'),
      '#default_value' => '',
      '#size' => 25,
      '#maxlength' => 25,
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => t('URL'),
      '#description' => t('URL of the page to extract the context for'),
      '#default_value' => '',
      '#size' => 25,
      '#maxlength' => 125,
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Test context'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = \Drupal::config(
      $form_state->getValue('smartling_config')
    )->get('settings');

    $filename = $form_state->getValue('filename');
    $url = $form_state->getValue('url');
    $current_user_name = \Drupal::currentUser()->getAccountName();

    /** @var \Drupal\tmgmt_smartling\Context\ContextUploader $context_uploader */
    $context_uploader = \Drupal::getContainer()->get('tmgmt_smartling.utils.context.uploader');

    if ($form_state->getValue('do_direct_output')) {
      try {
        $html = $context_uploader->getContextualizedPage($url, $settings, TRUE);
      } catch(\Exception $e) {
        $html = '';
      }

      \Drupal::getContainer()->get('tmgmt_smartling.utils.context.user_auth')->switchUser($current_user_name, TRUE);
      die($html);
    } elseif ($context_uploader->isReadyAcceptContext($filename, $settings)) {
      $response = $context_uploader->upload($url, $filename, $settings);

      \Drupal::getContainer()->get('tmgmt_smartling.utils.context.user_auth')->switchUser($current_user_name, TRUE);

      $message = print_r($response, TRUE);
      \Drupal::logger('tmgmt_smartling_context_debug')->info($message);
      $this->messenger()->addStatus('Smartling response: ' . $message);
    }
  }
}
