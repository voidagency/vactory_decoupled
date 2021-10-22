<?php

namespace Drupal\vactory_oauth_login\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Site\Settings;

/**
 * Redirects users.
 *
 * Logged in users are taken to the front-end sign-in page to init OAuth
 */
class UserLoginSubscriber implements EventSubscriberInterface
{

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a new redirect subscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(AccountInterface $account)
  {
    $this->account = $account;
  }

  /**
   * Redirects users when access is denied.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The event to process.
   */
  public function onKernelRequest(FilterResponseEvent $event)
  {
    $request = $event->getRequest();
    $route_name = RouteMatch::createFromRequest($request)->getRouteName();
    $redirect_url = NULL;

    if
    ($this->account->isAuthenticated() &&
      $route_name === 'user.login' &&
      $request->get('auth') === "true") {
      $redirect_url = Settings::get('BASE_FRONTEND_URL');
      $redirect_url .= '/signin/';
      $event->setResponse(new RedirectResponse($redirect_url));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    // Use a lower priority than
    // \Drupal\user\EventSubscriber\AccessDeniedSubscriber, because we wanna skip
    // the part it redirected the authenticated user to it profile.
    $events[KernelEvents::RESPONSE][] = ['onKernelRequest'];
    return $events;
  }

}
