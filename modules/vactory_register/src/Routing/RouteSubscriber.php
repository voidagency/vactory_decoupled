<?php

namespace Drupal\vactory_register\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {


  /**
   * @inheritDoc
   */
  protected function alterRoutes(RouteCollection $collection)
  {
    if ($route = $collection->get('user.register')) {
      $route->setDefault('_controller', '\Drupal\vactory_register\Controller\FormRender::register');
    }
  }
}
