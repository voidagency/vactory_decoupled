<?php

namespace Drupal\vactory_jsonapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionForm;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebformController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function index(Request $request) {
    $webform_data = $request->request->all();

    // Basic check for webform ID.
    if (empty($webform_data['webform_id'])) {
      return new JsonResponse([
        'error' => [
          'code'    => '400',
          'message' => 'Missing webform id',
        ],
      ], 400);
    }

    $entity_type = NULL;
    $entity_id = NULL;

    if (!empty($request->query->get('entityType')) && !empty($request->query->get('entityId'))) {
      $entity_type = $request->query->get('entityType');
      $entity_id = $request->query->get('entityId');
    }

    // Check for a valid webform.
    $webform = Webform::load($webform_data['webform_id']);
    if (!$webform) {
      return new JsonResponse([
        'error' => [
          'message' => 'Invalid webform_id value.',
        ],
      ], 400);
    }

    // Convert to webform values format.
    $values = [
      'in_draft'    => FALSE,
      'uid'         => \Drupal::currentUser()->id(),
      'uri'         => '/_webform/submit' . $webform_data['webform_id'],
      'entity_type' => $entity_type,
      'entity_id'   => $entity_id,
      // Check if remote IP address should be stored.
      'remote_addr' => $webform->hasRemoteAddr() ? $request->getClientIp() : '',
      'webform_id'  => $webform_data['webform_id'],
    ];

    // @todo: do data post here
    $values['data'] = $webform_data;

    // Don't submit webform ID.
    unset($values['data']['webform_id']);

    // Don't submit entity data.
    unset($values['data']['entityType']);
    unset($values['data']['entityId']);

    // Check if webform is open.
    $is_open = WebformSubmissionForm::isOpen($webform);

    if ($is_open !== TRUE) {
      return new JsonResponse([
        'error' => [
          'message' => 'This webform is closed, or too many submissions have been made.',
        ],
      ], 400);
    }

    $webform_submission = WebformSubmissionForm::submitFormValues($values);

    // Check if submit was successful.
    if ($webform_submission instanceof WebformSubmissionInterface) {
      return new JsonResponse([
        'sid'      => $webform_submission->id(),
        'settings' => self::getWhitelistedSettings($webform),
      ]);
    }
    else {
      // Return validation errors.
      return new JsonResponse([
        'error' => $webform_submission,
      ], 400);
    }
  }

  static private function getWhitelistedSettings(WebformInterface $webform) {
    $whitelist = [
      'confirmation_url',
      'confirmation_type',
      'confirmation_message',
      'confirmation_title',
    ];

    return array_intersect_key(
      $webform->getSettings(),
      array_flip($whitelist)
    );
  }

}
