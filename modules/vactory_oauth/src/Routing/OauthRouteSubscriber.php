<?php

namespace Drupal\vactory_oauth\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Sets the _auth for specific oauth-related routes.
 */
class OauthRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // @todo: routes are hardcoded.
    $oauth_protected_routes = [
      '/api/user/user',
      '/api/user/user/{entity}',
      '/api/user/user/{entity}/relationships/roles',
      '/api/user/user/{entity}/relationships/content_translation_uid',
      '/api/user/user/{entity}/content_translation_uid',
      '/api/user/user/{entity}/relationships/user_picture',
      '/api/user/user/{entity}/user_picture',
      '/api/user/user/{file_field_name}',
      '/api/user/user/{entity}/{file_field_name}',
    ];

    foreach ($collection->all() as $route) {
      if (in_array($route->getPath(), $oauth_protected_routes)) {
        $route->setRequirements([
          '_permission' => 'access user profiles',
        ]);
        $route->setOption('_auth', ['oauth2']);
      }
    }
  }

}
