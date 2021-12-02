<?php

namespace Drupal\news\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\news\Services\NewsMail;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class NewsController extends ControllerBase {
    /**
     * @var $mail_service
     */
    protected $mail_service;

    /**
     * Constructor
     */
    public function __construct(NewsMail $mail_service) {
        $this->mail_service = $mail_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static($container->get('xnavi_news_mail.mail'));
    }

    public function sendTestMail() {
        // Build mail params.
        $params['subject'] = 'Neuer Inhalt wurde geposted';
        $params['cta_url'] = '/node/1';
        $params['body'] = $this->t('Someone just posted new content:');
        $params['cta_text'] = 'View new post';
        $params['bold_text'] = 'Example title / subject';
        $params['lower_body'] = 'This is a lower body example text.';
        $params['users'] = $this->getAllUsers();
        //kint($params['users']);
        // Send mail via service.
        $mail_service = \Drupal::service('xnavi_news_mail.mail');
        $key = 'xnavi_news_mail';
        //$mail_service->sendMail($params, $key); //TODO: Wieder aktivieren, wenn ich es an einen User schicke
        return array();
    }

    

    public function content() {
        return ['#markup' => 'News Content'];
    }

    public function activities() {
        $database = \Drupal::database();
        $query = $database->select('activities', 'activities');
        $query->fields('activities', ['nid']);
        $result = $query->execute()->fetchAll();

        foreach ($result as $record) {
            $nid = $record->nid;
            $node_storage = \Drupal::entityManager()->getStorage('node');
            $node = $node_storage->load($nid);

            $title = $node->get('title')->value;
            $creation_date = $node->get('created')->value;

            $items[] = [
                '#wrapper_attributes' => [
                    'class' => ['news_item', 'list-group-item'] 
                ],
                '#children' => $title . ' wurde erstellt.',
            ];

        }

        $activities = [
            '#theme' => 'item_list',
            '#list_type' => 'ul',
            '#items' => $items,
            '#attributes' => [
                'class' => ['list_group', 'list-group-flush']
            ],
            '#wrapper_attributes' => [
                'class' => ['class_for_div']
            ],
        ];

        return $activities;
    }

    /**
   * Helper function to get all users.
   * @return mixed
   */

   //TODO: Sollte geloescht werden können
    private function getAllUsers(){
        $query = \Drupal::database()->select('users_field_data', 'ufd');
        $query->addField('ufd', 'name');
        $query->addField('ufd', 'mail');
        $query->condition('ufd.status', 1);
        //$data = $query->execute()->fetchAll();

        return $query->execute()->fetchAll();
    }


    public function sendConfirmationMail($email, $salutation, $firstname, $surname) {
        //global $base_url;

        $base_path = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
        //dsm($base_url);
        $config = $this->config('news.settings');
        $subscription_text = $config->get('subscription_text');
        //Get token from database
        $result = \Drupal::database()->select('newsletter_order', 'no')
                    ->fields('no', ['token'])
                    ->condition('no.email', $email)
                    ->execute();
        
        foreach($result as $record) {
            $token = $record->token;
        }
                // Build mail params.
        $params['subject'] = 'Newsletter abonnieren';
        $params['cta_url'] = 'news/newsletter/order/confirmation/' . $token; //TODO: Url aufbauen und im Routing einbauen. 
        $params['body'] = $subscription_text . '' . $this->t('Um Ihr Abonnement für die E-Mail-Adresse ' . $email . ' zu aktivieren, klicken Sie bitte auf den Abonnieren-Button.');
        $params['cta_text'] = 'Abonnieren';
        //$params['bold_text'] = 'Example title / subject';
        $params['lower_body'] = 'Sollten Sie keine Anmeldung für diesen Newsletter vorgenommen haben, so ignorieren Sie bitte diese Email. Sie werden keine weiteren Emails von uns erhalten.';
        $params['name_recipient'] = 'Sehr geehrte(r) ' . $salutation . ' ' . $firstname . ' ' . $surname ; //TODO: Anpassen
        $params['email'] = $email;
        $params['base_path'] = $base_path;
        $params['order_flag'] = FALSE;
        //kint($params['users']);
        // Send mail via service.
        $mail_service = \Drupal::service('xnavi_news_mail.mail');
        $key = 'xnavi_news_mail';
        $mail_service->sendMail($params, $key);
        return ['#markup' => 'Eine Bestätigungsmail wurde an folgende Email: "' . $email . '" gesendet.' ];
    }

