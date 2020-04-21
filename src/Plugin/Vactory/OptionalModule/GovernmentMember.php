<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * GovernmentMember.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_government_member",
 *   label = @Translation("GovernmentMember"),
 *   description = @Translation("The government_member module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class GovernmentMember extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_government_member']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The government_member module adds a content model and default content"),
        ];

        return $form;
    }

}
