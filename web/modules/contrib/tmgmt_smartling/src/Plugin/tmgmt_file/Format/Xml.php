<?php

namespace Drupal\tmgmt_smartling\Plugin\tmgmt_file\Format;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt_file\Plugin\tmgmt_file\Format\Html;
use Drupal\tmgmt\Data;

/**
 * Export into XML.
 *
 * @FormatPlugin(
 *   id = "xml",
 *   label = @Translation("XML")
 * )
 */
class Xml extends Html {
  /**
   * @var ModuleHandlerInterface
   */
  private $moduleHandler;

  public function __construct() {
    $this->moduleHandler = \Drupal::moduleHandler();
  }

  /**
   * {@inheritdoc}.
   */
  public function export(JobInterface $job, $conditions = []) {
    // Export job items data without relation to their ids.
    $items = [];

    foreach ($job->getItems($conditions) as $item) {
      $data = \Drupal::service('tmgmt.data')->filterTranslatable($item->getData());

      foreach ($data as $key => $value) {
        // TODO: identical filename task.
        // $items[$item->id()][$this->encodeIdSafeBase64($item->getItemType() . ':' . $item->getItemId() . '][' . $key)] = $value;
        $items[$item->id()][$this->encodeIdSafeBase64($item->id() . '][' . $key)] = $value + [
          'sl-variant' => $item->getItemType() . '-' . $item->getItemId() . '-' . $key,
        ];
      }
    }

    $this->moduleHandler->alter('tmgmt_smartling_xml_file_export_data', $items);

    // Avoid rendering with "renderer" service in order to avoid theme debug
    // mode - if it's enabled we shouldn't print debug messages into XML file.
    // Use "twig" service instead.
    $variables = [
      'items' => $items,
    ];
    $theme_registry = theme_get_registry();
    $info = $theme_registry['tmgmt_smartling_xml_template'];
    $template_file = $info['template'] . '.html.twig';

    if (isset($info['path'])) {
      $template_file = $info['path'] . '/' . $template_file;
    }

    return $this->escapePluralStringDelimiter(
      \Drupal::service('twig')->loadTemplate($template_file)->render($variables)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateImport($imported_file, $job = TRUE) {
    $xml = simplexml_load_file($imported_file);

    if (!$xml) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function import($imported_file, $job = TRUE) {
    libxml_use_internal_errors(true);

    $dom = new \DOMDocument();
    $dom->loadHTMLFile($imported_file);
    $xml = simplexml_import_dom($dom);
    $data = [];

    $lock_fields_manager = \Drupal::getContainer()->get('tmgmt_smartling.lock_fields_manager');

    // Get job items data from xml.
    foreach ($xml->xpath("//div[@class='atom']|//span[@class='atom']") as $atom) {
      // Assets are our strings (eq fields in nodes).
      $key = $this->decodeIdSafeBase64((string) $atom['id']);
      $data[$key]['#text'] = (string) $atom;

      $sl_variant_data = $this->parseSmartlingSlVariantKey((string) $atom['sl-variant']);
      $lang_code = $job->getTargetLangcode();

      if ($sl_variant_data) {
        $locked_fields = $lock_fields_manager->getLockedFieldsByContentEntityData(
          $sl_variant_data['entity_type'],
          $sl_variant_data['entity_id'],
          $lang_code
        );

        // In case if field which is being processed is locked we don't read
        // its value from file but we do read its value from entity's translated
        // field instead.
        if (in_array($sl_variant_data['field_name'], $locked_fields)) {
          $data[$key]['#text'] = $lock_fields_manager->getLockedFieldValue(
            $sl_variant_data['entity_type'],
            $sl_variant_data['entity_id'],
            $lang_code,
            $sl_variant_data['field_name'],
            $sl_variant_data['field_index'],
            $sl_variant_data['field_value_name']
          );
        }
      }

      // If we have some markup in plain text fields we need to decode it.
      if ($atom->getName() == 'span') {
        $data[$key]['#text'] = html_entity_decode($data[$key]['#text']);
      }

      $data[$key]['#text'] = $this->unEscapePluralStringDelimiter($data[$key]['#text']);
    }

    $this->moduleHandler->alter('tmgmt_smartling_xml_file_import_data', $data);

    return \Drupal::service('tmgmt.data')->unflatten($data);

    // TODO: identical filename task.
    // Map job items from xml to job items from a given job.
    // $result = [];
    // $data = \Drupal::service('tmgmt.data')->unflatten($data);
    //
    // foreach ($data as $data_key => $data_item) {
    //   $conditions = explode(':', $data_key);
    //   $job_item = $job->getItems([
    //     'item_type' => $conditions[0],
    //     'item_id' => $conditions[1],
    //   ]);
    //   $job_item = reset($job_item);
    //
    //   if (!empty($job_item)) {
    //     $result[$job_item->id()] = $data_item;
    //   }
    // }
    //
    // return $result;
  }

  /**
   * Parses sl-variant string into array.
   *
   * @param string $sl_variant
   *  Contains Smartling sl-variant string like 'node-4-title][0][value'
   *
   * @return array
   *  Array with fields: 'entity_type', 'entity_id', 'field_name',
   *  'field_index' and 'field_value_name' parsed from sl-variant string
   */
  protected function parseSmartlingSlVariantKey($sl_variant) {
    $sl_variant = explode('-', $sl_variant);

    if (
      !isset($sl_variant[0]) ||
      !isset($sl_variant[1]) ||
      !isset($sl_variant[2])
    ) {
      return [];
    }

    $field_array = explode(Data::TMGMT_ARRAY_DELIMITER, $sl_variant[2]);

    if (
      !isset($field_array[0]) ||
      !isset($field_array[1]) ||
      !isset($field_array[2])
    ) {
      return [];
    }

    return [
      'entity_type' => $sl_variant[0],
      'entity_id' => $sl_variant[1],
      'field_name' => $field_array[0],
      'field_index' => $field_array[1],
      'field_value_name' => $field_array[2],
    ];
  }

  protected function escapePluralStringDelimiter($string) {
    return preg_replace("/\x03/", "!PLURAL_STRING_DELIMITER", $string);
  }

  protected function unEscapePluralStringDelimiter($string) {
    return preg_replace("/!PLURAL_STRING_DELIMITER/", "\x03", $string);
  }

  public function decodeIdSafeBase64($data) {
    return parent::decodeIdSafeBase64($data);
  }

  public function encodeIdSafeBase64($data) {
    return parent::encodeIdSafeBase64($data);
  }
}