    public function recieveConfirmation($token) {

        try {
            $query = \Drupal::database()->update('newsletter_order')
            ->fields([
                'confirmation_flag' => 1,
            ])
            ->condition('token', $token, '=')
            ->execute();
            $html =  '<p>Vielen Dank für die Registrierung zum Newsletter.</p>'; //TODO In Admin bringen
            \Drupal::logger('news')->notice('Newsletter wurde abonniert. Token: ' . $token);

        } catch(Exception $e) {
            \Drupal::logger('news')->error($e);
            $html = '<p>Bei der Registrierung scheint etwas schiefgelaufen zu sein. Bitte kontaktieren Sie den Systemadministrator</p>';
        }
        return ['#markup' => $html];
    }

    public function sendNewsletterAdministration() {
        $link = \Drupal::service('link_generator')->generateFromLink(Link::createFromRoute($this->t('Newsletter absenden'),'news.newsletter_send_newsletter_mail' ));
        
        return ['#markup' => $link];
    }

    public function sendNewsletter() {
        //TODO Build Mail params
        //$email = 'hoferj@uni-hildesheim.de'; //TODO spaeter loeschen

        //Load configuration
        $config = $this->config('news.settings');
        $greeting_text = $config->get('greeting_text_settings');

        //Base Path
        $base_path = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();


        //Fetch database
        $database = \Drupal::database();
        $query = $database->select('newsletter_order', 'no');
        //$query->condition('email', $email);
        $query->fields('no',['salutation', 'firstname', 'surname', 'types', 'confirmation_flag', 'token', 'email']);
        $result = $query->execute();

        foreach($result as $record) {
            //dsm($record);
            if(intval($record->confirmation_flag) === 1) {
                $salutation = $record->salutation;
                $firstname = $record->firstname;
                $surname = $record->surname;
                $email = $record->email;
                $types = explode('|',$record->types);
                $token = $record->token;

                //Subject
                $params['subject'] = 'Newsletter';
                //Base Path
                $params['base_path'] = $base_path;
                //Name
                $params['name_recipient'] = $this->t('Sehr geehrte(r) ' . $salutation . ' ' . $firstname . ' ' . $surname);
                //Token
                $params['token'] = $token;
                //Body
                $params['body'] = $greeting_text;
                $params['order_flag'] = TRUE;
                //Context based news
              
                $params['news_items'] = $this->_buildNewsletterNewsItems($types);
                //Send E-Mail
                $params['email'] = $email;
                $mail_service = \Drupal::service('xnavi_news_mail.mail');
                $key = 'xnavi_news_mail';
                $mail_service->sendMail($params, $key);

            }
        }
        
        $highestActivityId = $this->getHighestActivityId();
        $this->saveLatestActivity($highestActivityId);
        //dsm($highestActivityId);
        return ['#markup' => 'Eine News-Mail wurde verschickt'];
    }

    public function _buildNewsletterNewsItems($types) {
                $params = [];
                $newsletter_log_activity_id = $this->getLatestActivityIdFromLog();
                $highest_activitiy_id = $this->getHighestActivityId();
                $database = \Drupal::database();
                $query = $database->select('activities', 'a');
                $query->fields('a',['nid', 'content_type']);
                $query->condition('activities_id', [$newsletter_log_activity_id, $highest_activitiy_id], 'BETWEEN');
                $result = $query->execute();

                $node_storage = \Drupal::entityTypeManager()->getStorage('node');
                foreach($result as $row) {
                    $nid = $row->nid;
                    $content_type = $row->content_type;
                    if(in_array($content_type, $types)) {
                        $node = $node_storage->load($nid);

                        if ($content_type !== 'event') {
                            $params['news_items'][] = [
                                'title' => $node->title->value, 
                                'type' => $content_type,
                                'nid' => $nid,
                            ];
                        } elseif ($content_type === 'event') {
                            $zeit = explode('T', $node->field_zeit->value);
                            $params['news_items'][] = [
                                'title' => $node->title->value,
                                'date' => $zeit[0],
                                'time' => $zeit[1],
                                'nid' => $nid,
                                'type' => $content_type,
                            ];
                        }
                    }                            
                }

                return $params['news_items'];
    }

