<?php

namespace Drupal\vactory_news\Plugin\vactory_dynamic_field\Platform;

use Drupal\vactory_dynamic_field\VactoryDynamicFieldPluginBase;

/**
 * A DF provider plugin.
 *
 * @PlatformProvider(
 *   id = "vactory_news",
 *   title = @Translation("News")
 * )
 */
class VactoryNews extends VactoryDynamicFieldPluginBase
{

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $widgetsPath)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, drupal_get_path('module', 'vactory_news') . '/widgets');
  }

}
