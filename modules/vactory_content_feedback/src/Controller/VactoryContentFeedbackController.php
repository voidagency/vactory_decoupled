<?php

namespace Drupal\vactory_content_feedback\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\Controller\ControllerBase;
use Drupal\admin_feedback\Controller\AdminFeedbackController;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The VactoryContentFeedbackController class.
 */
class VactoryContentFeedbackController extends ControllerBase {

  /**
   * The Database Connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The RequestStack object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The LanguageManager object.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Event Dispatcher Service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('language_manager'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Construct an AdminFeedbackController object.
   */
  public function __construct(Connection $database, RequestStack $requestStack, LanguageManagerInterface $languageManager, EventDispatcherInterface $eventDispatcher) {
    $this->database = $database;
    $this->requestStack = $requestStack;
    $this->languageManager = $languageManager;
    $this->eventDispatcher = $eventDispatcher;
  }

  

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function updateFeedback(Request $request) {
    $data = json_decode($request->getContent());

    // Basic check for Feedback ID.
    if (empty($data->feedback_id)) {
      return new JsonResponse([
        'message' => t('Missing id'),
      ], 400);
    }

    if (empty($data->feedback_message)) {
      return new JsonResponse([
        'message' => t('Missing message'),
      ], 400);
    }

    $feedback_id = $data->feedback_id;
    $feedback_message = $data->feedback_message;

    $feedback_controller_obj = new AdminFeedbackController($this->database, $this->requestStack, $this->languageManager, $this->eventDispatcher);
    $feedback_controller_obj->updateFeedback($feedback_id, $feedback_message);

    return new JsonResponse([
      'status' => TRUE,
      'messages' => $this->t('Feedback updated successfully')
    ], 200);
  }

}
