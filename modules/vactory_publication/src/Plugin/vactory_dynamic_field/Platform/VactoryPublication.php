<?php

namespace Drupal\vactory_publication\Plugin\vactory_dynamic_field\Platform;

use Drupal\vactory_dynamic_field\VactoryDynamicFieldPluginBase;

/**
 * A DF provider plugin.
 *
 * @PlatformProvider(
 *   id = "vactory_publication",
 *   title = @Translation("Publication")
 * )
 */
class VactoryPublication extends VactoryDynamicFieldPluginBase {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $widgetsPath) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, drupal_get_path('module', 'vactory_publication') . '/widgets');
  }

}
