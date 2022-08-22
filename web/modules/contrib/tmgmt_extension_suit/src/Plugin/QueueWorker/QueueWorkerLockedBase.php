<?php

namespace Drupal\tmgmt_extension_suit\Plugin\QueueWorker;

use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\RequeueException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class QueueWorkerLockedBase.
 *
 * Base class for queue workers that need to guarantee that only one
 * instance of queue worker processes queue at a time.
 *
 * @package Drupal\tmgmt_extension_suit\Plugin\QueueWorker
 */
abstract class QueueWorkerLockedBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * Constructs a new LocaleTranslation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   Lock service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, LockBackendInterface $lock, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->logger = $logger;
    $this->lock = $lock;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('lock.persistent'),
      $container->get('logger.channel.tmgmt_extension_suit')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $lockId = get_called_class() . ":processItem";

    // Lock ttl equals to time from queue worker plugin setup. There is no
    // need in locking more than required.
    $ttl = $this->getPluginDefinition()["cron"]["time"];

    if (!$this->lock->acquire($lockId, $ttl)) {
      // Do not remove item from queue if lock is already acquired.
      // It means current queue is being processed by another process.
      throw new RequeueException("Attempting to re-acquire $lockId.");
    }
    else {
      // Call hook method implemented by children.
      try {
        $this->doProcessitem($data);
      } finally {
        // Release lock when work is done.
        $this->lock->release($lockId);
      }
    }
  }

  /**
   * Hook method to be implemented in child classes.
   *
   * @param array $data
   *   Queue item data.
   */
  protected function doProcessItem(array $data) {}

}
