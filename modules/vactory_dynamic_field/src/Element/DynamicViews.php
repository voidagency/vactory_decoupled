<?php

namespace Drupal\vactory_dynamic_field\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\views\Views;

/**
 * Provide a Views form element for dynamic field.
 *
 * @FormElement("dynamic_views")
 */
class DynamicViews extends FormElement
{

  const DELIMITER = ',';

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
    $element['#tree'] = TRUE;

    $element['views_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('View ID'),
      '#default_value' => $element['#default_value']['views_id'] ?? '',
    ];

    $element['views_display_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('View Display ID'),
      '#default_value' => $element['#default_value']['views_display_id'] ?? '',
    ];

    $element['limit'] = [
      '#type' => 'number',
      '#title' => t('Limit'),
      '#default_value' => $element['#default_value']['limit'] ?? '',
    ];

    $element['args'] = [
      '#type' => 'textfield',
      '#title' => t('Args'),
      '#description' => t('Provide a comma separated list of arguments to pass to the view.'),
      '#default_value' => $element['#default_value']['args'] ? self::convertArraytoCommaValue($element['#default_value']['args']) : '',
    ];

    $element['fields'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => t('Fields'),
      '#description' => self::allowedValuesDescription(),
      '#default_value' => $element['#default_value']['fields'] ? self::allowedValuesString($element['#default_value']['fields']) : '',
    ];

    $element['vocabularies'] = [
      '#type' => 'checkboxes',
      '#title' => t('Exposed Vocabularies'),
      '#description' => t('Terms in these vocabularies will be exposed by the API.'),
      '#options' => self::getVocabularyBundles(),
      '#default_value' => $element['#default_value']['vocabularies'] ?? '',
    ];

    $element['image_styles'] = [
      '#type' => 'checkboxes',
      '#title' => t('Image Styles'),
      '#description' => t('Image styles to be applied on image fields.'),
      '#options' => self::getImageStyles(),
      '#default_value' => $element['#default_value']['image_styles'] ?? '',
    ];

    return $element;
  }

  /**
   * Form element validate callback.
   */
  public static function validateElement(&$element, FormStateInterface $form_state, &$form)
  {
    $views_name = $element['views_id']['#value'];
    $views_display_name = $element['views_display_id']['#value'];
    $fields = $element['fields']['#value'];
    $view = Views::getView($views_name);
    if (!$view) {
      $form_state->setError($element['views_id'], t("Views ID @views_id is not valid.", ['@views_id' => $views_name]));
    }

    if ($view && !$view->access($views_display_name)) {
      $form_state->setError($element['views_display_id'], t("Views Display ID @views_display_id is not valid.", ['@views_display_id' => $views_display_name]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state)
  {
    if ($input !== FALSE && $input !== NULL) {
      // Upon save convert comma separated to array.
      if (isset($input['args']) && !empty($input['args'])) {
        $input['args'] = array_map('trim', explode(self::DELIMITER, $input['args']));
      }

      if (isset($input['fields']) && !empty($input['fields'])) {
        $values = [];

        $list = explode("\n", $input['fields']);
        $list = array_map('trim', $list);
        $list = array_filter($list, 'strlen');
        foreach ($list as $position => $text) {
          // Check for an explicit key.
          $matches = [];
          if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
            // Trim key and value to avoid unwanted spaces issues.
            $key = trim($matches[1]);
            $value = trim($matches[2]);
            $values[$key] = $value;
          }
        }

        $input['fields'] = $values;
      }


    }

    return is_array($input) ? $input : $element['#default_value'];
  }

  /**
   * Convert array to comma separated string.
   *
   * @param array $values
   * @return string
   */
  protected static function convertArraytoCommaValue($values)
  {
    $values = array_map(static function ($item): ?string {
      return $item ?? NULL;
    }, (array)$values);
    $values = array_filter($values);

    return implode(self::DELIMITER, $values);
  }

  /**
   * Generates a string representation of an array.
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  protected static function allowedValuesString($values)
  {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }

    return implode("\n", $lines);
  }

  /**
   * {@inheritdoc}
   */
  protected static function allowedValuesDescription()
  {
    $description = '<p>' . t('The possible values this field can contain. Enter one value per line, in the format key|label.');
    $description .= '<br/>' . t('The key is the stored value, and must be numeric. The label will be used in displayed values and edit forms.');
    $description .= '</p>';
    return $description;
  }


  /**
   * The taxonomy terms bundle list to use in checkboxes options.
   *
   * @return array
   *   The taxonomy terms bundle list.
   */
  protected static function getVocabularyBundles(): array
  {
    $bundle_options = [];
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('taxonomy_term');
    foreach ($bundles as $bundle_id => $bundle) {
      $bundle_options[$bundle_id] = $bundle['label'];
    }

    return $bundle_options;
  }

  /**
   * The image styles list to use in checkboxes options.
   *
   * @return array
   *   The image styles list.
   */
  protected static function getImageStyles(): array
  {
    $bundle_options = [];
    $styles = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple();

    foreach ($styles as $style_id => $style) {
      $bundle_options[$style_id] = $style->label();
    }

    return $bundle_options;
  }

}
