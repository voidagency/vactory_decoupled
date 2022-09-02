<?php

namespace Drupal\vactory_sondage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class SondageApiController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function checkPoll($block_uuid) {

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    if(!in_array("authenticated", $roles))
      return new JsonResponse([
        'status' => FALSE,
        'message' => $this->t('Unauthorized user'),
      ], 401);

    $blockContent = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['uuid' => $block_uuid]);
    $blockContent = reset($blockContent);
    if(!$blockContent)
      return new JsonResponse([
        'status' => FALSE,
        'message' => $this->t('Invalid block id'),
      ], 404);

    $description = $blockContent->body->value;
    $question = $blockContent->field_sondage_question->value;
    $sondage_options = $blockContent->field_sondage_options->getValue();
    $is_closed = \Drupal::service('vactory_sondage.manager')->isSondageClosed($blockContent);
    $close_date = $blockContent->field_sondage_close_date->value;
    
    $storage_results = $blockContent->get('field_sondage_results')->value;
    $storage_results = isset($storage_results) && !empty($storage_results) ? $storage_results : '[]';
    $storage_results = json_decode($storage_results, TRUE);
    $hasVoted = $storage_results ? in_array($current_user->id(), $storage_results['all_votters']) : false;
    if ((!empty($storage_results) && $hasVoted) || $is_closed) {
      $statistics = \Drupal::service('vactory_sondage.manager')->getStatistics($blockContent);
      $pollData = array_merge([
        'description' => $description,
        'question' => $question,
        'close_date' => $close_date,
        'has_voted' => $hasVoted
      ], $statistics);
      return new JsonResponse([
        'status' => TRUE,
        'data' => $pollData,
      ]);
    }

    $options = [];
    foreach ($sondage_options as $option) {
      $key = $option['option_value'];
      if (!isset($options[$key])) {
        $type = !empty($option['option_text']) ? 'text' : 'image';
        $options[$key] = [
          'type' => $type,
          'value' => $option['option_value']
        ];
        if ($type === 'text') {
          $options[$key]['text'] = $option['option_text'];
        }
        if ($type === 'image') {
          $media = Media::load($option['option_image']);
          if ($media) {
            $fid = $media->get('field_media_image')->target_id;
            $alt = $media->get('field_media_image')->alt;
            $file = $fid ? File::load($fid) : NULL;
            $uri = '';
            if ($file) {
              $uri = $file->getFileUri();
              $alt = $media->get('field_media_image')->alt;
            }
            $options[$key]['image']['uri'] = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
            $options[$key]['image']['alt'] = $alt;
          }
        }
      }
    }
    ksort($options);
    $options = array_values($options);
    $pollData = [
      'description' => $description,
      'question' => $question,
      'close_date' => $close_date,
      'options' => $options,
    ];
    return new JsonResponse([
      'status' => TRUE,
      'data' => $pollData
    ]);
  }

  /**
   * Posts items in agenda.
   */
  public function postPollAnswer($block_uuid, Request $request) {
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    if(!in_array("authenticated", $roles))
      return new JsonResponse([
        'status' => FALSE,
        'message' => $this->t('Unauthorized user'),
      ], 401);

    //get block content
    $blockContent = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['uuid' => $block_uuid]);
    $blockContent = reset($blockContent);
    if(!$blockContent)
      return new JsonResponse([
        'status' => FALSE,
        'message' => $this->t('Invalid block id'),
      ], 404);
    $post_data = json_decode( $request->getContent(),TRUE);
    if(!isset($post_data['option_value']))
      return new JsonResponse([
        'status' => FALSE,
        'message' => $this->t('Option value required'),
      ], 400);
    //Check if option is valid
    if(!$this->findOption($post_data['option_value'], $blockContent->field_sondage_options->getValue()))
      return new JsonResponse([
        'status' => FALSE,
        'message' => $this->t('Invalid option'),
      ], 400);

    $voted_option_value = $post_data["option_value"];
    $storage_results = $blockContent->get('field_sondage_results')->value;
    $storage_results = isset($storage_results) && !empty($storage_results) ? $storage_results : '[]';
    $storage_results = json_decode($storage_results, TRUE);
    $count = isset($storage_results[$voted_option_value]) ? $storage_results[$voted_option_value]['count'] + 1 : 1;
    $storage_results[$voted_option_value]['count'] = $count;
    $storage_results[$voted_option_value]['votters'][] = $current_user->id();
    $storage_results['all_votters'][] = $current_user->id();
    $blockContent->set('field_sondage_results', json_encode($storage_results));
    $blockContent->save();
    $options_statistics = \Drupal::service('vactory_sondage.manager')->getStatistics($blockContent);
    return new JsonResponse([
      'status' => TRUE,
      'data' => $options_statistics,
    ]);
  }

  /**
   * Finds if a poll option exists.
   */
  public function findOption($option_value, $options){
    foreach ($options as $value)
    {
      if ($value["option_value"] == $option_value)
          return TRUE;
    }
    return FALSE;
  }

}
