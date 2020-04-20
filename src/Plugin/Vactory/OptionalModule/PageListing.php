<?php

namespace Drupal\vactory_decoupled\Plugin\Vactory\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Page Listing.
 *
 * @VactoryOptionalModule(
 *   id = "vactory_page_listing",
 *   label = @Translation("Page Listing"),
 *   description = @Translation("The page listing module adds a content model and default content"),
 *   type = "module",
 *   weight = 10,
 *   standardlyEnabled = true,
 * )
 */
class PageListing extends AbstractOptionalModule {

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form = parent::buildForm($form, $form_state);

        $form['vactory_page_listing']['project_info'] = [
            '#type' => 'item',
            '#description' => $this->t("The page listing module adds a content model and default content"),
        ];

        return $form;
    }

}
