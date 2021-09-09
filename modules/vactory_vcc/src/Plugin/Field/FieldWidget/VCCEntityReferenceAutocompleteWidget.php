<?php

namespace Drupal\vactory_vcc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\node\Entity\NodeType;

/**
 * Plugin implementation of the 'vcc_entity_reference_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "vcc_entity_reference_autocomplete",
 *   label = @Translation("Autocomplete"),
 *   description = @Translation("An autocomplete text field."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class VCCEntityReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $form_element = parent::formElement($items, $delta, $element, $form, $form_state);
    $entity_element = $form_element['target_id'];
    $entity = $items->getEntity();
    // Make sure we are targeting only this bundle.
    $entity_element['#selection_settings']['target_bundles'] = [$entity->bundle()];

    return ['target_id' => $entity_element];
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();
    $node_type = NodeType::load($entity->bundle());
    $cardinality = $node_type->getThirdPartySetting('vactory_vcc', 'limit', 3);

    // Fake cardinality without impacting storage.
    $this->fieldDefinition->getFieldStorageDefinition()->setCardinality($cardinality);
    return parent::formMultipleElements($items, $form, $form_state);
  }

}
