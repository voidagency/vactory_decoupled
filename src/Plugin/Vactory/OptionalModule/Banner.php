<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * News.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_banner",
 *   label = @Translation("Banner"),
 *   description = @Translation("The banner module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = true,
 * )
 */
class Banner extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_banner']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The banner module adds a content model and default content"),
        ];

        return $form;
    }

}
