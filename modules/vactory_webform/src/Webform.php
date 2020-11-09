<?php

namespace Drupal\vactory_webform;


use Drupal\webform\Element\WebformTermReferenceTrait;

/**
 * Simplifies the process of generating an API version of a webform.
 *
 * @api
 */
class Webform
{
  use WebformTermReferenceTrait;

  /**
   * Return the requested entity as an structured array.
   *
   * @param $webform_id
   * @return array
   *   The JSON structure of the requested resource.
   */
  public function normalize($webform_id)
  {
    $webform = \Drupal\webform\Entity\Webform::load($webform_id);
    $elements = $webform->getElementsInitialized();
    return $this->itemsToSchema($elements);
  }

  /**
   * Creates a JSON Schema out of Webform Items.
   *
   * @param $items
   * @return array
   */
  public function itemsToSchema($items)
  {
    $schema = [];

    foreach ($items as $key => $item) {
      if ($key === 'actions') {
        continue;
      }
      $schema[$key] = $this->itemToUiSchema($items, $item);
    }

    return $schema;
  }

  /**
   * Creates a UI Schema out of a Webform Item.
   *
   * @param $items
   * @param $item
   * @return array
   */
  private function itemToUiSchema($items, $item)
  {
    $properties = [];
    if (isset($item['#required']) || isset($item['#pattern'])) {
      $properties['validation'] = [];
    }

    // @todo: webform_terms_of_service

    $types = [
      'textfield' => 'text',
      'email' => 'text',
      'webform_email_confirm' => 'text',
      'url' => 'text',
      'tel' => 'text',
      'hidden' => 'text',
      'number' => 'number',
      'textarea' => 'textArea',
      'captcha' => 'captcha',
      'checkbox' => 'checkbox',
      'webform_terms_of_service' => 'checkbox',
      'select' => 'select',
      'webform_select_other' => 'select',
      'webform_term_select' => 'select',
      'radios' => 'radios',
      'webform_radios_other' => 'radios',
      'checkboxes' => 'checkboxes',
      'webform_buttons' => 'checkboxes',
      'webform_buttons_other' => 'checkboxes',
      'webform_checkboxes_other' => 'checkboxes',
    ];

    $htmlInputTypes = [
      'tel' => 'tel',
      'hidden' => 'hidden'
    ];

    $type = $item['#type'];
    $ui_type = $types[$type];
    $properties['type'] = $ui_type;
    (isset($item['#title']) && !is_null($item['#title'])) ? $properties['label'] = $item['#title'] : NULL;
    (isset($item['#placeholder']) && !is_null($item['#placeholder'])) ? $properties['placeholder'] = (string)t($item['#placeholder']) : NULL;
    (isset($item['#description']) && !is_null($item['#description'])) ? $properties['helperText'] = (string)t($item['#description']) : NULL;
    (isset($item['#readonly']) && !is_null($item['#readonly'])) ? $properties['readOnly'] = $item['#readonly'] : NULL;
    (isset($htmlInputTypes[$type]) && !is_null($htmlInputTypes[$type])) ? $properties['htmlInputType'] = $htmlInputTypes[$type] : NULL;
    (isset($item['#options']) && !is_null($item['#options'])) ? $properties['options'] = $this->formatOptions($item['#options'] ?? []) : NULL;
    (isset($item['#empty_option']) && !is_null($item['#empty_option'])) ? $properties['emptyOption'] = (string)t($item['#empty_option']) : NULL;
    (isset($item['#empty_value']) && !is_null($item['#empty_value'])) ? $properties['emptyValue'] = $item['#empty_value'] : NULL;
    (isset($item['#options_display']) && !is_null($item['#options_display'])) ? $properties['optionsDisplay'] = $item['#options_display'] : NULL;
    (isset($item['#options_all']) && !is_null($item['#options_all'])) ? $properties['optionsAll'] = $item['#options_all'] : NULL;
    (isset($item['#options_none']) && !is_null($item['#options_none'])) ? $properties['optionsNone'] = $item['#options_none'] : NULL;

    if (isset($item['#required'])) {
      $properties['validation']['required'] = TRUE;
      (isset($item['#required_error']) && !is_null($item['#required_error'])) ? $properties['validation']['requiredError'] = (string)t($item['#required_error']) : NULL;
    }

    if (isset($item['#pattern'])) {
      $properties['validation']['pattern'] = $item['#pattern'];
      (isset($item['#pattern_error']) && !is_null($item['#pattern_error'])) ? $properties['validation']['patternError'] = $item['#pattern_error'] : NULL;
    }

    if (isset($item['#min'])) {
      $properties['validation']['min'] = $item['#min'];
    }
    if (isset($item['#max'])) {
      $properties['validation']['max'] = $item['#max'];
    }

    if (
      $type === 'email' ||
      $type === 'webform_email_confirm'
    ) {
      if (!isset($properties['validation']['pattern'])) {
        $properties['validation']['pattern'] = "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i";
        $properties['validation']['patternError'] = (string)t("Le champ @field n'est pas valide", [
          '@field' => $properties['label']
        ]);
      }
    }

    if ($type === 'webform_email_confirm') {
      foreach ($items as $key => $element) {
        if (isset($element['#type']) && $element['#type'] === 'email') {
          $properties['validation']['sameAs'] = $key;
          $properties['validation']['sameAsError'] = (string)t("Le champ @field n'est pas valide", [
            '@field' => $properties['label']
          ]);
          break;
        }
      }
    }

    if ($type === 'webform_term_select') {
      if (empty($item['#vocabulary'])) {
        $properties['options'] = [];
      }

      if (!empty($element['#breadcrumb'])) {
        $properties['options'] = $this->formatOptions(static::getOptionsBreadcrumb($item, ''));
      } else {
        $properties['options'] = $this->formatOptions(static::getOptionsTree($item, ''));
      }
    }

    if (
      isset($properties['emptyOption']) &&
      !empty($properties['emptyOption']) &&
      !empty($properties['options'])
    ) {
      $emptyOption = [
        'label' => $properties['emptyOption'],
        'value' => ''
      ];

      if (
        isset($properties['emptyValue']) &&
        !empty($properties['emptyValue'])
      ) {
        $emptyOption['value'] = $properties['emptyValue'];
      }

      array_unshift($properties['options'], $emptyOption);
    }

    if ($type === 'captcha') {
      $properties['validation']['required'] = TRUE;
    }

    return $properties;
  }

  /**
   * @param $items
   * @param bool $reverse
   * @return array
   */
  private function formatOptions($items, $reverse = FALSE)
  {
    $options = [];

    foreach ($items as $value => $label) {
      array_push($options, [
        'value' => $value,
        'label' => $label,
      ]);
    }

    return $options;
  }


}
