<?php

namespace Drupal\vactory_sondage;

use Drupal\vactory_jsonapi\BlocksManager;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\jsonapi_extras\EntityToJsonApi;

/**
 * Decorates vactory_jsonapi blocks manager service.
 */
class BlocksManagerOverride extends BlocksManager {

  /**
   * Blocks manager service.
   * 
   * @var \Drupal\vactory_jsonapi\BlocksManager
   */
   protected $blocksManager;

   /**
   * Drupal\Core\Block\BlockManagerInterface definition.
   *
   * @var BlockManagerInterface
   */
  protected $pluginManagerBlock;

  /**
   * Drupal\Core\Theme\ThemeManagerInterface definition.
   *
   * @var ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The condition plugin manager.
   *
   * @var ExecutableManagerInterface
   */
  protected $conditionPluginManager;

  /**
   * The JSON:API version generator of an entity..
   *
   * @var EntityToJsonApi
   */
  protected $entityToJsonApi;

   /**
   * {@inheritdoc}
   */
  public function __construct(
    BlocksManager $blocksManager,
    BlockManagerInterface $block_manager,
    ThemeManagerInterface $theme_manager,
    EntityTypeManagerInterface $entity_type_manager,
    ExecutableManagerInterface $condition_plugin_manager,
    EntityToJsonApi $entity_to_jsonapi
  )
  {
    $this->pluginManagerBlock = $block_manager;
    $this->themeManager = $theme_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->conditionPluginManager = $condition_plugin_manager;
    $this->entityToJsonApi = $entity_to_jsonapi;
    $this->blocksManager = $blocksManager;
  }

  /**
   * {@inheritDoc}
   */
   public function getThemeBlocks() {
    $themeBlocks = parent::getThemeBlocks();
    foreach ($themeBlocks as &$themeBlock) {
      $plugin = $themeBlock['plugin'];
      list($plugin_type, $plugin_uuid) = explode(':', $plugin);
      if ($plugin_type === 'block_content') {
        $contentBlock = $this->entityTypeManager->getStorage('block_content')->loadByProperties(['uuid' => $plugin_uuid]);
        if (!empty($contentBlock)) {
          if (is_array($contentBlock)) {
            $contentBlock = reset($contentBlock);
            if($contentBlock->bundle() === "vactory_sondage"){
              $themeBlock['content']['widget_data'] = [
                'uuid' => $plugin_uuid,
                // 'description' => $contentBlock->body->value,
                // 'status' => $contentBlock->field_sondage_status->value,
                // 'question' => $contentBlock->field_sondage_question->value,
                // 'options' => $contentBlock->field_sondage_options->getValue(),
                // 'close_date' => $contentBlock->field_sondage_close_date->value,
                // 'results' => $contentBlock->field_sondage_results->getValue(),
              ];
            }
          }
        }
      }
    };
     return $themeBlocks;
   }

}