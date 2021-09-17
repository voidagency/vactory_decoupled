<?php

namespace Drupal\vactory_icon\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\file\Entity\File;


/**
 * Provides an example element.
 *
 * @FormElement("vactory_icon_picker")
 */
class VactoryIconPicker extends FormElement
{
  /**
   * {@inheritdoc}
   */
  public function getInfo()
  {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processVactoryIconPicker'],
      ],
      '#theme' => 'select',
      '#theme_wrappers' => ['form_element'],
    ];
  }


  /**
   * @param $element
   * @param FormStateInterface $form_state
   * @param $form
   * @return mixed
   */
  public static function processVactoryIconPicker(&$element, FormStateInterface $form_state, &$form)
  {
    $element['#type'] = 'select';
    $element['#attributes'] = [
      'class' => ['vactory--icon-picker'],
    ];
    $element['#options'] = [];
    $icons = array('');

    $json_file = \Drupal::service('file_system')->realpath("public://vactory_icon/selection.json");
    $file_content = file_get_contents($json_file);
    $decoded_content = Json::decode($file_content);
    foreach ($decoded_content['icons'] as $icon) {
      array_push($icons, 'icon-' . $icon['properties']['name']);
    }

    foreach ($icons as $icon) {
      $element['#options'][$icon] = $icon;
    }

    $element['#attached']['library'][] = 'vactory_icon/vactory_icon.fonticonpicker';

    return $element;
  }


}
