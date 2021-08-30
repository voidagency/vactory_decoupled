<?php
/**
 * Created by PhpStorm.
 * User: ismail
 * Date: 21/03/2021
 * Time: 18:37
 */

namespace Drupal\vactory_icon\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Archiver\ArchiverException;
use Drupal\Core\Archiver\Zip;
use Drupal\Core\File\Exception\FileException;
use \Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Yaml\Yaml;

class VactotyIconSettingsForm extends ConfigFormBase
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
            'vactory_icon.settings'
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
        return 'vactory_icon_admin_settings';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $validators = array(
            'file_validate_extensions' => array('zip'),
        );

        $form['selection'] = array(
            '#type' => 'managed_file',
            '#name' => 'selection',
            '#title' => t('Icons'),
            '#description' => t('Upload icomoon zip file'),
            '#upload_validators' => $validators,
            '#upload_location' => 'public://vactory_icon',
        );

        $form['array_filter'] = array('#type' => 'value', '#value' => TRUE);
        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        //============ Get Paths ============
        $fid = reset($form_state->getValue('selection'));
        $file = \Drupal\file\Entity\File::load($fid);
        $uri = $file->getFileUri();
        $absolute_path = \Drupal::service('file_system')->realpath($uri);
        $absolute_path_parent = \Drupal::service('file_system')->realpath('public://vactory_icon');

        try {

            //============ Delete old files before unzip ============
            file_unmanaged_delete_recursive('public://vactory_icon/style.css');
            file_unmanaged_delete_recursive('public://vactory_icon/selection.json');
            file_unmanaged_delete_recursive('public://vactory_icon/fonts');

            //============ Extract files ============
            $zip = new Zip($absolute_path);
            $zip->extract($absolute_path_parent);
            var_dump($zip->listContents());
            $zip->getArchive()->close();

            //============ Delete useless files and dirs ============
            file_unmanaged_delete_recursive('public://vactory_icon/demo.html');
            file_unmanaged_delete_recursive('public://vactory_icon/Read Me.txt');
            file_unmanaged_delete_recursive('public://vactory_icon/demo-files');

            //============ Delete zip itself ============
            file_delete($fid);
            drupal_set_message('file uploaded');
        } catch (ArchiverException $exception) {
            drupal_set_message('cannot extract files from uploaded file','error');
        }


    }

}