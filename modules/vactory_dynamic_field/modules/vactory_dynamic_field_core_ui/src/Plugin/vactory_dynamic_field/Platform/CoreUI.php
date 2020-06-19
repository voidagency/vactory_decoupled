<?php

namespace Drupal\vactory_dynamic_field_core_ui\Plugin\vactory_dynamic_field\Platform;

use Drupal\vactory_dynamic_field\VactoryDynamicFieldPluginBase;

/**
 * A YouTube provider plugin.
 *
 * @PlatformProvider(
 *   id = "vactory_core_ui",
 *   title = @Translation("Core UI")
 * )
 */
class CoreUI extends VactoryDynamicFieldPluginBase {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $widgetsPath) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, drupal_get_path('module', 'vactory_dynamic_field_core_ui') . '/widgets');
  }

}
