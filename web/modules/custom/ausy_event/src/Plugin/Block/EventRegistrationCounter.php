<?php

namespace Drupal\ausy_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ausy_event\service\AusyEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event registration Counter.
 *
 * @Block(
 *   id = "event_registration_counter",
 *   admin_label = @Translation("Event registration Counter")
 * )
 */
class EventRegistrationCounter extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Ausy Event service.
   *
   * @var \Drupal\ausy_event\service\AusyEvent
   */
  public $ausyEvent;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AusyEvent $ausyEvent) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ausyEvent = $ausyEvent;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ausy_event')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build['counter'] = [
      '#markup' => $this->t('Count Registration: @count', ['@count' => $this->ausyEvent->getEventRegistrationCount()]),
      '#cache' => [
        'tags' => [
          'node_list:registration',
        ],
      ],
    ];

    return $build;
  }

}