    public function sendTestNewsletter($email) {

        //Load configuration
        $config = $this->config('news.settings');
        $greeting_text = $config->get('greeting_text_settings');
        $base_path = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();


        $types = 'event|news';
        $salutation = 'Herr';
        $firstname = 'Max';
        $surname = 'Mustermann';
        $types = explode('|',$types);

        $params['subject'] = 'Test Newsletter';
        $params['name_recipient'] = $this->t('Sehr geehrte(r) ' . $salutation . ' ' . $firstname . ' ' . $surname);
        $params['body'] = $greeting_text;
        $params['token'] = 'TestTokenABCD';
        $params['cta_text'] = 'CTA';
        $params['base_path'] = $base_path;
        $params['cta_url'] = 'node/327'; //TODO: For testing

        /*
        $database = \Drupal::database();
        $query = $database->select('activities', 'a');
        $query->fields('a',['nid', 'content_type']);
        $result = $query->execute();

        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        foreach($result as $row) {
            $nid = $row->nid;
            $content_type = $row->content_type;

            $node = $node_storage->load($nid);

            if ($content_type !== 'event') {
                $params['news_items'][] = ['title' => $node->title->value, 'type' => $content_type];
            } elseif ($content_type === 'event') {
                $zeit = explode('T', $node->field_zeit->value);
                $params['news_items'][] = [
                    'title' => $node->title->value,
                    'date' => $zeit[0],
                    'time' => $zeit[1],
                    'nid' => $nid,
                    'type' => $content_type,
                    ];
            }     
        }*/

        $params['news_items'] = $this->_buildNewsletterNewsItems();


        //Send E-Mail
        $params['email'] = $email;
        $mail_service = \Drupal::service('xnavi_news_mail.mail');
        $key = 'xnavi_news_mail';
        $mail_service->sendMail($params, $key);

        return ['#markup' => 'Eine Test-News-Mail wurde verschickt'];

    }

    public function getAllContentTypes() {
        $types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
        //dsm($types);

        return $types;
    }

