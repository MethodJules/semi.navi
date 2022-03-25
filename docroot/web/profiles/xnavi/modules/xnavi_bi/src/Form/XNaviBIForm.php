<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 01.06.19
 * Time: 23:12
 */

namespace Drupal\xnavi_bi\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class XNaviBIForm extends FormBase
{
    public function getFormId()
    {
        return 'xnavi_bi_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['actions']['extra_actions'] = array(
            '#type' => 'dropbutton',
            '#links' => array(
                'simple_form' => array(
                    'title' => $this
                        ->t('Simple Form'),
                    'url' => Url::fromRoute('xnavi_bi.content'),
                ),
                'demo' => array(
                    'title' => $this
                        ->t('Build Demo'),
                    'url' => Url::fromRoute('xnavi_bi.content'),
                ),
            ),
        );
        $form['actions']['network'] = [
            '#type' => 'link',
            '#title' => $this->t('Network'),
            '#url' => Url::fromRoute('xnavi_bi.content'),
            '#attributes' => [
                'class' => ['btn', 'btn-info', 'btn-block'],
            ],
        ];

        $form['actions']['charts'] = [
            '#type' => 'link',
            '#title' => $this->t('Charts'),
            '#url' => Url::fromRoute('xnavi_bi.content'),
            '#attributes' => [
                'class' => ['btn', 'btn-info'],
            ],
        ];


        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // TODO: Implement submitForm() method.
        drupal_set_message('Hallo');
    }

}