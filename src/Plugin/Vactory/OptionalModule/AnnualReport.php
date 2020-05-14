<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Annual report.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_annual_report",
 *   label = @Translation("Annual report"),
 *   description = @Translation("The Annual report module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = false,
 * )
 */
class AnnualReport extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_annual_report']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The annual report module adds a content model and default content"),
        ];

        return $form;
    }

}
