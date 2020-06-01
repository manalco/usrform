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
      '#access' => ($step == 1)? TRUE : FALSE,
    ];

    $form['form-wrapper']['step_1']['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
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
      '#access' => ($step == 2)? TRUE : FALSE,
    ];

    $form['form-wrapper']['step_2']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
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

    /*$title = $form_state->getValue('title');

    if (strlen($title) < 10) {
      $form_state->setErrorByName('title', $this->t('The title must be at least 10 characters long.'));
    }*/
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messenger = \Drupal::messenger();
    $messenger->addMessage('Success');
    //$messenger->addMessage('Title: '.$form_state->getValue('title'));

    //$form_state->setRedirect('/usrform/create');

  } 

}