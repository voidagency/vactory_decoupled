<?php

namespace Drupal\vactory_video_help\Plugin\vactory_dynamic_field\Platform;

use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\vactory_dynamic_field\VactoryDynamicFieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A DF provider plugin.
 *
 * @PlatformProvider(
 *   id = "vactory_video_help",
 *   title = @Translation("Video help")
 * )
 */
class VideoHelp extends VactoryDynamicFieldPluginBase {

  /**
   * Extension path resolver service.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * {@inheritDoc }
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->extensionPathResolver = $container->get('extension.path.resolver');
    $instance->setWidgetsPath($instance->extensionPathResolver->getPath('module', 'vactory_video_help') . '/widgets');
    return $instance;
  }

}
