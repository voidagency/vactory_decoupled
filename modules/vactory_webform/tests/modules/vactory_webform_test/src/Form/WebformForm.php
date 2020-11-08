<?php

namespace Drupal\vactory_webform_test\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\vactory_dynamic_field\ModalEnum;
use Drupal\vactory_dynamic_field\Plugin\Field\FieldWidget\FormWidgetTrait;
use Drupal\vactory_dynamic_field\WidgetsManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform class.
 */
class WebformForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'dynamic_field_hello_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $d = \Drupal::service('vactory.webform.normalizer');
    $web = $d->normalize('contact');
    dpm($web);

    $form['webform'] = [
      '#type' => 'webform_decoupled',
      '#title' => t('Form.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $webform_id = $form_state->getValue('webform_id');
    $d = \Drupal::service('vactory.dynamic_field.webform');
    $d->normalize($webform_id);
  }
}
