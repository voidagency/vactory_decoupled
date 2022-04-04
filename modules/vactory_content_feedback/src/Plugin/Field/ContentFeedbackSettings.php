<?php

namespace Drupal\vactory_content_feedback\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Content feedback settings.
 */
class ContentFeedbackSettings extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {

    $entity = $this->getEntity();

    if ($entity->isNew()) {
      return;
    }

    $admin_feedback_block = \Drupal\block\Entity\Block::load("adminfeedbackblock");
    if(!$admin_feedback_block)
      return;

    $block_negate_condition = $admin_feedback_block->getVisibility()["entity_bundle:node"]["negate"];
    $block_bundles = $admin_feedback_block->getVisibility()["entity_bundle:node"]["bundles"];

    // Check if bundle is enabled in block config
    if(!$block_negate_condition && !in_array($entity->bundle(), $block_bundles))
      return;
    else if ($block_negate_condition && in_array($entity->bundle(), $block_bundles))
      return;

    // Get Admin Feedback configuration
    $feedback_config = \Drupal::config("admin_feedback.settings")->getOriginal();

    $result = $feedback_config;

    $this->list[0] = $this->createItem(0, $result);
  }

}
