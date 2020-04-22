<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Tender.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_tender",
 *   label = @Translation("Tender"),
 *   description = @Translation("The tender module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class Tender extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_tender']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The tender module adds a content model and default content"),
        ];

        return $form;
    }

}
