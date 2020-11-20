<?php

namespace Drupal\vactory_jsonapi;

use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\node\Entity\Node;

// @todo: verify weight & order of blocks.
// @todo: verify i18n

// web/core/modules/system/src/Plugin/Condition/RequestPath.php
// web/core/tests/Drupal/KernelTests/Core/Plugin/Condition/RequestPathTest.php
// web/core/modules/node/src/Plugin/Condition/NodeType.php
// web/core/modules/node/tests/src/Kernel/NodeConditionTest.php
//     $manager = $this->container->get('plugin.manager.condition');
// web/core/modules/block/src/Entity/Block.php


/*
 *     $container = \Drupal::hasContainer() ? \Drupal::getContainer() : new ContainerBuilder();

 * try {
      $container = \Drupal::getContainer();
    }
    catch (ContainerNotInitializedException $e) {
      $container = new ContainerBuilder();
    }
    $container->set('keyvalue', $key_value_factory);
    \Drupal::setContainer($container);
 *
 *
 * */

/**
 * {@inheritdoc}
 */
class BlocksManager
{
  use ConditionAccessResolverTrait;

  /**
   * Drupal\Core\Block\BlockManagerInterface definition.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $pluginManagerBlock;

  /**
   * Drupal\Core\Theme\ThemeManagerInterface definition.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    BlockManagerInterface $block_manager,
    ThemeManagerInterface $theme_manager,
    EntityTypeManagerInterface $entity_type_manager
  )
  {
    $this->pluginManagerBlock = $block_manager;
    $this->themeManager = $theme_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function getBlocksByNode($nid)
  {
    $blocks = $this->getThemeBlocks();
    $visible_blocks = $this->getVisibleBlocks($blocks, $nid);

    return $visible_blocks;
  }

  /**
   * Access control handler for the block.
   *
   * @param $blocks
   * @param $nid
   * @return array
   */
  protected function getVisibleBlocks($blocks, $nid)
  {
    $node = Node::load($nid);
    $path = '/node/' . $nid;
    $visible_blocks = [];
    foreach ($blocks as $block) {
      $conditions = [];
      foreach ($block['visibilityConditions'] as $condition_id => $condition) {
        if ($condition_id === 'decoupled_request_path') {
          $condition->setContextValue('path', $path);
        } else if (in_array($condition_id, ['entity_bundle:node', 'node_type'])) {
          $condition->setContextValue('node', $node);
        }

        $conditions[$condition_id] = $condition;
      }

      if ($this->resolveConditions($conditions, 'and') !== FALSE) {
        unset($block['visibilityConditions']);
        $visible_blocks[] = $block;
      }
    }
    return $visible_blocks;
  }

  /**
   * Block objects list.
   *
   * @return array
   *   Blocks array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getThemeBlocks()
  {
    $name = __CLASS__ . '_' . __METHOD__;
    $blocks = &drupal_static($name, []);

    if ($blocks) {
      return $blocks;
    }

    $blocksManager = $this->entityTypeManager->getStorage('block');
    $theme = $this->themeManager->getActiveTheme()->getName();
    $conditionPluginManager = \Drupal::service('plugin.manager.condition'); // @todo: move this.
    $regions = system_region_list($theme, BlockRepositoryInterface::REGIONS_VISIBLE);

    $blocks_list = [];
    foreach ($regions as $key => $region) {
      $region_blocks = $blocksManager->loadByProperties(
        [
          'theme' => $theme,
          'region' => $key,
        ]
      );

      if (!empty($region_blocks)) {
        $region_blocks = (array)$region_blocks;
        $blocks_list = array_merge($blocks_list, $region_blocks);
      }
    }

    // @todo: get content here.
    $blocks = array_map(function ($block) use ($conditionPluginManager) {
      $visibility = $block->getVisibility();

      if (isset($visibility['request_path'])) {
        $visibility['decoupled_request_path'] = $visibility['request_path'];
        $visibility['decoupled_request_path']['id'] = 'decoupled_request_path';
        unset($visibility['request_path']);
      }

      $visibilityCollection = new ConditionPluginCollection($conditionPluginManager, $visibility);

      return [
        'id' => $block->getOriginalId(),
        'label' => $block->label(),
        'region' => $block->getRegion(),
        'plugin' => $block->getPluginId(),
        'weight' => $block->getWeight(),
        'visibilityConditions' => $visibilityCollection,
      ];
    }, $blocks_list);

    return $blocks;
  }

}
