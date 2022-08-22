<?php

namespace Drupal\tmgmt_smartling\Smartling\LockFields;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class LockFieldsFormManager
 *
 * @package Drupal\tmgmt_smartling\Smartling\LockFields
 */
class LockFieldsFormManager {
  private $lockFieldsManager;
  private $languageManager;

  /**
   * LockFieldsFormManager constructor.
   *
   * @param LockFieldsManager $lock_fields_manager
   * @param LanguageManagerInterface $language_manager
   */
  public function __construct(LockFieldsManager $lock_fields_manager, LanguageManagerInterface $language_manager) {
    $this->lockFieldsManager = $lock_fields_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * Extends given form array with 'Lock fields' tab.
   *
   * @param $form
   * @param FormStateInterface $form_state
   *
   * @return array
   */
  public function addLockFieldsListToForm(array &$form, FormStateInterface $form_state) {
    $entity = $form_state->getFormObject()->getEntity();
    $entity_lang_code = $entity->get('default_langcode')->getLangcode();
    $default_site_lang_code = $this->languageManager->getDefaultLanguage()->getId();
    $translatable_fields = $this->lockFieldsManager->getTranslatableFieldsByContentEntity($entity);

    if ($entity_lang_code != $default_site_lang_code) {
      $form['smartling'] = [
        '#type' => 'details',
        '#title' => new TranslatableMarkup('Smartling management'),
        '#group' => 'advanced',
        '#attached' => [
          'drupalSettings' => [
            'smartling' => [
              'checkAllId' => [
                'edit-locked-fields',
              ],
            ],
          ],
          'library' => [
            'tmgmt_smartling/checkAll'
          ]
        ]
      ];

      $form['smartling']['locked_fields'] = [
        '#title' => new TranslatableMarkup('Lock fields'),
        '#type' => 'checkboxes',
        '#description' => t('Selected fields will not be overwritten.'),
        '#options' => $translatable_fields,
        '#default_value' => $this->lockFieldsManager->getLockedFieldsByContentEntity($entity)
      ];

      $form['smartling']['locked_fields']['check_all'] = [
        '#weight' => 1,
        '#markup' => '<div class="check-control"><a href="#" class="check-all">' . (new TranslatableMarkup('Check all / Uncheck All')) . '</a></div>',
      ];

      $form['actions']['submit']['#submit'][] = [
        LockFieldsFormManager::class,
        'submitLockFieldsList'
      ];
    }
  }

  /**
   * Saves locked fields list using States API
   *
   * We don't use TMGMT Job Item
   * for storing data because there are will be situations when one entity
   * sits in many TMGMT Job Items and it will not be clear which item to choose
   * for getting/setting locked fields list.
   *
   * This callback is intentionally static and passed to form as
   *
   * @code
   * $form['actions']['submit']['#submit'][] = [
   *   LockFieldsFormManager::class,
   *   'submitLockFieldsList'
   * ];
   * @endcode
   *
   * to avoid the serialization issue because $this contains implicit relation
   * to \Drupal\Core\Database\Connection object which is not serializable and
   * throws an exception from __sleep method.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public static function submitLockFieldsList(array &$form, FormStateInterface $form_state) {
    \Drupal::getContainer()
      ->get('tmgmt_smartling.lock_fields_manager')->setLockedFields(
        $form_state->getFormObject()->getEntity(),
        $form_state->getValue('locked_fields')
      );
  }
}
