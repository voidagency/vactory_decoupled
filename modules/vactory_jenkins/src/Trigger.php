<?php

namespace Drupal\vactory_jenkins;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Site\Settings;

/**
 * Class Trigger.
 */
class Trigger
{

  use MessengerTrait;
  use StringTranslationTrait;

  const DEPLOYMENT_STRATEGY_CRON = 'cron';

  const DEPLOYMENT_STRATEGY_ENTITYSAVE = 'entitysave';

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The http_client service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The current_user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The string_translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $stringTranslation;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger.factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The deploy logger service.
   *
   * @var \Drupal\vactory_jenkins\DeployLogger
   */
  protected $deployLogger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The cache tag invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagInvalidator;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a new Trigger object.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    ClientInterface $httpClient,
    AccountProxyInterface $currentUser,
    TranslationManager $stringTranslation,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger,
    DeployLogger $deployLogger,
    EntityTypeManager $entityTypeManager,
    CacheTagsInvalidatorInterface $cacheTagInvalidator,
    EventDispatcherInterface $event_dispatcher
  )
  {
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->currentUser = $currentUser;
    $this->stringTranslation = $stringTranslation;
    $this->messenger = $messenger;
    $this->logger = $logger;
    $this->deployLogger = $deployLogger;
    $this->entityTypeManager = $entityTypeManager;
    $this->cacheTagInvalidator = $cacheTagInvalidator;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Triggers all environments that are marked to fire on cron.
   */
  public function deployFrontendCron()
  {
    $this->deployByDeploymentStrategy(self::DEPLOYMENT_STRATEGY_CRON);
  }

  /**
   * Triggers all environments that are marked to fire on entity update.
   */
  public function deployFrontendEntityUpdate()
  {
    $this->deployByDeploymentStrategy(self::DEPLOYMENT_STRATEGY_ENTITYSAVE);
  }

  /**
   * Triggers all environments found by a specific deployment strategy.
   *
   * @param string $strategy
   *   The type of deployment strategy.
   */
  private function deployByDeploymentStrategy(string $strategy)
  {
    try {
      $this->triggerBuild();
    } catch (\Exception $e) {
      $this->messenger()
        ->addWarning($this->t('Could not trigger deployments with strategy @strategy. Error message: @error', [
          '@strategy' => $strategy,
          '@error' => $e->getMessage(),
        ]));
    }
  }

  /**
   * Checks if the user has the permission to trigger deployments.
   *
   * @return bool
   *   Boolean value.
   */
  private function isValidUser()
  {
    return $this->currentUser->hasPermission('trigger jenkins deployments');
  }

  /**
   * Trigger a deployment.
   */
  public function triggerBuild()
  {
    try {
      $response = $this->triggerWebHook();
      if ($response->getStatusCode() == 201) {
        // If the call was successful, set the latest deployment time.
        $this->deployLogger->setLastDeployTime();
        $this->messenger()
          ->addMessage($this->t('Deployment triggered'));
      } else {
        $this->messenger()
          ->addWarning($response->getReasonPhrase());
      }
    } catch (GuzzleException $e) {
      $error = [
        'Failed to execute build hook for environment @env . Error message: <pre> @message </pre>',
        [
          '@message' => $e->getMessage(),
        ],
      ];
      $this->messenger()
        ->addError($this->t('Failed to execute a deploy . Error message: <pre> @message </pre>', $error[1]));
      $this->logger->get('vactory_jenkins')->error($e->getMessage());
    }
  }

  /**
   * Triggers a build hook.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response of client.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function triggerWebHook()
  {
    $webhook_url = Settings::get('DEPLOY_WEBHOOK_URL', FALSE);

    $response = $this->httpClient->request(
      'POST',
      $webhook_url
    );

    return $response;
  }

  /**
   * Get build status.
   *
   * @param $webhook_url
   * @return array
   * @throws GuzzleException
   */
  public function getLastBuildStatus($webhook_url)
  {
    $response = $this->httpClient->request(
      'GET',
      $webhook_url
    );

    $status = [];

    if ($response->getStatusCode() === 200) {
      $bodyContent = $response->getBody()->getContents();
      $content = json_decode($bodyContent, TRUE);

      $status = [
        'name' => $content['fullDisplayName'],
        'status' => $content['result'],
        'timestamp' => $content['timestamp'],
      ];
    }

    return $status;
  }

  /**
   * Get build errors.
   *
   * @param $webhook_url
   * @return string
   * @throws GuzzleException
   */
  public function getLastBuildLog($webhook_url)
  {
    $response = $this->httpClient->request(
      'GET',
      $webhook_url
    );

    $status = '';

    if ($response->getStatusCode() === 200) {
      $status = $response->getBody()->getContents();
    }

    return $status;
  }

}
