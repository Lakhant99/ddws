<?php

namespace Drupal\tmgmt_extension_suit\Utils;

use Drupal\Core\Database\Connection;
use Drupal\Core\Queue\QueueFactory;
use Psr\Log\LoggerInterface;

/**
 * Class UniqueQueueItem.
 *
 * Adds only unique queue items to the queue.
 *
 * @package Drupal\tmgmt_extension_suit\Utils
 */
class UniqueQueueItem {

  /**
   * Queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private $queueFactory;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $databaseConnection;

  /**
   * UniqueQueueItem constructor.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   * @param \Drupal\Core\Database\Connection $database_connection
   *   Database connection.
   */
  public function __construct(QueueFactory $queue_factory, LoggerInterface $logger, Connection $database_connection) {
    $this->queueFactory = $queue_factory;
    $this->logger = $logger;
    $this->databaseConnection = $database_connection;
  }

  /**
   * Adds item into a queue only if it's unique.
   *
   * @param string $queue_name
   *   Queue name.
   * @param array $data
   *   Data to be added.
   * @param bool $force
   *   Force adding item into the queue.
   */
  public function addItem($queue_name, array $data, $force = FALSE) {
    $serialized_data = serialize($data);

    if (!$force) {
      $count = $this->databaseConnection->select('queue', 'q')
        ->condition('q.name', $queue_name)
        ->condition('q.data', $serialized_data)
        ->countQuery()
        ->execute()
        ->fetchField();

      if ($count != 0) {
        return;
      }
    }

    $result = $this->queueFactory->get($queue_name)->createItem($data);

    if ($result) {
      $this->logger->info('New unique item has been added into the "@queue" queue. Serialized queue item: @item, forced: @forced.', [
        '@queue' => $queue_name,
        '@item' => $serialized_data,
        '@forced' => (int) $force,
      ]);
    }
  }

}
