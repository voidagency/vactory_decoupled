<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * News.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_locator",
 *   label = @Translation("Locator"),
 *   description = @Translation("The locator module adds an entity model and default content for maps"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class Locator extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_locator']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The locator module adds an entity model and default content for maps"),
        ];

        return $form;
    }

}
