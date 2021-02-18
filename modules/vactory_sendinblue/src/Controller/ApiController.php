<?php

namespace Drupal\vactory_sendinblue\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\sendinblue\SendinblueManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Messenger\MessengerInterface;

class ApiController extends ControllerBase
{

  const ERROR_MISSING_ID = 1;
  const ERROR_MISSING_EMAIL = 2;
  const ERROR_INVALID_EMAIL = 3;
  const ERROR_EMAIL_ALREADY_EXISTS = 4;

  /**
   * SendinblueManager.
   *
   * @var \Drupal\sendinblue\SendinblueManager
   */
  protected $sendinblueManager;

  /**
   * EntityTypeManagerInterface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * EmailValidatorInterface.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    SendinblueManager $sendinblueManager,
    EntityTypeManagerInterface $entityTypeManager,
    MessengerInterface $messenger,
    EmailValidatorInterface $emailValidator
  )
  {
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->emailValidator = $emailValidator;
    $this->sendinblueManager = $sendinblueManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('sendinblue.manager'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function send(Request $request)
  {
    $form_data = $request->request->all();

    // Basic check for list ID.
    if (empty($form_data['id'])) {
      return new JsonResponse([
        'status' => 'error',
        'type' => 'system',
        'code' => static::ERROR_MISSING_ID,
        'message' => t('Missing id'),
      ], 400);
    }

    if (empty($form_data['email'])) {
      return new JsonResponse([
        'status' => 'error',
        'type' => 'system',
        'code' => static::ERROR_MISSING_EMAIL,
        'message' => t('Missing email'),
      ], 400);
    }

    $signupID = (int)$form_data['id'];
    $email = $form_data['email'];

    $signup = $this->entityTypeManager
      ->getStorage(SendinblueManager::SENDINBLUE_SIGNUP_ENTITY)
      ->load($signupID);

    if ($signup === NULL || empty($signup)) {
      return new JsonResponse([
        'status' => 'error',
        'type' => 'system',
        'code' => static::ERROR_MISSING_ID,
        'message' => t('Invalid id'),
      ], 400);
    }

    $settings = (!$signup->settings->first()) ? [] : $signup->settings->first()
      ->getValue();
    $list_id = $settings['subscription']['settings']['list'];
    $email_confirmation = $settings['subscription']['settings']['email_confirmation'];
    $redirect_url = $settings['subscription']['settings']['redirect_url'];

    // Validate email.
    if (!$this->emailValidator->isValid($email)) {
      return new JsonResponse([
        'status' => 'error',
        'type' => 'field',
        'field' => 'email',
        'code' => static::ERROR_INVALID_EMAIL,
        'message' => t('Invalid email'),
      ], 400);
    }

    $emailValidationResponse = $this->sendinblueManager->validationEmail($email, $list_id);
    if ($emailValidationResponse['code'] === 'invalid') {
      return new JsonResponse([
        'status' => 'error',
        'type' => 'field',
        'field' => 'email',
        'code' => static::ERROR_INVALID_EMAIL,
        'message' => t('Invalid email'),
      ], 400);
    }
    if ($emailValidationResponse['code'] === 'already_exist') {
      return new JsonResponse([
        'status' => 'error',
        'type' => 'field',
        'field' => 'email',
        'code' => static::ERROR_EMAIL_ALREADY_EXISTS,
        'message' => t('Email already exists'),
      ], 400);
    }
    $info = []; // @todo: No data passed for now.
    $list_ids = [$list_id];

    $this->sendinblueManager->subscribeUser($email, $info, $list_ids);

    // Store db.
    $data = $this->sendinblueManager->getSubscriberByEmail($email);
    if ($data == FALSE) {
      $data = [
        'email' => $email,
        'info' => serialize($info),
        'code' => uniqid('', TRUE),
        'is_active' => 1,
      ];
      $this->sendinblueManager->addSubscriberTable($data);
    }

    $response = [
      'status' => 'success',
      'email' => $email,
      'messages' => $settings['subscription']['messages']
    ];

    if ($email_confirmation) {
      $template_id = $settings['subscription']['settings']['template'];
      $this->sendinblueManager->sendEmail('confirm', $email, $template_id);
    }

    if ($redirect_url != '') {
      $url = \Drupal::service('vactory_core.url_resolver')->fromUri($redirect_url);
      $response['redirect'] = $url;
    }

    return new JsonResponse($response, 201);
  }
}
