<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Press kit.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_press_kit",
 *   label = @Translation("Press kit"),
 *   description = @Translation("The press kit module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class PressKit extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_press_kit']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The Press kit module adds a content model and default content"),
        ];

        return $form;
    }

}
