<?php

namespace Drupal\vactory_oauth\Controller;

//use Drupal\Component\Serialization\Json;
//use Drupal\Component\Utility\Crypt;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
//use Drupal\oauth2_server\OAuth2StorageInterface;
//use Drupal\oauth2_server\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WellKnownController extends ControllerBase {

//  /**
//   * The OAuth2Storage.
//   *
//   * @var \Drupal\oauth2_server\OAuth2StorageInterface
//   */
//  protected $storage;
  protected $loggerFactory;
  protected $logger;

  /**
   * Constructs a new \Drupal\oauth2_server\Controller\OAuth2Controller object.
   *
   * @param \Drupal\oauth2_server\OAuth2StorageInterface $oauth2_storage
   *   The OAuth2 storage object.
   * @param LoggerChannelFactoryInterface $loggerFactory
   *   Drupal Logger Factory
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory) {
    $this->loggerFactory = $loggerFactory;
    $this->logger = $this->loggerFactory->get('openid_connect_autodiscovery');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')
    );
  }

//  /**
//   * Certificates. Clone from oauth2_service
//   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
//   *   Route found.
//   * @param \Symfony\Component\HttpFoundation\Request $request
//   *   Request in use.
//   * @return \Symfony\Component\HttpFoundation\JsonResponse
//   */
//  public function certificates(RouteMatchInterface $route_match, Request $request) {
//    $keys = Utility::getKeys();
//    $certificates = [];
//    $certificates[] = $keys['public_key'];
//    return new JsonResponse($certificates);
//  }

  /**
   * Return the JWKS endpoint JSON.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function jwksEndpoint(RouteMatchInterface $route_match, Request $request) {
    \Drupal::logger('vactory_oauth')->error('not yer implemented');

    throw new HttpException(522,"SSL subsytem failure detected.");

    //    $keys = Utility::getKeys();
//    $cert = openssl_x509_read($keys['public_key']);
//    $publicKey = openssl_get_publickey($cert);
//    openssl_x509_free($cert);
//    $keyDetails = openssl_pkey_get_details($publicKey);
//    openssl_pkey_free($publicKey);
//    $jsonKeys['e'] = base64_encode($keyDetails['rsa']['e']);
//    $jsonKeys['n'] = base64_encode($keyDetails['rsa']['n']);
//    $jsonKeys['mod'] = self::base64url_encode($keyDetails['rsa']['n']);
//    $jsonKeys['exp'] = self::base64url_encode($keyDetails['rsa']['e']);
//    $jsonKeys['x5c'][] = self::base64url_encode(self::pem2der($keys['public_key']));
//    $jsonKeys['kty'] = 'RSA';
//    $jsonKeys['use'] = "sig";
//    $jsonKeys['alg'] = "RS256";
//
//
//    $json = ["keys"=>[$jsonKeys]];
//    $opensslError = openssl_error_string();
//
//    if($opensslError !== false){
//      $this->logger->error("JWKS-json Generator OpenSSL Error [@Err]",["@Err" => $opensslError]);
//      //            $errorResponse = new HtmlResponse("SSL subsytem failure detected.",522);
//      throw new HttpException(522,"SSL subsytem failure detected.");
//    }
//    return new JsonResponse($json,200);
  }

  /**
   * Return the autodiscovery JSON
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function openidDiscovery(RouteMatchInterface $route_match, Request $request) {
    $base_root = $request->getSchemeAndHttpHost();
    $base_url = $base_root;
    $oidc = [];
//    $oidc['issuer'] = $base_root . "/";
    $oidc['issuer'] = $base_root;
    $oidc["authorization_endpoint"] = Url::fromRoute('oauth2_token.authorize')->setAbsolute()->toString();
    $oidc["token_endpoint"] = Url::fromRoute('oauth2_token.token')->setAbsolute()->toString();
    $oidc["userinfo_endpoint"] =  Url::fromRoute('vactory_oauth.userinfo')->setAbsolute()->toString();
    $oidc["end_session_endpoint"] = Url::fromRoute('user.logout')->setAbsolute()->toString();
    $oidc["subject_types_supported"] = ["public"];
    $oidc["response_types_supported"] = ["code","token"];
    $oidc["jwks_uri"] = Url::fromRoute('vactory_oauth.openid-jwks')->setAbsolute()->toString();
    $oidc['id_token_signing_alg_values_supported'] ="RS256";
    return new JsonResponse($oidc);
  }

  /**
   * Convert a Pem encoded to Der encoded certificate.
   * @param $pem_data
   * @return bool|string
   * @see http://php.net/manual/en/ref.openssl.php#74188
   */
  public static function pem2der($pem_data) {
    $begin = "CERTIFICATE-----";
    $end   = "-----END";
    $pem_data = substr($pem_data, strpos($pem_data, $begin)+strlen($begin));
    $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
    $der = base64_decode($pem_data);
    return $der;
  }

  /**
   * Do the specific Base64 encoding as used by OIDC.
   * @param $data
   * @return string
   * @see http://php.net/manual/en/function.base64-encode.php#103849
   */
  public static function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

}
