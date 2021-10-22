<?php

namespace Drupal\vactory_reminder\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\vactory_reminder\Services\Exception\ReminderConsumerIdNotFoundException;
use Drupal\vactory_reminder\SuspendCurrentItemException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\vactory_reminder\ReminderManager;

/**
 * Processes Reminder Tasks.
 *
 * @QueueWorker(
 *   id = "reminder_queue_processor",
 *   title = @Translation("Queue worker Reminder"),
 * )
 */
class ReminderQueueProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The reminder plugin manager service.
   *
   * @var \Drupal\vactory_reminder\ReminderManager
   */
  protected $reminderPluginManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vactory_reminder\ReminderManager $reminder_plugin_manager
   *   Reminder plugin manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ReminderManager $reminder_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->reminderPluginManager = $reminder_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.reminder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (!isset($data['date']) || !isset($data['consumer_id']) || !isset($data['plugin_id'])) {
      throw new \Exception('The queue is broken.');
    }
    // Get consumer related date interval from module settings.
    $config = \Drupal::config('vactory_reminder.settings');
    $reminder_consumers = $config->get('reminder_consumers');
    if (isset($reminder_consumers) && !isset($reminder_consumers[$data['consumer_id']])) {
      throw new ReminderConsumerIdNotFoundException('Reminder Consumer ID "' . $data['consumer_id'] . '" not found');
    }
    $send_at = $reminder_consumers[$data['consumer_id']];
    $plugin = $this->getPlugin($data['plugin_id']);
    if ($this->isDateTimeApproaching($data['date'], $send_at)) {
      $plugin->processItem($data);
    }
    else {
      throw new SuspendCurrentItemException('Date is still far away.');
    }
  }

  /**
   * {@inheritdoc}
   */
  private function isDateTimeApproaching($start_date, $relative_unit = '-1 hour') {
    $origin = new \DateTime($start_date);
    $currentTime = time();
    $date_begin = strtotime($relative_unit, $origin->getTimestamp());
    $dateEnd = $origin->getTimestamp();

    // Handle send after date case.
    if ($date_begin > $dateEnd) {
      return $currentTime > $date_begin;
    }

    // Handle send before date case.
    if ($currentTime > $dateEnd) {
      return TRUE;
    }
    return ($currentTime > $date_begin && $currentTime < $dateEnd);
  }

  /**
   * Get a reminder plugin instance.
   */
  private function getPlugin($plugin_id) {
    /** @var Drupal\vactory_reminder\ReminderInterface[] $plugin */
    static $plugin = [];

    if (!isset($plugin[$plugin_id])) {
      /** @var Drupal\vactory_reminder\ReminderInterface $p */
      $p = $this->reminderPluginManager->createInstance($plugin_id);
      $plugin[$plugin_id] = $p;
    }

    return $plugin[$plugin_id];
  }

}
