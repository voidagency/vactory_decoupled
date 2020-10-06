<?php

namespace Drupal\vactory_jenkins;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\State\StateInterface;

/**
 * Class DeployLogger.
 */
class DeployLogger {

  const LOGGER_CHANNEL_NAME = 'vactory_jenkins_logger';

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * Drupal\Core\State\StateInterface definition.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerChannel $logger,
    StateInterface $state,
    Connection $database
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $logger;
    $this->state = $state;
    $this->database = $database;
  }

  /**
   * Determines if we should log activity related to the passed entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   *
   * @return bool
   *   True if we should log it, false otherwise.
   */
  public function isEntityTypeLoggable(ContentEntityInterface $entity) {
    // @todo: add setting for this.
    return TRUE;
  }

  /**
   * Logs the creation of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   */
  public function logEntityCreated(ContentEntityInterface $entity) {
    $this->logger->info('@entityBundle: %entityTitle was created.', [
      '@entityBundle' => $entity->bundle(),
      '%entityTitle'  => $entity->label(),
    ]);
  }

  /**
   * Logs the updating of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   */
  public function logEntityUpdated(ContentEntityInterface $entity) {
    $this->logger->info('@entityBundle: %entityTitle was updated.', [
      '@entityBundle' => $entity->bundle(),
      '%entityTitle'  => $entity->label(),
    ]);
  }

  /**
   * Logs the deleting of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   */
  public function logEntityDeleted(ContentEntityInterface $entity) {
    $this->logger->info('@entityBundle: %entityTitle was deleted.', [
      '@entityBundle' => $entity->bundle(),
      '%entityTitle'  => $entity->label(),
    ]);
  }

  /**
   * Get the last deployed time for an environment.
   *
   */
  public function setLastDeployTime() {
    $this->state->set('lastDeploy', time());
  }

  /**
   * Get the last deployed time for an environment.
   *
   * @return mixed
   *   The timestamp of the latest deployment for the environment.
   */
  public function getLastDeployTime() {
    return $this->state->get('lastDeploy', 0);
  }

  /**
   * Gets a list of the last relevant log items after a certain timestamp.
   *
   * @param int $timestamp
   *   The timestamp after which to get the elements.
   *
   * @return array
   *   An array of log items.
   */
  public function getLogItemsSinceTimestamp($timestamp) {
    $type = self::LOGGER_CHANNEL_NAME;
    $query = $this->database->select('watchdog', 'w');
    $query->fields('w', [
      'wid',
    ]);
    $query->condition('w.timestamp', $timestamp, '>');
    $query->condition('w.type', $type, '=');
    $result = $query
      ->execute();
    return $result->fetchCol(0);
  }

  /**
   * Gets how many changes have happened since the last deployment.
   *
   * @return int
   *   The amount of changes for the environment since last deployment.
   */
  public function getNumberOfItemsSinceLastDeployment() {
    $timestamp = $this->getLastDeployTime();
    $elements = $this->getLogItemsSinceTimestamp($timestamp);
    return count($elements);
  }

}
