<?php
namespace Drupal\single_content_sync_commerce\Plugin\SingleContentSyncBaseFieldsProcessor;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\single_content_sync\SingleContentSyncBaseFieldsProcessorPluginBase;

/**
 * Plugin implementation for user base fields processor plugin.
 *
 * @SingleContentSyncBaseFieldsProcessor(
 *   id = "commerce_store",
 *   label = @Translation("Commerce store base fields processor"),
 *   entity_type = "commerce_store",
  * )
 */
class CommerceStore extends SingleContentSyncBaseFieldsProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function exportBaseValues(FieldableEntityInterface $entity): array {
    return [
      'name' => $entity->getName(),
      'mail' => $entity->get('mail')->value, // bypass the function
      'default_currency' => $entity->getDefaultCurrencyCode(),
      'timezone' => $entity->getTimezone(),
      'langcode' => $entity->language()->getId(),
      'billing_countries' => $entity->getBillingCountries(),
      'address' =>  $entity->getAddress()->getValue(),
      'is_default' => $entity->get('is_default')->value,
      'path' => $entity->hasField('path') ? $entity->get('path')->alias : NULL,
      'tax_registrations' =>  array_column($entity->get('tax_registrations')->getValue(), 'value'),
      'prices_include_tax'=> $entity->hasField('prices_include_tax') ? $entity->get('prices_include_tax')->value : NULL,
    ];
  }

  /**
  * {@inheritdoc}
  */
  public function mapBaseFieldsValues(array $values): array {
    return [
      'name' => $values['name'],
      'mail' => $values['mail'],
      'default_currency' => $values['default_currency'],
      'timezone' => $values['timezone'],
      'langcode' => $values['langcode'],
      'address' => $values['address'],
      'billing_countries' => $values['billing_countries'],
      'is_default' => $values['is_default'],
      'path' => $values['path'],
      'tax_registrations' => $values['tax_registrations'],
      'prices_include_tax' => $values['prices_include_tax'],
  ];
  }
}
