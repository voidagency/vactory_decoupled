<?php

namespace Drupal\vactory_reminder\Services;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\vactory_reminder\Services\Exception\ReminderConsumerIdNotFoundException;

/**
 * Reminder queue manager service.
 */
class ReminderQueueManager {

  /**
   * Reminder queue processor.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  private $reminderQueueProcessor;

  /**
   * Reminder plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  private $reminderPluginManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(QueueFactory $queueFactory, PluginManagerInterface $reminderPluginManager) {
    $this->reminderQueueProcessor = $queueFactory->get('reminder_queue_processor');
    $this->reminderPluginManager = $reminderPluginManager;
  }

  /**
   * Push new reminder task to reminder tasks queue.
   */
  public function reminderQueuePush($relatedDateTimestamp, string $consumerId, string $reminderPluginId, array $extra = []) {
    $relatedDate = \DateTime::createFromFormat('U', $relatedDateTimestamp);
    if (!$relatedDate instanceof \DateTime) {
      throw new \InvalidArgumentException(sprintf('The first argument of %s::reminderQueuePush should be a valid timestamp string, received value is "%s".', static::class, $relatedDateTimestamp));
    }
    if (empty($consumerId)) {
      throw new \InvalidArgumentException(sprintf('The second argument of %s::reminderQueuePush should not be empty, received value is "%s".', static::class, $consumerId));
    }
    if (empty($reminderPluginId)) {
      throw new \InvalidArgumentException(sprintf('The third argument of %s::reminderQueuePush should not be empty, received value is "%s".', static::class, $reminderPluginId));
    }
    if (!$this->reminderPluginManager->hasDefinition($reminderPluginId)) {
      throw new PluginNotFoundException($reminderPluginId);
    }
    // Get consumer related date interval from module settings.
    $config = \Drupal::config('vactory_reminder.settings');
    $reminder_consumers = $config->get('reminder_consumers');
    if (isset($reminder_consumers) && !isset($reminder_consumers[$consumerId])) {
      throw new ReminderConsumerIdNotFoundException('Reminder Consumer ID "' . $consumerId . '" not found');
    }
    $this->reminderQueueProcessor->createItem([
      'date' => $relatedDate->format('Y-m-d H:i:s'),
      'consumer_id' => $consumerId,
      'plugin_id' => $reminderPluginId,
      'extra' => $extra,
    ]);
  }

}
