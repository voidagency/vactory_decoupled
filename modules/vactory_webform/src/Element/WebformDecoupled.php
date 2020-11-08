<?php

namespace Drupal\vactory_webform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\webform\Entity\Webform;

/**
 * Provide a Webform form element for dynamic field.
 *
 * @FormElement("webform_decoupled")
 */
class WebformDecoupled extends FormElement
{

  /**
   * {@inheritDoc}
   */
  public function getInfo()
  {
    $class = get_class($this);

    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processElement'],
      ],
      '#element_validate' => [
        [$class, 'validateElement'],
      ],
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Element process callback.
   */
  public static function processElement(array &$element, FormStateInterface $form_state, array &$complete_form)
  {
    $element['webform_id'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => t('Webform'),
      '#options' => self::getWebforms(),
      '#default_value' => $element['#default_value']['webform_id'] ?? '',
    ];

    return $element;
  }

  /**
   * Form element validate callback.
   */
  public static function validateElement(&$element, FormStateInterface $form_state, &$form)
  {
    $webform_id = $element['webform_id']['#value'];
    $webform = Webform::load($webform_id);
    if (!$webform) {
      $form_state->setError($element['webform_id'], t("Webform ID @webform_id is not valid.", ['@webform_id' => $webform_id]));
    }
  }

  /**
   * The webforms list to use in options.
   *
   * @return array
   *   The webforms list.
   */
  protected static function getWebforms(): array
  {
    $forms_options = [];
    $styles = \Drupal::entityTypeManager()->getStorage('webform')->loadMultiple();

    foreach ($styles as $webform) {
      $forms_options[$webform->id()] = $webform->label();
    }

    return $forms_options;
  }

}
