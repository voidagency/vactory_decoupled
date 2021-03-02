<?php

namespace Drupal\vactory_ckeditor\Plugins\CKEditorPlugin;

use Drupal\ckeditor\Plugin\CKEditorPlugin\Internal;
use Drupal\editor\Entity\Editor;

/**
 * Allow custom config settings.
 *
 * @CKEditorPlugin(
 *   id = "vactory_ckeditor_custom_internal",
 *   label = @Translation("Custom CKEditor core")
 * )
 */
class CustomInternal extends Internal {

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {

    $config = parent::getConfig($editor);

    $config['bodyId'] = 'app';

    return $config;
  }

}