    public function getEvents() {
        $events = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'event', 'status' => 1]);
        //dsm($events);
        return $events;
    }

    public function getHighestActivityId() {
        $database = \Drupal::database();

        $query = $database->select('activities', 'a');
        $query->fields('a', ['activities_id']);
        $query->orderBy('activities_id', 'DESC');
        $result = $query->execute();

        foreach($result as $record) {
            $highest_activitiy_id = $record->activities_id;
            break;
        }

        return $highest_activitiy_id;

    }

    public function getLatestActivityIdFromLog() {
        $database = \Drupal::database();
        $query = $database->select('newsletter_log', 'nl');
        $query->fields('nl', ['last_activity']);
        $query->orderBy('last_activity', 'DESC');
        $result = $query->execute();

        foreach($result as $record) {
            $last_activity = $record->last_activity;
            break;
        }

        return $last_activity;
    }

    public function saveLatestActivity($id) {
        $database = \Drupal::database();
        $query = $database->insert('newsletter_log')->fields([
            'date' => date('Y-m-d H:i:s', strtotime(str_replace('-', '/', \Drupal::time()->getCurrentTime()))), 
            'last_activity' => $id]
            )->execute();
        //dsm('Saved');
    }

    public function previewNewsletter() {
        global $base_url;
        $params = $this->getActivities();
        //dsm($params);
        //Load configuration
        $config = $this->config('news.settings');
        $greeting_text = $config->get('greeting_text_settings');

        $params['lower_body'] = $this->t('Sollten Sie keine Anmeldung für diesen Newsletter vorgenommen haben, so ignorieren Sie bitte diese Email. Sie werden keine weiteren Emails von uns erhalten.');

        return [
            '#theme' => 'newsletter_preview',
            '#body' => $this->t($greeting_text),
            '#message' => 
                [
                    'name_recipient' => $this->t('Sehr geehrte(r) Herr Max Mustermann'),
                    //'cta_text' => $this->t('Abonnieren'),
                    //'bold_text' => 'Bold Tesxt',
                    'news_items' => $params['news_items'],
                    'lower_body' => $params['lower_body'],
                    'base_url' => $base_url,
                ],
        ];
    }

    public function getActivities() {
        $database = \Drupal::database();
        $query = $database->select('activities', 'a');
        $query->fields('a',['nid', 'content_type']);
        $result = $query->execute();

        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        foreach($result as $row) {
            $nid = $row->nid;
            $content_type = $row->content_type;

            $node = $node_storage->load($nid);

            if ($content_type !== 'event') {
                $params['news_items'][] = ['title' => $node->title->value, 'type' => $content_type, 'nid' => $nid];
            } elseif ($content_type === 'event') {
                $zeit = explode('T', $node->field_zeit->value);
                $params['news_items'][] = [
                    'title' => $node->title->value,
                    'date' => $zeit[0],
                    'time' => $zeit[1],
                    'nid' => $nid,
                    'type' => $content_type,
                ];
            }     
        }

        return $params;
    }

    public function unsubsribeNewsletter($token) {
        //$token = '31c095f969b8da4d7e2d975d30a90e7c';
        $database = \Drupal::database();
        $query = $database->update('newsletter_order')
                            ->fields(['confirmation_flag' => 0])
                            ->condition('token', $token, '=')
                            ->execute();

        return ['#markup' => '<p>' . $this->t('Sie sind nun von unserem Newsletter abgemeldet. Alle personenbezogenen Daten wurden gelöscht.') . '</p>'];
    }

    public function dashboard() {
        $database = \Drupal::database();
        $query = $database->select('newsletter_order', 'no');
        $query->addField('no', 'types');
        $result = $query->execute();

        foreach($result as $record) {
            $data[] = explode('|', $record->types);
        }

        //$data2 = [];
        foreach($data as $d) {
         //dsm($d);
            foreach($d as $d2) {
                $data2[] = $d2;
            }
        }


        $header = [$this->t('Kategorie'), $this->t('Anzahl')];
        $data3 = array_count_values($data2);
        foreach($data3 as $key => $value) {
            $rows[] = [
                $this->_getName($key), 
                $value];
        }
        //dsm($data2);
        //dsm(array_count_values($data2));

        $database = \Drupal::database();
        $query = $database->select('newsletter_order', 'no');
        $query->addField('no', 'salutation');
        $query->addField('no', 'firstname');
        $query->addField('no', 'surname');
        $query->addField('no', 'company');
        $query->addField('no', 'email');
        $result = $query->execute();

        foreach($result as $record) {
            $email_rows[] = [
                $record->salutation,
                $record->firstname,
                $record->surname,
                $record->company,
                $record->email,               
            ];
        }

        $email_header = ['Anrede', 'Vorname', 'Nachname', 'Unternehmen', 'E-Mail'];

        

        
        $subscribers_count = $this->_getSubcribersCount();

        $link = \Drupal::service('link_generator')->generateFromLink(Link::createFromRoute($this->t('Newsletter absenden'),'news.newsletter_send_newsletter_mail' ));

        $newsletter_config_link = \Drupal::service('link_generator')->generateFromLink(Link::createFromRoute($this->t('Newsletter konfigurieren'),'news.newsletter_send_newsletter_administration_form'));

        $config = $this->config('news.settings');
        $greeting_text = $config->get('greeting_text_settings');

        
        $build['greeting_text'] = [
            '#markup' => '<h4>' . $this->t('Begrüßungstext') . '</h4><br/><p>' . $greeting_text . '</p>',
        ];

        $build['subscribers'] = [
            '#markup' => '<p><strong>' . $this->t('Anzahl Abonennten: '). '</strong> ' . $subscribers_count . '</p>',
        ];

        $build['categories'] = [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $rows,
        ];

        $build['emails'] = [
            '#type' => 'table',
            '#header' => $email_header,
            '#rows' => $email_rows,
        ];

        $build['newsletter_send'] = [
            '#theme' => 'item_list',
            '#list_type' => 'ul',
            '#items' => [$link, $newsletter_config_link],
        ];

        

        
        return $build;

    }

    //TODO: Auslagern in Service oder aehnliches wird auch in NewsletterOrderForm verwendet
    public function _getName($type_name) {
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

    public function _getSubcribersCount() {
        $database = \Drupal::database();

        $query = $database->select('newsletter_order', 'no');

        $num_rows = $query->countQuery()->execute()->fetchField(0);

        return $num_rows;
    }

    public function activityStream() {
        $database = \Drupal::database();

        $query = $database->select('activities', 'a');
        $query->fields('a', ['nid']);

        $result = $query->execute();

        foreach($result as $record) {
            
            $node_storage = \Drupal::entityTypeManager()->getStorage('node');
            $node = $node_storage->load($record->nid);

            $activities[] = [
                'nid' => $node->id(),
                'title' => $node->title->value,
                'bundle' => $node->bundle(),
            ];
        }
        
        dsm($activities);
        return [
            '#theme' => 'activity_stream',
            '#activities' => $activities,
        ];
    }

}