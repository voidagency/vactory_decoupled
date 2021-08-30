<?php
/**
 * Created by PhpStorm.
 * User: ismail
 * Date: 30/03/2021
 * Time: 13:18
 */

namespace Drupal\vactory_redirect\Controller;

use \Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RedirectController extends ControllerBase
{

    public function index()
    {
        $dir = \Drupal::service('file_system')->realpath("public://vactory_redirect");
        $scanned_directory = array_diff(scandir($dir,1), array('..', '.'));
        $csv_file = \Drupal::service('file_system')->realpath("public://vactory_redirect/".$scanned_directory[0]);
        $file_content = file_get_contents($csv_file);
        $array = array_map("str_getcsv", explode("\n", $file_content));
        $redirections = array();
        for ($i = 1; $i < count($array); ++$i) {
            $item = array(
                $array[0][0] => $array[$i][0],
                $array[0][1] => $array[$i][1],
                $array[0][2] => $array[$i][2],
            );
            array_push($redirections, $item);
        }

        return new JsonResponse([
            "redirections" => $redirections]
        );
    }
}