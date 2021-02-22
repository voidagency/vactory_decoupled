<?php

namespace Drupal\vactory_oauth\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribing an event.
 */
class SecurityEventSubscriber implements EventSubscriberInterface
{

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Response object.
   *
   * @var \Symfony\Component\HttpFoundation\Response
   */
  protected $response;

  /**
   * Constructs an SecKitEventSubscriber object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory)
  {
    $this->config = $config_factory->get('seckit.settings');
  }

  /**
   * Executes actions on the response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Filter Response Event object.
   */
  public function onKernelResponse(FilterResponseEvent $event)
  {
    if (!$event->isMasterRequest()) {
      return;
    }

    // \Symfony\Component\HttpFoundation\Request $request
    $request = $event->getRequest();
    $response = $event->getResponse();

    if (
      strpos($request->getRequestUri(), '/user/login') !== false ||
      strpos($request->getRequestUri(), '/oauth/authorize') !== false) {
      $response->headers->remove('X-Frame-Options');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    $events[KernelEvents::RESPONSE][] = ['onKernelResponse'];
    return $events;
  }
}
