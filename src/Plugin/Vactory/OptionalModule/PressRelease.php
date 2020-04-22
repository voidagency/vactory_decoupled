<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Press release.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_press_release",
 *   label = @Translation("Press release"),
 *   description = @Translation("The press release module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class PressRelease extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_press_release']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The Press release module adds a content model and default content"),
        ];

        return $form;
    }

}
