<?php

namespace Drupal\tmgmt_smartling_acquia_cohesion;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;

trait LayoutCanvasAwareTrait {
  /**
   * @param $json
   * @return bool|LayoutCanvas
   */
  public function isLayoutCanvas($json) {
    if (preg_match('/^\{"canvas":\[(.*)\}$/', $json) !== 1 || empty(json_decode((string) $json))) {
      return false;
    }

    return new LayoutCanvas($json);
  }
}
