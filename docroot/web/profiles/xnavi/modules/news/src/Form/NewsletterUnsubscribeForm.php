<?php

namespace Drupal\news\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class NewsletterUnsubscribeForm extends FormBase {

    public function getFormId()
    {
        return 'newsletter_unsubscribe_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['newsletter_unsubscribe_form']['email'] = [
            '#type' => 'email',
            '#title' => $this->t('E-Mail'),
            '#description' => $this->t('Bitte geben Sie hier Ihre E-Mail Adresse ein, um den Newsletter abzubestellen'),
            '#required' => TRUE,
        ];

        $form['newsletter_unsubscribe_form']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Absenden'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $email = $form_state->getValue('email');
        $token = $this->getToken($email);
        $status = $this->deleteSubscriber($token);

        if($status) {
            dsm('Geloescht');
        } else {
            dsm('Fehler');
        }
        
    }

    public function getToken($email) {
        $database = \Drupal::database();
        $query = $database->select('newsletter_order', 'no')
                        ->fields('no', ['token'])
                        ->condition('email', $email, '=');
                       
        dsm($query->__toString());
        $result = $query->execute();
        foreach($result as $record) {
            $token = $record->token;
        }

        return $token;
    }

    public function deleteSubscriber($token) {
        $database = \Drupal::database();
        $query = $database->delete('newsletter_order')->condition('token', $token)->execute();
        return $query;
    }
}