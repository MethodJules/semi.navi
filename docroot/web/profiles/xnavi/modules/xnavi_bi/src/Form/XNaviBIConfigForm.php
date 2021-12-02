<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 06.09.19
 * Time: 13:37
 */

namespace Drupal\xnavi_bi\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class XNaviBIConfigForm extends ConfigFormBase
{
    protected function getEditableConfigNames()
    {
        return ['xnavi_bi.adminsettings'];
    }

    public function getFormId()
    {
        return 'xnavi_bi_admin_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form = parent::buildForm($form, $form_state);
        $settings = $this->config('xnavi_bi.adminsettings');

        $form['xnavi_bi_admin']['vocabularies'] = [
            '#title' => $this->t('Vocabularies'),
            '#type' => 'textfield',
            '#description' => $this->t('Please enter here all vocabulary machine names seperated by a whitespace which should be filtered.'),
            '#default_value' => $settings->get('vocabularies'),
        ];



        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);
        $this->config('xnavi_bi.adminsettings')
            ->set('vocabularies', $form_state->getValue('vocabularies'))
            ->save();
    }


}
