<?php

namespace Drupal\news\Services;

class NewsMail{
    /**
     * @var $route
     */
    protected $mailer;

    /**
     * @var current_user
     */
    protected $current_user;

    /**
     * Constructor
     * 
     * @param $mailer
     * @param $current_user
     * @param $members
     * 
     */
    public function __construct($mailer, $current_user) {
        $this->mailer = $mailer;
        $this->current_user = $current_user;
    }

    /**
     * Sends the mails
     * @param array $params
     */
    public function sendMail($params = array(), $key) {
        //Build vars
        $module = 'news';
        //$key = 'xnavi_news_mail';
        $lang_code = $this->current_user->getPreferredLangcode();
        $params['base_url'] = \Drupal::request()->getSchemeAndHttpHost();
        $params['sender_name'] = $this->current_user->getAccountName(); //TODO: ggf. anpassen
        
        //Send email
        $result = $this->mailer->mail($module, $key, $params['email'], $lang_code, $params, NULL, TRUE);
        if($result['result'] === TRUE) {
            \Drupal::messenger()->addStatus('Mail verschickt');
        }
        //$users = $params['users'];
        //$user_count = count($users);
        //foreach($users as $user) {
        //    $params['name_recipient'] = $user->name;
        //    $result = $this->mailer->mail($module, $key, $user->mail, $lang_code, $params, NULL, TRUE);
            //ksm($result);
        //}
        
        
        /*
        if($result['result'] === true) {
            \Drupal::messenger()->addStatus($user_count . t(' user(s) notified successfully.'));
        } else {
            \Drupal::messenger()->addError(t('Unable to send emails, please contact the admin'));
        }*/

    }
}