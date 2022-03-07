<?php

namespace Drupal\vactory_register\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;


class FormRender extends ControllerBase {



  /**
   * Returns Register form.
   */
  public function register() {
    $is_anonymous = \Drupal::currentUser()->isAnonymous();
    $registration_form = NULL;
    if ($is_anonymous) {
      $entity = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->create([]);
      $formObject = \Drupal::entityTypeManager()
        ->getFormObject('user', 'register')
        ->setEntity($entity);
      $registration_form = \Drupal::formBuilder()->getForm($formObject);
    }
    $config = $this->config('vactory_register.settings')->get('allowed_fields');
    asort($config);
    return [
        '#theme'      => 'vactory_register_form',
        '#form_register' => $registration_form,
        '#extension' => array_keys($config),
      ];
  }

 

  

 

 



}