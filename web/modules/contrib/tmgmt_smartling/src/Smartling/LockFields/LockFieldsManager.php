<?php

namespace Drupal\tmgmt_smartling\Smartling\LockFields;

use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\State\StateInterface;

/**
 * Class LockFieldsManager
 *
 * @package Drupal\tmgmt_smartling\Smartling\LockFields
 */
class LockFieldsManager {
  protected $state;
  protected $entityTypeManager;
  protected $entityRepository;

  /**
   * LockFieldsManager constructor.
   *
   * @param StateInterface $state
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param EntityRepositoryInterface $entity_repository
   */
  public function __construct(
    StateInterface $state,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->state = $state;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Returns list of translatable fields for a given entity.
   *
   * @param ContentEntityInterface $entity
   *
   * @return array
   */
  public function getTranslatableFieldsByContentEntity(ContentEntityInterface $entity) {
    $field_definitions = $entity->getFieldDefinitions();
    $exclude_field_types = ['language'];
    $exclude_field_names = [
      'moderation_state',
      'default_langcode',
      'revision_translation_affected',
      'content_translation_outdated',
      'content_translation_uid',
      'content_translation_created',
    ];

    return array_map(
      function(FieldDefinitionInterface $field_definition) {
        return $field_definition->getLabel();
      },
      array_filter($field_definitions, function (FieldDefinitionInterface $field_definition) use ($exclude_field_types, $exclude_field_names) {
        // Field is not translatable.
        if (!$field_definition->isTranslatable()) {
          return FALSE;
        }

        // Field type matches field types to exclude.
        if (in_array($field_definition->getType(), $exclude_field_types)) {
          return FALSE;
        }

        // Field name matches field names to exclude.
        if (in_array($field_definition->getName(), $exclude_field_names)) {
          return FALSE;
        }

        // User marked the field to be excluded.
        if ($field_definition instanceof ThirdPartySettingsInterface) {
          $is_excluded = $field_definition->getThirdPartySetting('tmgmt_content', 'excluded', FALSE);
          if ($is_excluded) {
            return FALSE;
          }
        }

        return TRUE;
      })
    );
  }

  /**
   * Returns list of locked fields for a given entity.
   *
   * @param EntityInterface $entity
   * @param array $default
   *
   * @return array
   */
  public function getLockedFieldsByContentEntity(EntityInterface $entity, array $default = []) {
    return array_filter($this->state->get(
      $this->getLockedFieldsStateKeyByContentEntity($entity),
      $default
    ));
  }

  /**
   * Returns list of locked fields for an entity by its data.
   *
   * @param $entity_type
   * @param $entity_id
   * @param $lang_code
   * @param array $default
   *
   * @return array
   */
  public function getLockedFieldsByContentEntityData($entity_type, $entity_id, $lang_code, array $default = []) {
    try {
      $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
    }
    catch (\Exception $e) {
      return [];
    }

    if (empty($entity)) {
      return [];
    }

    $translation = $this->entityRepository->getTranslationFromContext($entity, $lang_code);

    return $this->getLockedFieldsByContentEntity($translation, $default);
  }

  public function getLockedFieldValue($entity_type, $entity_id, $lang_code, $field_name, $field_index, $field_value_name) {
    $entity = $this->entityTypeManager
      ->getStorage($entity_type)
      ->load($entity_id);

    $translation = $this->entityRepository
      ->getTranslationFromContext($entity, $lang_code);

    $locked_value = $translation
      ->get($field_name)
      ->getValue();

    return $locked_value[$field_index][$field_value_name];
  }

  /**
   * Saves list of locked fields for a given entity.
   *
   * @param ContentEntityInterface $entity
   * @param array $locked_fields
   *
   * @return mixed
   */
  public function setLockedFields(ContentEntityInterface $entity, array $locked_fields) {
    return $this->state->set(
      $this->getLockedFieldsStateKeyByContentEntity($entity),
      $locked_fields
    );
  }

  /**
   * Generates State key for a given entity.
   *
   * @param EntityInterface $entity
   *
   * @return string
   */
  protected function getLockedFieldsStateKeyByContentEntity(EntityInterface $entity) {
    $lang_code = $entity->get('default_langcode')->getLangcode();
    $entity_type = $entity->getEntityTypeId();
    $entity_id = $entity->id();

    return "tmgmt_smartling.lock_fields.$lang_code.$entity_type.$entity_id";
  }
}
