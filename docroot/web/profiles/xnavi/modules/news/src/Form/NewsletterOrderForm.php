<?php
namespace Drupal\news\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Exception;

class NewsletterOrderForm extends FormBase {
    public function getFormId() {
        return 'newsletter_order_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        
        $form['newsletter_order']['category'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Kategorien'),
            '#description' => $this->t('Bitte kreuzen Sie die Kategorien an über welche Sie künftig per Newsletter informiert werden möchten.'),
            '#options' => $this->getContentTypes(),
            '#required' => TRUE,
        ];

        $form['newsletter_order']['email'] = [
            '#type' => 'email',
            '#title' => 'E-Mail-Adresse',
            //'#default_value' => $this->getUserEmail(),
            '#required' => TRUE,
        ];

        $form['newsletter_order']['salutation'] = [
            '#type' => 'select',
            '#title' => $this->t('Anrede'),
            '#options' => ['Herr', 'Frau', 'Divers', 'Keine Angabe'],
            '#required' => TRUE,
        ];

        $form['newsletter_order']['firstname'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Vorname'),
            '#required' => TRUE,
        ];

        $form['newsletter_order']['surname'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nachname'),
            '#required' => TRUE,
        ];

        $form['newsletter_order']['company'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Firma'),
        ];
        $form['newsletter_order']['branch'] = [
            '#type' => 'select',
            '#title' => $this->t('Branche'),
            '#options' => [
                'Tourismus',
                'Marketing und Kommunikation',
                'Logistik',
                'Erneurbare Energien',
                'Life Science',
                'Wissenschaft',
                'Finanzen',
                'Handel',
                'IT',
                'Luftfahrt',
                'Sonstiges',
            ]
        ];

        $form['newsletter_order']['declaration'] = [
            '#type' => 'checkbox',
            '#title' => 'Ich bin einverstanden regelmäßig Newsletter mit den aktuellsten Nachrichten vom 
            Zentrum für Digitalen Wandel per E-Mail zu erhalten. Ich bin einverstanden, dass das Leseverhalten 
            ausgewertet wird, um zu erfahren wie häufig der Newsletter geöffnet wird 
            und welche Inhalte die Leser interessieren. Zudem bin ich einverstanden, dass die durch diese Auswertung 
            gewonnenen Daten in einem persönlichen Nutzerprofil verknüpft werden. Diese Einwilligung kann ich jederzeit 
            am Ende des Newsletters widerrufen. Nähere Informationen sind in der Datenschutzerklärung der 
            des Zentrums für Digitalen Wandel enthalten, die ich zur Kenntnis genommen habe.',
            '#required' => TRUE,
        ];

        

        $form['newsletter_order']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Senden'),
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        //check if email already exists
        $email = $form_state->getValue('email');
        $database = \Drupal::database();
        $query = $database->select('newsletter_order', 'no')->condition('email', $email);
        $number_of_rows = $query->countQuery()->execute()->fetchField();

        $message = $this->t('Diese E-Mail wurde schon registriert!');
        //$message .= $this->t(' Wenn Sie sich vom Newsletter abmelden möchten klicken Sie ');
        //$message .= Link::createFromRoute($this->t('hier'), 'news.newsletter_unsubscribe_confirmation_form')->toString()->getGeneratedLink();
        
        $message = new TranslatableMarkup('@message', array('@message' => $message));
        if($number_of_rows > 0) {
            $form_state->setErrorByName('email', $message);
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $token = $this->generateRandomToken();
        $salutation_key = $form_state->getValue('salutation');
        $salutation = $form['newsletter_order']['salutation']['#options'][$salutation_key];
        $branch_key = $form_state->getValue('branch');
        $branch = $form['newsletter_order']['branch']['#options'][$branch_key];
        $firstname = $form_state->getValue('firstname');
        $surname = $form_state->getValue('surname');
        $company = $form_state->getValue('company');
        $email = $form_state->getValue('email');
        $categories = $form_state->getValue('category');
        foreach($categories as $key => $value) {
            //dsm($value);
            if(!is_int($value)) {
                $types[] = $value;
            }
        }
        $types = implode('|', $types);
        //dsm($types);
        $message = "Anrede: " . $salutation . 
            " Vorname: " . $firstname . 
            " Nachname: " . $surname . 
            " E-Mail: " . $email . 
            " Firma: " . $company . 
            " Branche: " . $branch .
            " Kategorie " . $types .
            " Token: " . $token;
        \Drupal::messenger()->addMessage($message, 'status');
        $this->saveToDatabase($salutation, $firstname, $surname, $email, $company, $branch, $types, $token);
        $url = Url::fromRoute('news.newsletter_order_confirmation_mail')->setRouteParameters(['salutation' => $salutation, 'firstname' => $firstname, 'surname' => $surname, 'email' => $email ]);
        $form_state->setRedirectUrl($url);

    }

    public function saveToDatabase($salutation, $firstname, $surname, $email, $company, $branch, $types, $token) {
        $connection = \Drupal::service('database');
        try {
            $result = $connection->insert('newsletter_order')
            ->fields([
                'salutation' => $salutation,
                'firstname' => $firstname,
                'surname' => $surname,
                'email' => $email,
                'company' => $company,
                'branch' => $branch,
                'types' => $types,
                'token' => $token,
                'confirmation_flag' => 0,

            ])
            ->execute();
        } catch(Exception $e) {
            \Drupal::logger('news')->error($e);
        }
        

    }

    public function getUserEmail() {
        $user = \Drupal::currentUser();
        return $user->getEmail();
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
        $config = $this->config('news.settings');
        //dsm($config->get('type_settings'));

        $config_types = $config->get('type_settings');
        foreach($config_types as $config_type) {
            if ($config_type == "0") {
                continue;
            }
            
            $categories2[$config_type] = $this->getName($config_type);
        }


        //dsm($categories2);

        $config_types = [
            'abschlussarbeit' => 'Abschlussarbeit', 
            'article' => 'Presseartikel', 
            'news' => 'News', 
            'event' => 'Veranstaltung'
        ];
        //dsm(array_intersect_key($categories, $config_types));
        return array_intersect($categories, $categories2);
        //return $config_types;
    }

    public function generateRandomToken() {
        //Generate a random string
        $token = openssl_random_pseudo_bytes(16);
        //Convert the binary into hex
        $token = bin2hex($token);
        return $token;
    }

    public function getName($type_name) {
        $entities = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => $type_name]);
        //dsm($entities);
        foreach($entities as $entity) {
            //$label = $entity->label();
            //$bundle = $entity->bundle();
            //$bundle_type_id = $entity->getEntityType()->getBundleEntityType();
            $bundle_label = \Drupal::entityTypeManager()->getStorage('node_type')->load($entity->bundle())->label();
            break;
        }

        //dsm($label);
        //dsm($bundle);
        //dsm($bundle_type_id);
        return $bundle_label;
    }
    

     
}

