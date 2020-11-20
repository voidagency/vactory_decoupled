<?php

namespace Drupal\vactory_jsonapi\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Blocks per node.
 */
class InternalNodeEntityBlocksFieldItemList extends FieldItemList
{

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue()
  {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    if (!in_array($entity_type, ['node'])) {
      return;
    }

    $value = [
      'test' => "Hello world!",
    ];

    $this->list[0] = $this->createItem(0, $value);
  }
}
