<?php

namespace Drupal\vactory_widgets_ui\Plugin\vactory_dynamic_field\Platform;

use Drupal\vactory_dynamic_field\VactoryDynamicFieldPluginBase;

/**
 * A DF provider plugin.
 *
 * @PlatformProvider(
 *   id = "vactory_widgets_ui",
 *   title = @Translation("vactory widgets ui")
 * )
 */
class VactoryWidgetsUi extends VactoryDynamicFieldPluginBase {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $widgetsPath) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, drupal_get_path('module', 'vactory_widgets_ui') . '/widgets');
  }

}
