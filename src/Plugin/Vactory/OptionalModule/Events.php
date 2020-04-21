<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Events.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_events",
 *   label = @Translation("Events"),
 *   description = @Translation("The events module manages your events content."),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class Events extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_events']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The events module manages your events content."),
        ];

        return $form;
    }

}
