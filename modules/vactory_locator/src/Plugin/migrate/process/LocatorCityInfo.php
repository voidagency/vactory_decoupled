<?php

namespace Drupal\vactory_locator\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use GuzzleHttp\Exception\RequestException;

/**
 * LocatorCityInfo Class.
 *
 * @MigrateProcessPlugin(
 *   id = "locator_city_info"
 * )
 */
class LocatorCityInfo extends ProcessPluginBase
{

  const URL = 'http://104.197.11.192/geoserver/geoloc_getvilles.php?action=getvillesagence';

  /**
   * Transform function of MigrateProcessInterface Interface.
   *
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateSkipRowException
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
  {
    // Get list of cities from web service.
    $cities = $this->getCityByIdFromService(self::URL);
    // Get the city with the id.
    $city = array_filter(
      $cities,
      function ($val) use ($value) {
        return (isset($val['id']) && $val['id'] == $value[0]);
      }
    );
    $city_info = end($city);
    return $city_info['nom'] ?? '';
  }

  /**
   * GetCityByIdFromService function.
   *
   * @param string $uri
   *   Api uri.
   *
   * @return array
   *   Return array.
   */
  public function getCityByIdFromService($uri)
  {
    try {
      $response = \Drupal::httpClient()
        ->get($uri, ['headers' => ['Accept' => 'application/json']]);
      $data = (string)$response->getBody();
      if (!empty($data)) {
        return json_decode($data, TRUE)['ville'] ?? [];
      } else {
        return [];
      }
    } catch (RequestException $e) {
      return [];
    }
  }

}
