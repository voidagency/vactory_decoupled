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

        $validators = array(
            'file_validate_extensions' => array('csv'),
        );

        $form['redirect'] = array(
        '#type' => 'managed_file',
        '#name' => 'redirect',
        '#title' => t('redirection file'),
        '#description' => t('Upload redirect file (.csv).'),
        '#upload_validators' => $validators,
        '#upload_location' => 'public://vactory_redirect',
    );

        $form['array_filter'] = array('#type' => 'value', '#value' => TRUE);
        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $fid = reset($form_state->getValue('redirect'));
        $database = \Drupal::database();
        $query = $database->query("SELECT fid FROM {file_managed}");
        $result = $query->fetchAll();
        $array = json_decode(json_encode($result), true);
        foreach ($array as $item){
            if ($item['fid'] != $fid){
                file_delete($item['fid']);
            }
        }
        drupal_set_message('file has been uploaded.');

    }
}