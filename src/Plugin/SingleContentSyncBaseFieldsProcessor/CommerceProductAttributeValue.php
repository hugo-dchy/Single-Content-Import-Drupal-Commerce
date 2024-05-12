<?php
namespace Drupal\single_content_sync_commerce\Plugin\SingleContentSyncBaseFieldsProcessor;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\single_content_sync\SingleContentSyncBaseFieldsProcessorPluginBase;

/**
 * Plugin implementation for user base fields processor plugin.
 *
 * @SingleContentSyncBaseFieldsProcessor(
 *   id = "commerce_product_attribute_value",
 *   label = @Translation("Commerce product base fields processor"),
 *   entity_type = "commerce_product_attribute_value",
  * )
 */
class CommerceProductAttributeValue extends SingleContentSyncBaseFieldsProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function exportBaseValues(FieldableEntityInterface $entity): array {
    return [
      'name' => $entity->getName(),
      'langcode' => $entity->language()->getId(),
      'weight' => $entity->getWeight(),
      'attribute' => $entity->getAttributeId(),
    ];
  }

  /**
  * {@inheritdoc}
  */
  public function mapBaseFieldsValues(array $values): array {
    return [
      'name' => $values['name'],
      'langcode' => $values['langcode'],
      'weight' => $values['weight'],
      'attribute' => $values['attribute'], //FIXME
    ];
  }
}
