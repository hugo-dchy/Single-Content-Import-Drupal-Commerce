<?php
namespace Drupal\single_content_sync_commerce\Plugin\SingleContentSyncBaseFieldsProcessor;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\single_content_sync\ContentExporterInterface;
use Drupal\single_content_sync\ContentImporterInterface;
use Drupal\single_content_sync\SingleContentSyncBaseFieldsProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Plugin implementation for user base fields processor plugin.
 *
 * @SingleContentSyncBaseFieldsProcessor(
 *   id = "commerce_product",
 *   label = @Translation("Commerce product base fields processor"),
 *   entity_type = "commerce_product",
  * )
 */
class CommerceProduct extends SingleContentSyncBaseFieldsProcessorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The content exporter service.
   *
   * @var \Drupal\single_content_sync\ContentExporterInterface
   */
  protected ContentExporterInterface $exporter;

  /**
   * The content importer service.
   *
   * @var \Drupal\single_content_sync\ContentImporterInterface
   */
  protected ContentImporterInterface $importer;

    /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected EntityRepositoryInterface $entityRepository;

  /**
   * Constructs new TaxonomyTerm plugin instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\single_content_sync\ContentExporterInterface $exporter
   *   The content exporter service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                        ContentExporterInterface $exporter, ContentImporterInterface $importer,
                        EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->exporter = $exporter;
    $this->importer = $importer;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('single_content_sync.exporter'),
      $container->get('single_content_sync.importer'),
      $container->get('entity.repository'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function exportBaseValues(FieldableEntityInterface $entity): array {

    $base_fields = [
      'title' => $entity->get('title')->value,
      'status' => $entity->isPublished(),
      'variations' => $entity->getVariations(),
      // FIXME get stores via uuid
      'stores' => $entity->getStoreIds(),
      'langcode' => $entity->language()->getId(),
      'path' => $entity->hasField('path') ? $entity->get('path')->alias : NULL,
    ];

    // get variations
    $variations = null;
    foreach ($entity->getVariations() as $index => $variation){
      $varray = $this->exporter->doExportToArray($variation);
      $varray['base_fields']['product_uuid'] = $entity->get('uuid')->value;
      $variations[$index] = $varray;
    }
    $base_fields['variations'] = $variations;

    // get stores uuid
    $stores = null;
    foreach ($entity->getStores() as $index => $store){
      $stores[$index] = array('id' => $store->id(),
                         'uuid' => $store->uuid(),
                         'name' => $store->getName(),
                        );
    }
    $base_fields['stores'] = $stores;

    return $base_fields;
  }

  /**
  * {@inheritdoc}
  */
  public function mapBaseFieldsValues(array $values): array {

    $entity = [
      'title' => $values['title'],
      'status' => $values['status'],
      'langcode' => $values['langcode'],
      'variations' => $values['variations'],
      'stores' => $values['stores'],
      'path' => $values['path'],
    ];

    $variations_ids = [];
    // get variations
    if (!empty($values['variations'])) {
      foreach ($entity['variations'] as &$variation) {
        // If the entity was fully exported we do the full import.
        if ($this->importer->isFullEntity($variation)) {
          $imported = $this->importer->doImport($variation);
          $variations_ids[] = array('target_id' => $imported->id());
        }
      }
    }
    $entity['variations'] = empty($variations_ids) ? null : $variations_ids;

    $store_ids = [];
    // set stores
    if (!empty($values['stores'])) {
      foreach ($entity['stores'] as &$store) {
        // If the entity was fully exported we do the full import.
        $local_store = $this->entityRepository->loadEntityByUuid('commerce_store', $store['uuid']);
        if ($local_store != null) {
          $store_ids [] = array('target_id' => $local_store->id());
        } else {
          \Drupal::messenger()->addWarning(t('Store with name ":store" not found. Please check that this product has been associated with the correct stores.',array(':store' => $store['name'])));
        }
      }
    }
    $entity['stores'] = empty($store_ids ) ? null : $store_ids;

    return $entity;
  }
}
