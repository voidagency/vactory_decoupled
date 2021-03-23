<?php

namespace Drupal\vactory_jsonapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class FrequentSearchesController
 *
 * @package Drupal\vactory_jsonapi\Controller
 */
class FrequentSearchesController extends ControllerBase {

  /**
   * Display the frequent searches.
   *
   * @return array
   *   Return frequent searches array.
   */
  public function index() {
    $keywords = \Drupal::service('vactory_frequent_searches.frequent_searches_controller')
      ->fetchKeywordsWithResultsFromDatabase();
    $count = count($keywords);
    return new JsonResponse([
      'keywords' => $keywords,
      'count' => $count,
      'status' => 200
    ]);
  }

}