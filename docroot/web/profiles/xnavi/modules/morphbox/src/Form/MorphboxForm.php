<?php

namespace Drupal\morphbox\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\block\Entity\Block;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

class MorphboxForm extends FormBase {

    public function getFormId()
    {
        return 'morphbox_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        global $base_url;
        $form['#theme'] = 'morphboxform';
        $form['#attached']['library'][] = 'morphbox/morphbox';
        $form['#attached']['drupalSettings']['baseUrl'] = $base_url;


        $form['morphbox']['dimension'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Dimension'),
            '#description' => $this->t('Bitte geben Sie hier den Dimensionsnamen ein'),
            '#attributes' => [
                'v-model' => 'table',
                'v-if' => 'showDimensionInput'
            ]

        ];

        $form['morphbox']['attribute'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Attribut'),
            '#description' => $this->t('Bitte geben Sie hier den Attributsnamen ein'),
            '#attributes' => [
                'v-model' => 'attribute',
                'v-if' => 'showAttributeInput'
            ]

        ];

        $form['morphbox']['button_dimension_add'] = [
            '#type' => 'button',
            '#value' => $this->t('Add Dimension'),
            '#attributes' => [
                '@click' => 'buttonClick',
                'v-if' => 'showDimensionInput'
            ],
        ];

        $form['morphbox']['button_attribute_add'] = [
            '#type' => 'button',
            '#value' => $this->t('Add Attribute'),
            '#attributes' => [
                '@click' => 'saveAttribute'
            ],
        ];

        $form['morphbox']['button_dimension_save'] = [
            '#type' => 'button',
            '#value' => $this->t('Save Dimension'),
            '#attributes' => [
                '@click' => 'saveDimension',
                'v-if' => 'showDimensionSaveButton'
            ],
        ];

        $form['morphbox']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
        $dimension = $form_state->getValue('text');
        $this->_create_vocabulary($dimension);
        // \Drupal::messenger()->addMessage('Dimenson @dimension installiert', ['@dimension' => $dimension]);
        // TODO: Create Facets
        
        /*
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
        */

        /*
        $block = Block::load('contenttypefacet');
        $block->setRegion('sidebar');
        $block->save();

        \Drupal::messenger()->addMessage('Block enabled');

        */
    }

    public function _create_term($term, $vocabulary, array $parent = []) {

        // Create the taxonomy term.
        $new_term = Term::create([
          'name' => $term,
          'vid' => $vocabulary,
          'parent' => $parent,
        ]);
      
        // Save the taxonomy term.
        $new_term->save();
      
        // Return the taxonomy term id.
        return $new_term->id();
    }

    public function _create_vocabulary($name) {
        $vocabularies = Vocabulary::loadMultiple();
        $machine_name = preg_replace('@[^a-z0-9-]+@','-', strtolower($name));
        if(!isset($vocabularies[$machine_name])) {
            $vocabulary = Vocabulary::create([
                'vid' => $machine_name,
                'description' => '',
                'name' => $name,
            ]);
            $vocabulary->save();
        } else {
            // TODO: Do something here
        }
    }
}