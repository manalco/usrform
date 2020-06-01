<?php

namespace Drupal\usrform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

class UsrCreateForm extends FormBase {

  public function getFormId() {
    return 'usrform_usr_create_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!$form_state->has('step')) {
      $form_state->set('step', 1);
    }

    $step = $form_state->get('step');

    if ($trigger = $form_state->getTriggeringElement()) {
      switch ($trigger['#name']) {
        case 'Next':
          $form_state->set('step', ++$step);
          break;

        case 'Back':
          $form_state->set('step', --$step);
          break;
      }
    }

    $form['form-wrapper'] = [
      '#type' => 'item',
      '#prefix' => '<div id="form-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['form-wrapper']['step_1'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Step 1 of 2'),
      '#prefix' => ($step == 1)? '<div>':'<div class="hidden">',
      '#suffix' => '</div>',
    ];

    $form['form-wrapper']['step_1']['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'.$step),
      '#required' => TRUE,
    ];

    $form['form-wrapper']['step_1']['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
    ];

    $form['form-wrapper']['step_1']['gender'] = [
      '#type' => 'select',
      '#title' => $this->t('Gender'),
      '#options' => [
        '' => $this->t('-- Select one --'),
        'M' => $this->t('Male'),
        'F' => $this->t('Female'),
        'O' => $this->t('Other'),
      ],
      '#required' => TRUE,
    ];

    $form['form-wrapper']['step_1']['birthday'] = [
      '#type' => 'date',
      '#title' => 'Date of Birth',
      '#format' => 'm/d/Y',
      '#required' => TRUE
    ];

    $form['form-wrapper']['step_2'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Step 2 of 2'),
      '#prefix' => ($step == 2)? '<div>':'<div class="hidden">',
      '#suffix' => '</div>',
    ];

    $form['form-wrapper']['step_2']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => ($step == 2)? TRUE:FALSE,
    ];

    $form['form-wrapper']['step_2']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail'),
      '#required' => ($step == 2)? TRUE:FALSE,
    ];

    $form['form-wrapper']['step_2']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => FALSE,
    ];

    $form['form-wrapper']['step_2']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#required' => FALSE,
    ];

    $form['form-wrapper']['actions'] = [
      '#type' => 'actions',
    ];

    if($step == 2){
      $form['form-wrapper']['actions']['next'] = array();
      $form['form-wrapper']['actions']['back'] = [
        '#type' => 'button',
        '#value' => 'Back',
        '#name' => 'Back',
        '#access' => TRUE,
        '#ajax' => ['wrapper' => 'form-wrapper'],
      ];
      $form['form-wrapper']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#access' => TRUE,
      ];
    }else{
      $form['form-wrapper']['actions']['back'] = array();
      $form['form-wrapper']['actions']['next'] = [
        '#type' => 'button',
        '#value' => 'Next',
        '#name' => 'Next',
        '#access' => TRUE,
        '#ajax' => ['wrapper' => 'form-wrapper'],
      ];
      $form['form-wrapper']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#access' => FALSE
      ];
    }

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $birthday = $form_state->getValue('birthday');
    if(strtotime($birthday) > strtotime(date('m/d/Y'))){
      $form_state->setErrorByName('birthday', $this->t('<b>Date of Birth</b> must not be a future date.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $usr = \Drupal\user\Entity\User::create();
    $username = $this->sanitize($form_state->getValue('first_name').$form_state->getValue('last_name'));

    $ids = \Drupal::entityQuery('user')
      ->condition('name', $username)
      ->range(0, 1)
      ->execute();
    if(!empty($ids)){
      $i = 0;
      while (!empty($ids)) {
        $i++;
        $username = $this->sanitize($form_state->getValue('first_name').$form_state->getValue('last_name').$i);
        $ids = \Drupal::entityQuery('user')
          ->condition('name', $username)
          ->range(0, 1)
          ->execute();
      }
    }

    $usr->setPassword($username);
    $usr->enforceIsNew();
    $usr->setEmail($form_state->getValue('email'));
    $usr->setUsername($username);

    $usr->set("field_usr_first_name", $form_state->getValue('first_name'));
    $usr->set("field_usr_last_name", $form_state->getValue('last_name'));
    $usr->set("field_usr_gender", $form_state->getValue('gender'));
    $usr->set("field_usr_birthday", $form_state->getValue('birthday'));
    $usr->set("field_usr_city", $form_state->getValue('city'));
    $usr->set("field_usr_phone", $form_state->getValue('phone'));
    $usr->set("field_usr_address", $form_state->getValue('address'));

    $usr->activate();
    $res = $usr->save();

    $messenger = \Drupal::messenger();
    $messenger->addMessage("The user has been created with 'username' and 'password': ".$username);
  }

  private function sanitize($str){
    return preg_replace('/[^A-Za-z0-9]/', '', strtolower($str));
  }

}