<?php

namespace Drupal\vactory_jsonapi\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod\Drupal8Post;

require_once 'modules/contrib/recaptcha' . '/recaptcha-php/src/ReCaptcha/ReCaptcha.php';
require_once 'modules/contrib/recaptcha' . '/recaptcha-php/src/ReCaptcha/RequestMethod.php';
require_once 'modules/contrib/recaptcha' . '/recaptcha-php/src/ReCaptcha/RequestParameters.php';
require_once 'modules/contrib/recaptcha' . '/recaptcha-php/src/ReCaptcha/Response.php';
require_once 'modules/contrib/recaptcha' . '/src/ReCaptcha/RequestMethod/Drupal8Post.php';

/**
 * Validates the Recaptcha constraint.
 */
class RecaptchaValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $request = \Drupal::request();
    $method = strtoupper($request->getMethod());

    if (in_array($method, [
      'GET',
      'HEAD',
      'CONNECT',
      'TRACE',
      'OPTIONS',
    ], TRUE)) {
      return;
    }
    $raw_data = $request->getContent();
    $data = \Drupal\Component\Serialization\Json::decode($raw_data);
    $value = $data['data']['attributes']['g-recaptcha-response'] ?? '';

    if (empty($value)) {
      $this->context->buildViolation($constraint->required)
        ->atPath('g_recaptcha_response')
        ->setCode('factory-7a99-4df7-8ce9-46e416a1e60b')
        ->addViolation();
//      $this->context->addViolation($constraint->required, ['%value' => $value]);
    }
    else {
      if (!$this->isValid($value)) {
        $this->context->buildViolation($constraint->notValid)
          ->atPath('g-recaptcha-response')
          ->addViolation();
//        $this->context->addViolation($constraint->notValid, ['%value' => $value]);
      }
    }
  }

  /**
   * Is valid?
   *
   * @param string $value
   */
  private function isValid($value) {
    $config = \Drupal::config('recaptcha.settings');
    $recaptcha_secret_key = $config->get('secret_key');
    // Use Drupal::httpClient() to circumvent all issues with the Google library.
    $recaptcha = new ReCaptcha($recaptcha_secret_key, new Drupal8Post());

    // Ensures the hostname matches. Required if "Domain Name Validation" is
    // disabled for credentials.
    if ($config->get('verify_hostname')) {
      $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME']);
    }

    $resp = $recaptcha->verify(
      $value,
      \Drupal::request()->getClientIp()
    );

    return $resp->isSuccess();
  }

}
