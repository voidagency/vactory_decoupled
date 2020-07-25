<?php

namespace Drupal\vactory_oauth\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\simple_oauth\Authentication\TokenAuthUser;
use Drupal\user\PermissionHandlerInterface;
use Drupal\vactory_oauth\Entities\UserEntityWithClaims;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\CryptKey;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for the User Info endpoint.
 */
final class UserInfo implements ContainerInjectionInterface {

  /**
   * The authenticated user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $user;

  /**
   * The user permissions.
   *
   * @var \Drupal\user\PermissionHandlerInterface
   */
  protected $userPermissions;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  private $serializer;

  /**
   * UserInfo constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The user.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer service.
   * @param \Drupal\user\PermissionHandlerInterface $user_permissions
   *   The user permissions.
   */
  private function __construct(AccountProxyInterface $user, SerializerInterface $serializer, PermissionHandlerInterface $user_permissions) {
    $this->user = $user->getAccount();
    $this->serializer = $serializer;
    $this->userPermissions = $user_permissions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('serializer'),
      $container->get('user.permissions')
    );
  }

  /**
   * The controller.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function handle(ServerRequestInterface $request) {
    assert($this->serializer instanceof NormalizerInterface);
    if (!$this->user instanceof TokenAuthUser) {
      throw new AccessDeniedHttpException('This route is only available for authenticated requests using OAuth2.');
    }

    $requestValidator = clone $request;
    $accessTokenRepository = \Drupal::service('simple_oauth.repositories.access_token');
    $settings = \Drupal::service('config.factory')
      ->get('simple_oauth.settings');
    $keyPath = realpath($settings->get('public_key'));
    $publicKey = new CryptKey($keyPath);
    $tokenValidator = new BearerTokenValidator($accessTokenRepository);
    $tokenValidator->setPublicKey($publicKey);

    try {
      $validatedRequest = $tokenValidator->validateAuthorization($requestValidator);
    } catch (OAuthServerException $e) {
    }

    $tokenInfo = [
      "uuid"   => $this->user->uuid(),
      "aud"    => $validatedRequest->getAttribute('oauth_client_id'),
      "jti"    => $validatedRequest->getAttribute('oauth_access_token_id'),
      "sub"    => $validatedRequest->getAttribute('oauth_user_id'),
      "scopes" => $validatedRequest->getAttribute('oauth_scopes'),
    ];

    //    $permissions_list = $this->userPermissions->getPermissions();
    //    $permission_info = [];
    //    // Loop over all the permissions and check if the user has access or not.
    //    foreach ($permissions_list as $permission_id => $permission) {
    //      $permission_info[$permission_id] = [
    //        'title'  => $permission['title'],
    //        'access' => $this->user->hasPermission($permission_id),
    //      ];
    //      if (!empty($permission['description'])) {
    //        $permission_info['description'] = $permission['description'];
    //      }
    //    }
    //
    //    $extraUserInfo = [
    //      'roles'       => $this->user->getRoles(),
    //      'permissions' => $permission_info,
    //    ];

    //    $token = $this->user->getToken();
    $identifier = $this->user->id();
    $user_entity = new UserEntityWithClaims();
    $user_entity->setIdentifier($identifier);
    $normalized = $this->serializer->normalize($user_entity, 'json', [$identifier => $this->user]);

    $normalizedUserinfo = array_merge($normalized, $tokenInfo);
    // @TODO: do we need permisisons ?
    //    $normalizedUserinfo = array_merge($normalizedUserinfo, $extraUserInfo);

    //    $response = CacheableJsonResponse::create(
    //      $normalizedUserinfo
    //    );

    return JsonResponse::create(
      $normalizedUserinfo
    );

    //    return $response
    //      ->addCacheableDependency($token)
    //      ->addCacheableDependency($this->user);
  }

}
