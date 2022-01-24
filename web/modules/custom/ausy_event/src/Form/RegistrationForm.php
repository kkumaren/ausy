<?php

namespace Drupal\ausy_event\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for event registration.
 */
class RegistrationForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * A date time instance.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   A date time instance.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, TimeInterface $time) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'registration';
  }

  /**
   * Function build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $department = NULL) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of the employee'),
      '#required' => TRUE,
    ];
    $form['one_plus'] = [
      '#type' => 'radios',
      '#title' => $this->t('One plus'),
      '#options' => ['1' => $this->t('Yes'), '0' => $this->t('No')],
      '#required' => TRUE,
    ];
    $form['num_kids'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount of kids'),
      '#min' => 0,
    ];
    $form['num_vegetarian'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount of vegetarians'),
      '#min' => 0,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $num_people = 1;
    // Assuming one_plus means to add one more people for the
    // employee husband/wife.
    if (isset($values['one_plus']) && $values['one_plus'] = '1') {
      $num_people++;
    }
    $num_people += $values['num_kids'];

    if (isset($values['num_vegetarian']) && $values['num_vegetarian'] > $num_people) {
      $form_state->setErrorByName('num_vegetarian', $this->t('Amount of vegetarians can not be higher than the total amount of people.'));
    }
    if (isset($values['email']) && $values['email'] != '') {
      $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
        'type' => 'registration',
        'field_email' => $values['email'],
      ]);
      $node = reset($nodes);
      if (!empty($node)) {
        $form_state->setErrorByName('email', $this->t('This email address was already registered.'));
      }
    }

  }

  /**
   * Function submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $node = Node::create([
      'type' => 'registration',
      'title' => $values['name'] ?? '',
      'field_kids_num' => $values['num_kids'] ?? '',
      'field_one_plus' => $values['one_plus'] ?? '',
      'field_vegetarians_num' => $values['num_vegetarian'] ?? '',
      'field_email' => $values['email'] ?? '',
      'status' => NodeInterface::PUBLISHED,
      'promote' => 0,
      'sticky' => 0,
      'created' => $this->time->getRequestTime(),
      'changed' => $this->time->getRequestTime(),
    ]);
    $node->save();

  }

  /**
   * Checks access for the revision form.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param string $department
   *   The deparment.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, string $department = NULL) {
    $fields = $this->entityFieldManager->getFieldDefinitions('node', 'registration');
    if (isset($fields['field_department'])) {
      $field = $fields['field_department'];
      /** @var \Drupal\Core\Field\FieldStorageDefinitionInterface $field_definition */
      $field_definition = $field->getFieldStorageDefinition();
      $allowed_values = $field_definition->getSetting('allowed_values');
      if (isset($allowed_values[$department])) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

}
