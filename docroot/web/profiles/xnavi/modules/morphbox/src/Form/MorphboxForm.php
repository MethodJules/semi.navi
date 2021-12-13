<?php

namespace Drupal\morphbox\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\block\Entity\Block;

class MorphboxForm extends FormBase {

    public function getFormId()
    {
        return 'morphbox_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['morphbox']['text'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Dimension'),
            '#description' => $this->t('Bitte geben Sie hier den Dimensionsnamen ein'),

        ];

        $form['morphbox']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $facet = \Drupal\facets\Entity\Facet::create(
            [
                'id' => 'content_type_facet',
                'name' => 'Content Type Facet',
            ]

        );

        $facet->setFacetSourceId('search_api:views_page__portalsuche__page_1');  
        $facet->setFieldIdentifier('type'); //für Taxonomie der Maschinenname

        // weitere Einstellungen, die man sonst über das UI vornehmen würde
        $facet->setOnlyVisibleWhenFacetSourceIsVisible(TRUE);
        $facet->setWeight(0);
        $facet->setWidget('links');
        $facet->setEmptyBehavior(['behavior' => 'none']);
        $facet->addProcessor(['processor_id' => 'url_processor_handler']);
        $facet->setUrlAlias($facet->id());
        $facet->save();

        \Drupal::messenger()->addMessage('Facet installiert');
        

        /*
        $block = Block::load('contenttypefacet');
        $block->setRegion('sidebar');
        $block->save();

        \Drupal::messenger()->addMessage('Block enabled');

        */
    }
}