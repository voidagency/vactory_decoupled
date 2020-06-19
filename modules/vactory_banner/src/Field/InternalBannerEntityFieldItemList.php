<?php

namespace Drupal\vactory_banner\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\vactory_jsonapi\Plugin\jsonapi\VactoryDynamicFieldServiceEnhancer;

/**
 * Defines a banner list class for better normalization targeting.
 */
class InternalBannerEntityFieldItemList extends FieldItemList
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

    $value = [];
    $id = $entity->get('node_banner')->getString();

    // If no banner is set on node.
    // Fallback to settings.
    if (!$id || empty($id)) {
      $node_type = NodeType::load($entity->bundle());
      $id = $node_type->getThirdPartySetting('vactory_banner', 'banner');
    }

    // Load banner.
    if ($id && !empty($id)) {
      $enhancer = new VactoryDynamicFieldServiceEnhancer();
      $node = Node::load($id);
      $node = \Drupal::service('entity.repository')
        ->getTranslationFromContext($node, \Drupal::languageManager()->getCurrentLanguage()->getId());

      // Whatever Banner is disabled for this node.
      $disabled = (bool)$entity->get('node_banner_disabled')->getString();

      if (
        $node->get('field_banner_template')->first() &&
        $node->get('field_banner_template')->first()->getValue() &&
        !$disabled
      ) {
        $value = $enhancer->doUndoTransform($node->get('field_banner_template')->first()->getValue());
      }
    }

    $this->list[0] = $this->createItem(0, $value);
  }
}
