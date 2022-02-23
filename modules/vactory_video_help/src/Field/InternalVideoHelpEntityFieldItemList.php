<?php

namespace Drupal\vactory_video_help\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\vactory_jsonapi\Plugin\jsonapi\VactoryDynamicFieldServiceEnhancer;

/**
 * Defines a video help list class for better normalization targeting.
 */
class InternalVideoHelpEntityFieldItemList extends FieldItemList
{

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue()
  {
    /** @var \Drupal\node\NodeInterface $node */
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityTypeId();

    if ($entity_type !== 'node') {
      return;
    }

    $node_type = NodeType::load($entity->bundle());
    $is_enabled = $node_type->getThirdPartySetting('vactory_video_help', 'is_enabled_for_type');

    if (!$is_enabled) {
      return;
    }

    $value = [];
    $id = $entity->get('node_video_help')->getString();

    // If no video help is set on node.
    // Fallback to settings.
    if (!$id || empty($id)) {
      $id = $node_type->getThirdPartySetting('vactory_video_help', 'video_help');
    }

    // Load video help.
    if ($id && !empty($id)) {
      $enhancer = new VactoryDynamicFieldServiceEnhancer();
      $node = Node::load($id);
      $node = \Drupal::service('entity.repository')
        ->getTranslationFromContext($node, \Drupal::languageManager()->getCurrentLanguage()->getId());

      // Whatever video help is disabled for this node.
      $disabled = (bool)$entity->get('node_video_help_disabled')->getString();

      if (
        $node->get('field_video_help_template')->first() &&
        $node->get('field_video_help_template')->first()->getValue() &&
        !$disabled
      ) {
        $value = $enhancer->doUndoTransform($node->get('field_video_help_template')->first()->getValue());
      }
    }

    $this->list[0] = $this->createItem(0, $value);
  }
}
