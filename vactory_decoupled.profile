<?php

/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

use Drupal\vactory_decoupled\Plugin\Contenta\OptionalModule\AbstractOptionalModule;
use Drupal\Core\Serialization\Yaml;

/**
 * Implements hook_install_tasks_alter().
 */
function vactory_decoupled_install_tasks_alter(&$tasks, $install_state)
{
  $tasks['install_finished']['function'] = 'vactory_decoupled_after_install_finished';
}

/**
 * Implements hook_install_tasks().
 */
function vactory_decoupled_install_tasks(&$install_state)
{
  $tasks['_vactory_decoupled_enable_cors'] = [
    'display_name' => t('Enable CORS by default'),
  ];

  $tasks['vactory_decoupled_module_configure_form'] = [
    'display_name' => t('Configure additional modules'),
    'type' => 'form',
    'function' => 'Drupal\vactory_decoupled\Installer\Form\ModuleConfigureForm',
  ];

  $tasks['vactory_decoupled_module_install'] = [
    'display_name' => t('Install additional modules'),
  ];

  return $tasks;
}

/**
 * Installs the contenta_jsonapi modules.
 *
 * @param array $install_state
 *   The install state.
 */
function vactory_decoupled_module_install(array &$install_state)
{
  set_time_limit(0);

  $extensions = $install_state['forms']['vactory_decoupled_additional_modules'];
  $form_values = $install_state['forms']['form_state_values'];

  $optional_modules_manager = \Drupal::service('plugin.manager.vactory_decoupled.optional_modules');
  $definitions = array_map(function ($extension_name) use ($optional_modules_manager) {
    return $optional_modules_manager->getDefinition($extension_name);
  }, $extensions);
  $modules = array_filter($definitions, function (array $definition) {
    return $definition['type'] == 'module';
  });
  $themes = array_filter($definitions, function (array $definition) {
    return $definition['type'] == 'theme';
  });
  if (!empty($modules)) {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $installer */
    $installer = \Drupal::service('module_installer');
    $installer->install(array_map(function (array $module) {
      return $module['id'];
    }, $modules));
  }
  if (!empty($themes)) {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $installer */
    $installer = \Drupal::service('theme_installer');
    $installer->install(array_map(function (array $theme) {
      return $theme['id'];
    }, $themes));
  }
  $instances = array_map(function ($extension_name) use ($optional_modules_manager) {
    return $optional_modules_manager->createInstance($extension_name);
  }, $extensions);
  array_walk($instances, function ($instance) use ($form_values) {
    $instance->submitForm($form_values);
  });
}

/**
 * Alters the services.yml to enable CORS by default.
 */
function _vactory_decoupled_enable_cors()
{
  // Enable CORS for localhost.
  /** @var \Drupal\Core\DrupalKernelInterface $drupal_kernel */
  $drupal_kernel = \Drupal::service('kernel');
  $file_path = $drupal_kernel->getAppRoot() . '/' . $drupal_kernel->getSitePath();
  $filename = $file_path . '/services.yml';
  if (file_exists($filename)) {
    $services_yml = file_get_contents($filename);

    $yml_data = Yaml::decode($services_yml);
    if (empty($yml_data['parameters']['cors.config']['enabled'])) {
      $yml_data['parameters']['cors.config']['enabled'] = TRUE;
      $yml_data['parameters']['cors.config']['allowedHeaders'] = ['*'];
      $yml_data['parameters']['cors.config']['allowedMethods'] = ['*'];
      $yml_data['parameters']['cors.config']['allowedOrigins'] = ['localhost'];
      $yml_data['parameters']['cors.config']['allowedOriginsPatterns'] = ['/localhost:\d+/'];

      file_put_contents($filename, Yaml::encode($yml_data));
    }
  }
}

/**
 * Vactory after install finished.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   A renderable array with a redirect header.
 */
function vactory_decoupled_after_install_finished(array &$install_state)
{
  global $base_url;

  // After install direction.
  $after_install_direction = $base_url . '/?welcome';

  install_finished($install_state);

  // Clear all messages.
  /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
  $messenger = \Drupal::service('messenger');
  $messenger->deleteAll();

  // Success message.
  $messenger->addMessage(t('Congratulations, you have installed Vactory!'));

  // Run a complete cache flush.
  drupal_flush_all_caches();

  $output = [
    '#title' => t('Vactory'),
    'info' => [
      '#markup' => t('<p>Congratulations, you have installed Vactory!</p><p>If you are not redirected to the front page in 5 seconds, Please <a href="@url">click here</a> to proceed to your installed site.</p>', [
        '@url' => $after_install_direction,
      ]),
    ],
    '#attached' => [
      'http_header' => [
        ['Cache-Control', 'no-cache'],
      ],
    ],
  ];

  $meta_redirect = [
    '#tag' => 'meta',
    '#attributes' => [
      'http-equiv' => 'refresh',
      'content' => '5;url=' . $after_install_direction,
    ],
  ];
  $output['#attached']['html_head'][] = [$meta_redirect, 'meta_redirect'];

  return $output;
}
