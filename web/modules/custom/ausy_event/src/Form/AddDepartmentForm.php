<?php

namespace Drupal\ausy_event\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Add Department form.
 */
class AddDepartmentForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_event_department';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ausy_event.settings',
    ];
  }

  /**
   * Function build form.
   *
   * This for is only for adding new department.
   * We are not checking if it already exists or updating existing one.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];
    $form['key'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine Name'),
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Department'),
    ];

    return $form;

  }

  /**
   * Determines if the action already exists.
   *
   * @param string $id
   *   The action ID.
   *
   * @return bool
   *   TRUE if the action exists, FALSE otherwise.
   */
  public function exists($id) {
    // @todo check if the department already exists.
    // This form is only about adding new department.
    return FALSE;
  }

  /**
   * Function submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $key = $values['key'];

    $config = $this->configFactory->getEditable('ausy_event.settings');
    $departments = $config->get('departments');
    $departments[$key] = $values['name'];

    $config
      ->set('departments', $departments)
      ->save();

    $this->messenger()->addStatus($this->t('Department created.',));
  }

}
