<?php

namespace Drupal\news\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;


class NewsletterSendTestMailForm extends FormBase {


    public function getFormId()
    {
        return 'newsletter_administration_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['newsletter_administration']['categories'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Kategorien'),
            '#description' => $this->t('Bitte kreuzen Sie die Kategorien an über welche Sie künftig per Newsletter informiert werden möchten.'),
            '#options' => $this->getContentTypes(),
            '#required' => TRUE,
            '#weight' => 1,
        ];

        $form['newsletter_administration']['testemail'] = [
            '#type' => 'email',
            '#title' => $this->t('Test E-Mail-Adresse'),
            '#description' => $this->t('Bitte tragen Sie hier Ihre Test-E-Mail-Adresse ein'),
            '#required' => TRUE,
            '#weight' => 2,
        ];

        $form['newsletter_administration']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Absenden'),
            '#weight' => 3,
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        //TODO: E-Mail senden mit den Kategorien
        $email = $form_state->getValue('testemail');
        $url = Url::fromRoute('news.newsletter_send_newsletter_test_mail')->setRouteParameters(['email' => $email ]);
        $form_state->setRedirectUrl($url);
    }

    public function getContentTypes() {
        $content_types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
        //kint($content_types);

        foreach($content_types as $content_type) {
            //dsm($content_type);
            //dsm($content_type->bundle());
            //dsm($content_type->get('type'));
            $categories[$content_type->get('type')] = $content_type->get('name'); 
        }
        //dsm($categories);
        $config_types = [
            'abschlussarbeit' => 'Abschlussarbeit', 
            'article' => 'Presseartikel', 
            'news' => 'News', 
            'event' => 'Veranstaltung'
        ];
        //dsm(array_intersect_key($categories, $config_types));
        return array_intersect($categories, $config_types);
        //return $config_types;
    }
}