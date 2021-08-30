<?php


namespace Drupal\vactory_tour\Plugin\vactory_dynamic_field\Platform;

use Drupal\vactory_dynamic_field\VactoryDynamicFieldPluginBase;
/**
 * A DF provider plugin.
 *
 * @PlatformProvider(
 *   id = "vactory_tour",
 *   title = @Translation("Tour")
 * )
 */
class Tour extends VactoryDynamicFieldPluginBase
{

    public function __construct(array $configuration, $plugin_id, $plugin_definition, $widgetsPath) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, drupal_get_path('module', 'vactory_tour') . '/widgets');
      }

}