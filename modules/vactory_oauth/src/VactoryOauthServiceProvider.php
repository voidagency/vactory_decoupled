<?php
// https://www.drupal.org/project/simple_oauth/issues/2944981

namespace Drupal\vactory_oauth;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\vactory_oauth\OpenIdConnect\UserIdentityProvider;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Service provider that alters the Simple OAuth response type paramenters.
 *
 * Uses the ID token response that will include the ID token.
 */
class VactoryOauthServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('simple_oauth.server.response_type')) {
      $definition = $container->getDefinition('simple_oauth.server.response_type');
      $definition->setClass(IdTokenResponse::class);
      $definition->setArguments([
        new Reference(UserIdentityProvider::class),
        new Reference(ClaimExtractor::class),
      ]);
    }
  }

}
