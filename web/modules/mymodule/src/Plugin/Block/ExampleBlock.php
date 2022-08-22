<?php

namespace Drupal\mymodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "mymodule_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("Mymodule")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('My block It works!'),
    ];
    return $build;
  }

}
