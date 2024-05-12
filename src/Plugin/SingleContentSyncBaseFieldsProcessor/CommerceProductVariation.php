<?php
namespace Drupal\single_content_sync_commerce\Plugin\SingleContentSyncBaseFieldsProcessor;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\single_content_sync\SingleContentSyncBaseFieldsProcessorPluginBase;

/**
 * Plugin implementation for user base fields processor plugin.
 *
 * @SingleContentSyncBaseFieldsProcessor(
 *   id = "commerce_product_variation",
 *   label = @Translation("Commerce product variations fields processor"),
 *   entity_type = "commerce_product_variation",
  * )
 */
class CommerceProductVariation extends SingleContentSyncBaseFieldsProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function exportBaseValues(FieldableEntityInterface $entity): array {
    return [
      'sku' => $entity->getSku(),
      'title' => $entity->getTitle(),
      'langcode' => $entity->language()->getId(),
      'product_id' => $entity->getProductId(),
      'status' => $entity->isActive(),
      'list_price' => empty($entity->getListPrice()) ? null : $entity->getListPrice()->toArray(),
      'price' => empty($entity->getPrice()) ? null : $entity->getPrice()->toArray(),
    ];
  }

  /**
  * {@inheritdoc}
  */
  public function mapBaseFieldsValues(array $values): array {

    $product = \Drupal::service('entity.repository')->loadEntityByUuid('commerce_product', $values['product_uuid']);

    return [
      'sku' => $values['sku'],
      'title' => $values['title'],
      'langcode' => $values['langcode'],
      'product_id' => $product->id(),
      'status' => $values['status'],
      'list_price' =>  empty($values['list_price']) ? null : new \Drupal\commerce_price\Price($values['list_price']['number'], $values['list_price']['currency_code']),
      'price' =>  empty($values['price']) ? null : new \Drupal\commerce_price\Price($values['price']['number'], $values['price']['currency_code']),
    ];
  }
}
