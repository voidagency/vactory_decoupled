<?php

namespace Drupal\vactory_oauth\EventSubscriber;

use Drupal\Component\HttpFoundation\SecuredRedirectResponse;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Utility\UnroutedUrlAssemblerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allows manipulation of the response object when performing a redirect.
 */
class RedirectResponseSubscriber implements EventSubscriberInterface {

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * The unrouted URL assembler service.
   *
   * @var \Drupal\Core\Utility\UnroutedUrlAssemblerInterface
   */
  protected $unroutedUrlAssembler;

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * Constructs a RedirectResponseSubscriber object.
   *
   * @param \Drupal\Core\Utility\UnroutedUrlAssemblerInterface $url_assembler
   *   The unrouted URL assembler service.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   */
  public function __construct(UnroutedUrlAssemblerInterface $url_assembler, RequestContext $request_context, SessionInterface $session) {
    $this->unroutedUrlAssembler = $url_assembler;
    $this->requestContext = $request_context;
    $this->session = $session;
  }

  /**
   * Save destination in session.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    $destination = $request->query->get('destination');
    if (!empty($destination)) {
      $is_oauth = strpos($destination, '/oauth/authorize');

      if ($is_oauth !== FALSE) {
        $this->session->set('oauth_destination', $destination);
      }
    }
  }

  /**
   * Redirect to destination when performing a redirect.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The Event to process.
   */
  public function onResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();

    if (
      $response instanceof RedirectResponse &&
      $this->session->has('oauth_destination')
    ) {
      $target_url = $response->getTargetUrl();
      $is_profile = (boolean) preg_match('/\/user\/[0-9]+/', $target_url);

      if ($is_profile) {
        $request = $event->getRequest();
        $destination = $this->session->get('oauth_destination');
        $destination = $this->getDestinationAsAbsoluteUrl($destination, $request->getSchemeAndHttpHost());

        // Remove session.
        $this->session->remove('oauth_destination');

        // The 'Location' HTTP header must always be absolute.
        try {
          $response->setTargetUrl($destination);
        } catch (\InvalidArgumentException $e) {
        }

        // Regardless of whether the target is the original one or the overridden
        // destination, ensure that all redirects are safe.
        if (!($response instanceof SecuredRedirectResponse)) {
          try {
            // SecuredRedirectResponse is an abstract class that requires a
            // concrete implementation. Default to LocalRedirectResponse, which
            // considers only redirects to within the same site as safe.
            $safe_response = LocalRedirectResponse::createFromRedirectResponse($response);
            $safe_response->setRequestContext($this->requestContext);
          } catch (\InvalidArgumentException $e) {
            // If the above failed, it's because the redirect target wasn't
            // local. Do not follow that redirect. Display an error message
            // instead. We're already catching one exception, so trigger_error()
            // rather than throw another one.
            // We don't throw an exception, because this is a client error rather than a
            // server error.
            $message = 'Redirects to external URLs are not allowed by default, use \Drupal\Core\Routing\TrustedRedirectResponse for it.';
            trigger_error($message, E_USER_ERROR);
            $safe_response = new Response($message, 400);
          }
          $event->setResponse($safe_response);
        }
      }
    }
  }

  /**
   * Converts the passed in destination into an absolute URL.
   *
   * @param string $destination
   *   The path for the destination. In case it starts with a slash it should
   *   have the base path included already.
   * @param string $scheme_and_host
   *   The scheme and host string of the current request.
   *
   * @return string
   *   The destination as absolute URL.
   */
  protected function getDestinationAsAbsoluteUrl($destination, $scheme_and_host) {
    if (!UrlHelper::isExternal($destination)) {
      // The destination query parameter can be a relative URL in the sense of
      // not including the scheme and host, but its path is expected to be
      // absolute (start with a '/'). For such a case, prepend the scheme and
      // host, because the 'Location' header must be absolute.
      if (strpos($destination, '/') === 0) {
        $destination = $scheme_and_host . $destination;
      }
      else {
        // Legacy destination query parameters can be internal paths that have
        // not yet been converted to URLs.
        $destination = UrlHelper::parse($destination);
        $uri = 'base:' . $destination['path'];
        $options = [
          'query'    => $destination['query'],
          'fragment' => $destination['fragment'],
          'absolute' => TRUE,
        ];
        // Treat this as if it's user input of a path relative to the site's
        // base URL.
        $destination = $this->unroutedUrlAssembler->assemble($uri, $options);
      }
    }
    return $destination;
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    // Priority 30 before cache.
    $events[KernelEvents::REQUEST][] = ['onRequest', '30'];
    $events[KernelEvents::RESPONSE][] = ['onResponse', '30'];
    return $events;
  }

}
