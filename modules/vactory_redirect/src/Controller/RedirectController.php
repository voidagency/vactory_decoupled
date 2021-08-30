<?php

namespace Drupal\vactory_redirect\Controller;

use \Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use \Symfony\Component\HttpFoundation\JsonResponse;
use League\Csv\Reader;

class RedirectController extends ControllerBase
{

  public function index()
  {
    $fid = (int) \Drupal::config('vactory_redirect.settings')->get('fid');
    $file = $file = File::load($fid);
    if (!$file) {
      return new JsonResponse([
          "redirections" => []
      ]);
    }

    $csv_file = \Drupal::service('file_system')->realpath($file->getFileUri());
    $csv = Reader::createFromPath($csv_file, 'r');
    $csv->setHeaderOffset(0);
    $records = $csv->getRecords();
    $redirections = array();

    foreach ($records as $record) {
      array_push($redirections, $record);
    }

    return new JsonResponse([
        "redirections" => $redirections]
    );
  }
}
