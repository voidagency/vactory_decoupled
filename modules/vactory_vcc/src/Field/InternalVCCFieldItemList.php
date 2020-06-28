<?php

namespace Drupal\vactory_vcc\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\Core\Url;
use Drupal\node\Entity\NodeType;

/**
 * Defines a vcc list class for better normalization targeting.
 */
class InternalVCCFieldItemList extends FieldItemList
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
    $node_type = NodeType::load($entity->bundle());
    $isEnabled = $node_type->getThirdPartySetting('vactory_vcc', 'enabled', false);
    $limit = $node_type->getThirdPartySetting('vactory_vcc', 'limit', 3);
    $link_more = $node_type->getThirdPartySetting('vactory_vcc', 'more_link', NULL);
    $taxonomy_field = $node_type->getThirdPartySetting('vactory_vcc', 'taxonomy', 'none');
    $value = [];

    if ($entity_type !== 'node' || !$isEnabled || !$entity->hasField('vcc')) {
      return $value;
    }

    // Format link.
    if ($link_more && !empty($link_more)) {
      $link_more = str_replace('/backend', '', Url::fromUserInput($link_more)->toString());
    } else {
      $link_more = NULL;
    }

    // Look for values in node.
    $ids_raw = $entity->get('vcc')->getValue();
    $ids = array_map(function ($item) {
      return intval($item['target_id']);
    }, $ids_raw);

    // If no manual values are set on node.
    // Fallback to query.
    if (!$ids || empty($ids)) {
      $nids = \Drupal::entityQuery('node')
        ->condition('type', $entity->bundle())
        ->condition('nid', $entity->id(), '<>')
        ->condition('langcode', $entity->language()->getId())
        ->range(0, intval($limit));

      // Look for nodes with the same term.
      if ($taxonomy_field !== 'none' && $entity->hasField($taxonomy_field)) {
        $tids = $entity->get($taxonomy_field)->getValue();
        $tids = array_map(function ($item) {
          return intval($item['target_id']);
        }, $tids);
        $nids->condition($taxonomy_field, $tids, 'in');
      }

      $nids = $nids->execute();
      $ids = array_map('intval', array_values($nids));
    }

    $value = [
      'ids' => $ids,
      'link_more' => $link_more,
      'limit' => intval($limit),
    ];

    $this->list[0] = $this->createItem(0, $value);
  }
}
