<?php

namespace Drupal\news\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class NewsletterAdministrationForm extends ConfigFormBase {

    protected function getEditableConfigNames() {
        return ['news.settings'];
    }

    public function getFormId()
    {
        return 'newsletter_administration_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('news.settings');
        $form['newsletter_administration_form']['types'] = [
            '#type' => 'checkboxes',
            '#title' => 'Kategorien',
            '#description' => $this->t('Hier können Sie die Kategorien auswählen, die von den Abonnenten gewählt werden können.'),
            '#options' => $this->getAllContentTypes(),
            '#default_value' => $config->get('type_settings'),
        ];

        $form['newsletter_administration_form']['greeting_text'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Begruessungstext'),
            //'#format' => 'full_html',
            '#default_value'=> $config->get('greeting_text_settings'),
        ];

        $form['newsletter_administration_form']['subscription_text'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Abo-Text in E-Mail'),
            '#default_value' => $config->get('subscription_text'),
        ];

        $form['newsletter_administration_form']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Speichern'),
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('news.settings')->set('type_settings', $form_state->getValue('types'))->save();
        $this->config('news.settings')->set('greeting_text_settings', $form_state->getValue('greeting_text'))->save();
        $this->config('news.settings')->set('subscription_text', $form_state->getValue('subscription_text'))->save();
    }

    public function getAllContentTypes() {
        $types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
        foreach($types as $type) {
            $content_types[$type->get('type')] = $type->get('name');
        }

        //$config = $this->config('news.settings');
        //dsm($config->get('type_settings'));
        return $content_types;
    }
}