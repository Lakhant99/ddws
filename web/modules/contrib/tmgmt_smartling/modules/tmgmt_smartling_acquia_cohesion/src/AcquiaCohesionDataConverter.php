<?php

namespace Drupal\tmgmt_smartling_acquia_cohesion;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt_smartling\Plugin\tmgmt_file\Format\Xml;
use Drupal\tmgmt\Data;
use Psr\Log\LoggerInterface;

/**
 * Class AcquiaCohesionDataConverter
 * @package Drupal\tmgmt_smartling_acquia_cohesion
 */
class AcquiaCohesionDataConverter implements LayoutCanvasAwareInterface {
  use LayoutCanvasAwareTrait;

  const TMGMT_SMARTLING_ACQUIA_COHESION_KEY_DELIMITER = '-acquia_cohesion_field:';

  /**
   * @var Xml
   */
  protected $xmlPlugin;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * AcquiaCohesionDataConverter constructor.
   * @param LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger) {
    $this->xmlPlugin = new Xml();
    $this->logger = $logger;
  }

  /**
   * Converts Cohesion's JSON components to TMGMT Smartling's XML data items.
   *
   * @param array $data
   * @return array
   */
  public function findAndConvertCohesionJsonDataToCohesionXmlData(array $data) {
    $newData = [];

    foreach ($data as $jobItemId => $jobItemData) {
      foreach ($jobItemData as $jobItemDataEncodedFieldKey => $jobItemDataFieldValue) {
        if ($layoutCanvas = $this->isLayoutCanvas($jobItemDataFieldValue["#text"])) {
          $xmlDataItems = [];
          $jobItemDataDecodedFieldKey = $this->xmlPlugin->decodeIdSafeBase64($jobItemDataEncodedFieldKey);

          foreach ($layoutCanvas->iterateModels() as $model) {
            $modelUuid = $model->getUUID();

            foreach ($model->getValues() as $modelPropertyUid => $modelPropertyValue) {
              // Skip component title.
              if ($modelPropertyUid == 'settings') {
                continue;
              }

              $suffix = self::TMGMT_SMARTLING_ACQUIA_COHESION_KEY_DELIMITER . 'model:' . $modelUuid . ':property:' . $modelPropertyUid;
              $encodedKey = $this->xmlPlugin->encodeIdSafeBase64($jobItemDataDecodedFieldKey . $suffix);
              $slVariant = $jobItemDataFieldValue['sl-variant'] . $suffix;

              if (!$this->isTranslatableProperty($modelPropertyValue)) {
                continue;
              }

              // HTML text.
              if ($this->isCohesionPropertyHtmlText($modelPropertyValue)) {
                $this->logger->info(
                  'Exporting Acquia Cohesion rich text field: model=@modelUid property=@propertyUid',
                  ['@modelUid' => $modelUuid, '@propertyUid' => $modelPropertyUid]
                );

                $xmlDataItems[$encodedKey] = [
                  '#text' => $modelPropertyValue->text,
                  '#format' => $modelPropertyValue->textFormat,
                  'sl-variant' => $slVariant
                ];
              } else {
                $this->logger->info(
                  'Exporting Acquia Cohesion plain text field: model=@modelUid property=@propertyUid',
                  ['@modelUid' => $modelUuid, '@propertyUid' => $modelPropertyUid]
                );

                // Plain text.
                $xmlDataItems[$encodedKey] = [
                  '#text' => $modelPropertyValue,
                  'sl-variant' => $slVariant
                ];
              }
            }
          }

          $newData[$jobItemId] += $xmlDataItems;
        } else {
          $newData[$jobItemId][$jobItemDataEncodedFieldKey] = $jobItemDataFieldValue;
        }
      }
    }

    return $newData;
  }

  /**
   * Converts TMGMT Smartling's XML data items to Cohesion's JSON components.
   *
   * @param array $data
   * @return array
   */
  public function findAndConvertCohesionXmlDataToCohesionJsonData(array $data) {
    $newData = [];
    $cohesionData = [];

    // Grab Cohesion's fields from XML data items.
    foreach ($data as $dataKey => $dataValue) {
      if ($this->isCohesionDataKey($dataKey)) {
        $tmgmtKey = $this->getTmgmtKeyFromDataKey($dataKey);
        $cohesionKey = $this->getCohesionKeyFromDataKey($dataKey);

        if (isset($tmgmtKey) && isset($cohesionKey)) {
          $cohesionData[$tmgmtKey][$cohesionKey] = $dataValue;
        } else {
          $this->logger->warning(
            'Got invalid dataKey="@dataKey" from xml file: tmgmtKey="@tmgmtKey" or/and cohesionKey="@cohesionKey" is/are invalid. Skipping applying translation for cohesion field',
            ['@dataKey' => $dataKey, '@tmgmtKey' => $tmgmtKey, '@cohesionKey' => $cohesionKey]
          );
        }
      } else {
        $newData[$dataKey] = $dataValue;
      }
    }

    // Turn Cohesion's XML data items back to Cohesion's JSON.
    foreach ($cohesionData as $tmgmtKey => $cohesionItems) {
      $jobItem = $this->getJobItemFromTmgmtKey($tmgmtKey);

      if (empty($jobItem)) {
        $this->logger->warning(
          'Failed to load TMGMT Job Item by tmgmtKey="@tmgmtKey". Skipping applying translation for the cohesion fields',
          ['@tmgmtKey' => $tmgmtKey]
        );

        continue;
      }

      $layoutCanvas = $this->getLayoutCanvasFromJobItem($jobItem, $tmgmtKey);

      if (empty($layoutCanvas)) {
        $this->logger->warning(
          'Failed to load Layout Canvas by tmgmtKey="@tmgmtKey". Skipping applying translation for the cohesion fields',
          ['@tmgmtKey' => $tmgmtKey]
        );

        continue;
      }

      foreach ($cohesionItems as $cohesionItemKey => $cohesionItemData) {
        // Cohesion's key: "model(0):<model_uid>(1):property(2):<property_uid>(3)".
        $explodedCohesionKey = explode(":", $cohesionItemKey);
        $modelUid = isset($explodedCohesionKey[1]) ? $explodedCohesionKey[1] : null;
        $propertyUid = isset($explodedCohesionKey[3]) ? $explodedCohesionKey[3] : null;
        $text = $cohesionItemData["#text"];

        foreach ($layoutCanvas->iterateModels() as $model) {
          if ($model->getUUID() === $modelUid) {
            $property = $model->getProperty($propertyUid);

            if ($this->isCohesionPropertyHtmlText($property)) {
              $this->logger->info(
                'Importing Acquia Cohesion rich text field: model=@modelUid property=@propertyUid',
                ['@modelUid' => $modelUid, '@propertyUid' => $propertyUid]
              );

              $property->text = $text;
            } else {
              $this->logger->info(
                'Importing Acquia Cohesion plain text field: model=@modelUid property=@propertyUid',
                ['@modelUid' => $modelUid, '@propertyUid' => $propertyUid]
              );

              $property = $text;
            }

            $model->setProperty($propertyUid, $property);
          }
        }
      }

      $newData[$tmgmtKey]["#text"] = json_encode($layoutCanvas->jsonSerialize());
    }

    return $newData;
  }

  /**
   * @param $dataKey
   * @return bool
   */
  protected function isCohesionDataKey($dataKey) {
    return count($this->explodeDataKey($dataKey)) > 1;
  }

  /**
   * @param string $dataKey
   * @return array
   */
  protected function explodeDataKey($dataKey) {
    return explode(self::TMGMT_SMARTLING_ACQUIA_COHESION_KEY_DELIMITER, $dataKey);
  }

  /**
   * @param string $dataKey
   * @return string
   */
  protected function getTmgmtKeyFromDataKey($dataKey) {
    return $this->explodeDataKey($dataKey)[0];
  }

  /**
   * @param string $dataKey
   * @return string|null
   */
  protected function getCohesionKeyFromDataKey($dataKey) {
    $explodedDataKey = $this->explodeDataKey($dataKey);

    if (count($explodedDataKey) > 1) {
      return $explodedDataKey[1];
    }

    return null;
  }

  /**
   * @param string $tmgmtKey
   * @return JobItem|null
   */
  protected function getJobItemFromTmgmtKey($tmgmtKey) {
    return JobItem::load($this->explodeTmgmtKey($tmgmtKey)[0]);
  }

  /**
   * @param string $tmgmtKey
   * @return array
   */
  protected function explodeTmgmtKey($tmgmtKey) {
    return explode(Data::TMGMT_ARRAY_DELIMITER, $tmgmtKey);
  }

  /**
   * @param JobItemInterface $jobItem
   * @param string $tmgmtKey
   * @return LayoutCanvas|null
   */
  protected function getLayoutCanvasFromJobItem(JobItemInterface $jobItem, $tmgmtKey) {
    $explodedTmgmtKey = $this->explodeTmgmtKey($tmgmtKey);

    // TMGMT key is the same for cohesion fields:
    // "<job_item_id(0)>][<field_name(1)>][0(2)][entity(3)][json_values(4)][0(5)][value(6)".
    $layoutCanvasFieldData = $jobItem->getData([$explodedTmgmtKey[1]]);

    if (isset($layoutCanvasFieldData[$explodedTmgmtKey[2]][$explodedTmgmtKey[3]][$explodedTmgmtKey[4]][$explodedTmgmtKey[5]][$explodedTmgmtKey[6]]["#text"])) {
      return new LayoutCanvas($layoutCanvasFieldData[$explodedTmgmtKey[2]][$explodedTmgmtKey[3]][$explodedTmgmtKey[4]][$explodedTmgmtKey[5]][$explodedTmgmtKey[6]]["#text"]);
    }

    return null;
  }

  /**
   * @param mixed $property
   * @return bool
   */
  protected function isCohesionPropertyHtmlText($property) {
    return $property instanceof \stdClass && property_exists($property, "text") && property_exists($property, "textFormat");
  }

  /**
   * @param mixed $property
   * @return bool
   */
  protected function isTranslatableProperty($property) {
    return (
      is_string($property) && preg_match('/^\[media-reference:(.*):(.*)\]$/', $property) !== 1
    ) || $this->isCohesionPropertyHtmlText($property);
  }
}
