<?php 

namespace Drupal\xnavi\Installer\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class MessageForm extends FormBase {
    
    public function getFormId() {
        return 'xnavi_module_message_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['#title'] = $this->t('Hinweis');


        $form['description'] = [
            '#type' => 'item',
            '#markup' => $this->t('Bitte ändern Sie das Passwort der Datenbank ab und ändern die Daten in der settings.php Datei.'),
          ];


        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Verstanden'),
        ];
        
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $url = Url::fromRoute('entity.node_type.collection');
        $form_state->setRedirectUrl($url);
    }

}