<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Page.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_page",
 *   label = @Translation("Page"),
 *   description = @Translation("The page module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = true,
 * )
 */
class Page extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_page']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The page module adds a content model and default content"),
        ];

        return $form;
    }

}
