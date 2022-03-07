<?php

namespace Drupal\vactory_register\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class RegisterConfigForm extends ConfigFormBase {
    protected $fields = array();
    protected $sorted_fields = array();
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vactory_register_config_form';
  }

  protected function getEditableConfigNames() {
    return [
      'vactory_register.settings',
    ];
  }
 

  public function buildForm(array $form, FormStateInterface $form_state) {
    $group_class = 'group-order-weight';

      $entity = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->create([]);
      $formObject = \Drupal::entityTypeManager()
        ->getFormObject('user', 'register')
        ->setEntity($entity);
      $registration_form = \Drupal::formBuilder()->getForm($formObject);
    
    foreach(array_keys($registration_form) as $value ){
        if(str_starts_with($value, 'field_')){
            $this->fields[$value] = $value;
        }
    }
  

    $form['items']['#tree'] = TRUE;
    $form['items'] = [
      '#type' => 'table',

      '#header' => [
        $this->t('Label'),
        $this->t('Select'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('No items.'),
      '#tableselect' => false,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ]
      ]
    ];
    $config = $this->config('vactory_register.settings')->get('allowed_fields');

    foreach (array_keys($this->fields) as $key => $value) {
      $this->sorted_fields[$value] = array_key_exists($value, $config) ? $config[$value] : 0 ;
    }
    asort($this->sorted_fields);

    foreach (array_keys($this->sorted_fields) as $key => $value) {
      
      $form['items'][$key]['#attributes']['class'][] = 'draggable';
      $form['items'][$key]['#weight'] = isset($config[$value]) ? (int)$config[$value] : 0 ;
  
      $form['items'][$key]['label'] = [
        '#plain_text' => $value,
      ];
  
      $form['items'][$key][$value.'_checkbox'] = [
        '#type' => 'checkbox',
        '#default_value' => array_key_exists($value, $config)
      ];
  
      $form['items'][$key][$value.'_weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $value['label']]),
        '#title_display' => 'invisible',
        '#default_value' => isset($config[$value]) ?$config[$value] : 0 ,
        '#attributes' => ['class' => [$group_class]],
      ];
    } 
    
      
    return parent::buildForm($form,$form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $allowed_fields = array();
    foreach(array_keys($this->sorted_fields) as $key => $field){
        if($form_state->getValue(['items',$key,$field.'_checkbox'])){
            $allowed_fields[$field] = $form_state->getValue(['items',$key,$field.'_weight']);
        }

    }

    $this->config('vactory_register.settings')
      ->set('allowed_fields', $allowed_fields)
      ->save();
    parent::submitForm($form, $form_state);
    drupal_flush_all_caches();
  }

}