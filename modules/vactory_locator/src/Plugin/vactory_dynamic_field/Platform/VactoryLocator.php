<?php

namespace Drupal\vactory_locator\Plugin\vactory_dynamic_field\Platform;

use Drupal\vactory_dynamic_field\VactoryDynamicFieldPluginBase;

/**
 * A DF provider plugin.
 *
 * @PlatformProvider(
 *   id = "vactory_locator",
 *   title = @Translation("Locator Maps")
 * )
 */
class VactoryLocator extends VactoryDynamicFieldPluginBase
{

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $widgetsPath)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, drupal_get_path('module', 'vactory_locator') . '/widgets');
  }

}
