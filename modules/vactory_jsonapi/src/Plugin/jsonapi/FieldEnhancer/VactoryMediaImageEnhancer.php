<?php

namespace Drupal\vactory_jsonapi\Plugin\jsonapi\FieldEnhancer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\jsonapi_extras\Plugin\ResourceFieldEnhancerBase;
use Shaper\Util\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Use for internal media field value.
 *
 * @ResourceFieldEnhancer(
 *   id = "vactory_media_image",
 *   label = @Translation("Vactory for images (media field only)"),
 *   description = @Translation("Use for internal media field.")
 * )
 */
class VactoryMediaImageEnhancer extends ResourceFieldEnhancerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doUndoTransform($data, Context $context) {
//    if (
//      isset($data['type']) &&
//      $data['type'] === 'file--image'
//    ) {
//      $image_app_base_url = Url::fromUserInput('/app-image/')
//        ->setAbsolute()->toString();
//      $lqipImageStyle = ImageStyle::load('lqip');
//
//      $medias = \Drupal::entityTypeManager()
//        ->getStorage('file')
//        ->loadByProperties(['uuid' => $data['id']]);
//      $media = reset($medias);
//
      $uri = $media->getFileUri();

      $data['meta'] = [
        '_default'  => file_create_url($uri),
        '_lqip'     => $lqipImageStyle->buildUrl($uri),
        'uri'       => file_uri_target($uri),
        'fid'       => $media->thumbnail->entity->fid->value,
        'file_name' => $media->label(),
        'base_url'  => $image_app_base_url,
      ];
//    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function doTransform($value, Context $context) {
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOutputJsonSchema() {
    return [
      'type' => 'object',
    ];
  }

}
