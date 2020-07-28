<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Glossary.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_glossary",
 *   label = @Translation("Glossary"),
 *   description = @Translation("The glossary module manages your glossary content."),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class Glossary extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_glossary']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The glossary module manages your glossary content."),
        ];

        return $form;
    }

}
