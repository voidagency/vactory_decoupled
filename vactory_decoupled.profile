<?php

/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

use Drupal\vactory_decoupled\Plugin\Contenta\OptionalModule\AbstractOptionalModule;
use Drupal\Core\Serialization\Yaml;
use Drupal\language\Entity\ConfigurableLanguage;


/**
 * Implements hook_form_alter().
 */
function vactory_decoupled_form_alter(&$form, &$form_state, $form_id)
{
  switch ($form_id) {
    case 'install_configure_form':

      // Pre-populate site email address.
      $form['site_information']['site_name']['#default_value'] = 'Vactory';
      $form['site_information']['site_mail']['#default_value'] = 'admin@void.fr';

      // Pre-populate username.
      $form['admin_account']['account']['name']['#default_value'] = 'admin';

      // Pre-populate admin email.
      $form['admin_account']['account']['mail']['#default_value'] = 'admin@void.fr';

      // Disable notifications.
      $form['update_notifications']['update_status_module']['#default_value'][1] = 0;
      break;
  }
}

/**
 * Implements hook_install_tasks_alter().
 */
function vactory_decoupled_install_tasks_alter(&$tasks, $install_state)
{
//  $tasks['install_select_language']['display'] = FALSE;
//  $tasks['install_select_language']['run'] = INSTALL_TASK_SKIP;
  $tasks['install_finished']['function'] = 'vactory_decoupled_after_install_finished';
}

/**
 * Implements hook_install_tasks().
 */
function vactory_decoupled_install_tasks(&$install_state)
{
//  $tasks['_vactory_decoupled_enable_cors'] = [
//    'display_name' => t('Enable CORS by default'),
//  ];

  $tasks['vactory_decoupled_configure_multilingual'] = [
    'display_name' => t('Configure multilingual'),
    'display' => TRUE,
    'type' => 'batch',
  ];

//  $tasks['vactory_decoupled_install_core_modules'] = [
//    'display_name' => t('Install core modules'),
//    'display' => TRUE,
//    'type' => 'batch',
//  ];

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


function vactory_decoupled_install_core_modules(array &$install_state)
{
  $batch = [
    'title' => 'Install profile modules',
    'operations' => [],
    'error_message' => t('The installation has encountered an error.'),
  ];

  $modules = [
    'vactory_user' => 'User'
  ];
  $files = \Drupal::service('extension.list.module')->getList();

  // Always install required modules first. Respect the dependencies between
  // the modules.
  $required = [];
  $non_required = [];

  // Add modules that other modules depend on.
  foreach ($modules as $module) {
    if ($files[$module]->requires) {
      $modules = array_merge($modules, array_keys($files[$module]->requires));
    }
  }
  $modules = array_unique($modules);
  foreach ($modules as $module) {
    if (!empty($files[$module]->info['required'])) {
      $required[$module] = $files[$module]->sort;
    }
    else {
      $non_required[$module] = $files[$module]->sort;
    }
  }
  arsort($required);
  arsort($non_required);

  foreach ($required + $non_required as $module => $weight) {
    $batch['operations'][] = [
      'vactory_install_module',
      [$module, $files[$module]->info['name']],
    ];
  }

  return $batch;
}

function vactory_install_module($module, $module_name, &$context)
{
  \Drupal::service('module_installer')->install([$module], FALSE);
  $context['results'][] = $module;
  $context['message'] = t('Installed %module module.', ['%module' => $module_name]);
}

/**
 * Batch job to configure multilingual components.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   The batch job definition.
 */
function vactory_decoupled_configure_multilingual(array &$install_state)
{
  $batch = [];

  // Add all selected languages.
  foreach (['ar'] as $language_code) {
    $batch['operations'][] = [
      'vactory_configure_language_and_fetch_traslation',
      (array)$language_code,
    ];
  }

  return $batch;
}


/**
 * Batch function to add selected languages then fetch all translation.
 *
 * @param string|array $language_code
 *   Language code to install and fetch all translation.
 */
function vactory_configure_language_and_fetch_traslation($language_code)
{
  ConfigurableLanguage::createFromLangcode($language_code)->save();
}

/**
 * Installs the vactory_decoupled modules.
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
