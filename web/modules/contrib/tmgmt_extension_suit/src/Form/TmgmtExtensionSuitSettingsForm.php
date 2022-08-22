<?php

namespace Drupal\tmgmt_extension_suit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\Entity\Translator;
use Drupal\tmgmt_extension_suit\ExtendedTranslatorPluginInterface;

/**
 * TMGMT Extension Suit settings form.
 */
class TmgmtExtensionSuitSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tmgmt_extension_suit.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tmgmt_extension_suit_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('tmgmt_extension_suit.settings');
    $do_track_changes_by_provider_and_locales = \Drupal::state()->get('tmgmt_extension_suit.settings.do_track_changes_by_provider_and_locales');

    $form['do_track_changes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Track changes of the translatable entities.'),
      '#description' => 'If checked, all the entities that once were submitted for translation, would be re-sent automatically.',
      '#default_value' => $config->get('do_track_changes'),
      '#required' => FALSE,
    ];

    $languages = \Drupal::languageManager()->getLanguages();
    $default_language = \Drupal::languageManager()->getDefaultLanguage();

    $translators = [];

    foreach ($this->configFactory->listAll('tmgmt.translator') as $id) {
      $config = $this->configFactory->get($id);
      $config_data = $config->getRawData();
      $translator = Translator::load($config_data['name']);

      if (empty($translator)) {
        continue;
      }

      $translator_plugin = $translator->getPlugin();

      if (!$translator_plugin instanceof ExtendedTranslatorPluginInterface) {
        continue;
      }

      $translators[] = $translator;

      $form[$translator->id() . '_target_languages'] = [
        '#type' => 'details',
        '#title' => $translator->label(),
        '#open' => TRUE,
        '#states' => [
          'visible' => [
            ':input[name="do_track_changes"]' => ['checked' => TRUE],
          ],
        ]
      ];

      foreach ($languages as $language) {
        if ($language->getId() === $default_language->getId()) {
          continue;
        }

        $form[$translator->id() . '_target_languages'][$translator->id() . '_' . $language->getId()] = [
          '#type' => 'checkbox',
          '#title' => $language->getName(),
          '#required' => FALSE,
          '#default_value' => !empty($do_track_changes_by_provider_and_locales[$translator->id() . '_' . $language->getId()])
        ];
      }
    }

    if (empty($translators)) {
      $form['warning'] = [
        '#type' => 'markup',
        '#prefix' => $this->t('Providers which support track changes are not found'),
        '#states' => [
          'visible' => [
            ':input[name="do_track_changes"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('tmgmt_extension_suit.settings');
    $config
      ->set('do_track_changes', $form_state->getValue('do_track_changes'));

    \Drupal::state()->set('tmgmt_extension_suit.settings.do_track_changes_by_provider_and_locales', $form_state->getValues());

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
