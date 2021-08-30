<?php
/**
 * Created by PhpStorm.
 * User: ismail
 * Date: 29/03/2021
 * Time: 10:35
 */

namespace Drupal\vactory_redirect\Form;

use \Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class VactoryRedirectSettingsForm extends ConfigFormBase
{

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames()
  {
    return [
      'vactory_redirect.settings'
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId()
  {
    return 'vactory_redirect_admin_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $url_options = array('absolute' => TRUE);
    $t_args = [
      ':settings_url' => Url::fromUri('base:/admin/structure/file-types/manage/document/edit', $url_options)->toString(),
    ];
    $message = t('If your having trouble uploading the csv file. Add <strong><em>text/csv</em></strong> <a href=":settings_url"> to the allowed <em>MIME types</em></a>.', $t_args);
    \Drupal::messenger()->addWarning($message);

    // Check file.
    $fid = $this->config('vactory_redirect.settings')->get('fid');
    $fids = [];
    if ($fid && $fid > 0 && ($file = File::load($fid))) {
      $fids[0] = $file->id();
    }

    $validators = array(
      'file_validate_extensions' => array('csv'),
    );

    $form['redirect'] = array(
      '#type' => 'managed_file',
      '#name' => 'redirect',
      '#title' => t('CSV file'),
      '#description' => t('Upload redirect file (.csv).'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://vactory_redirect',
      '#default_value' => $fids,
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $fid = reset($form_state->getValue('redirect'));
    $this->config('vactory_redirect.settings')
      ->set('fid', $fid)
      ->save();
    $file = File::load($fid);
    // Make sure that a used file is permanent.
    if (!$file->isPermanent()) {
      $file->setPermanent();
      $file->save();
    }
    \Drupal::messenger()->addMessage('file has been uploaded.');
  }
}
