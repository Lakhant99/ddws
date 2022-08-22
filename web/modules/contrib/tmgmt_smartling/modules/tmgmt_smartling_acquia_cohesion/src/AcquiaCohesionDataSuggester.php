<?php

namespace Drupal\tmgmt_smartling_acquia_cohesion;

use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\tmgmt\JobItemInterface;
use Psr\Log\LoggerInterface;

class AcquiaCohesionDataSuggester implements LayoutCanvasAwareInterface {
  use LayoutCanvasAwareTrait;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * AcquiaCohesionDataSuggester constructor.
   * @param LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * @param array $jobItems
   * @return array
   */
  public function suggestCohesionContentComponents(array $jobItems) {
    $suggestions = [];

    foreach ($jobItems as $jobItem) {
      $jobItemData = $jobItem->getData();
      $suggestions = array_merge(
        $suggestions,
        $this->walkRecursiveAndGetSuggestions($jobItemData, $jobItem)
      );
    }

    return $suggestions;
  }

  /**
   * Only implemented manually because phpunit fails to run tests with closures.
   *
   * This recursion could be easily rewritten with array_walk_recursive
   * with closure but phpunit serializes data for process isolation purposes
   * which causes exception because closure can not be serialized.
   *
   * @param mixed $data
   * @param JobItemInterface $jobItem
   * @return array
   */
  protected function walkRecursiveAndGetSuggestions($data, JobItemInterface $jobItem) {
    $suggestions = [];

    if (is_string($data) && $layoutCanvas = $this->isLayoutCanvas($data)) {
      foreach ($layoutCanvas->iterateCanvas() as $element) {
        if ($element->isComponentContent()) {
          // This is the way Acquia Cohesion module uses for fetching number
          // from componentId property so used here ias well.
          $id = str_replace("cc_", "", $element->getProperty("componentContentId"));
          $componentContent = $this->loadComponentContent($id);

          if (empty($componentContent)) {
            $this->logger("Tried to suggest component content id=@id but failed to load it", [
              "@id" => $id
            ]);

            continue;
          }

          $suggestions[] = [
            'job_item' => tmgmt_job_item_create('content', $componentContent->getEntityTypeId(), $componentContent->id()),
            'reason' => t('Referenced content component'),
            'from_item' => $jobItem->id(),
          ];
        }
      };
    }

    if (is_array($data)) {
      foreach ($data as $key => $value) {
        $suggestions += $this->walkRecursiveAndGetSuggestions($value, $jobItem);
      }
    }

    return $suggestions;
  }

  /**
   * Needed for testing.
   *
   * @param int $id
   * @return ComponentContent|null
   */
  protected function loadComponentContent($id) {
    return ComponentContent::load($id);
  }
}
