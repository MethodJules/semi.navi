<?php

namespace Drupal\bibcite_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Contributor edit forms.
 *
 * @ingroup bibcite_entity
 */
class ContributorForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#prefix'] = '<div id="bibcite-contributor-form-wrapper">';
    $form['#suffix'] = '</div>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Unset submitted "name" field value so it's not set on entity and
    // other name parts are not overwritten by parsed values when entity is
    // saved.
    $form_state->unsetValue('name');

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()
          ->addStatus($this->t('Created the %label Contributor.', [
            '%label' => $entity->label(),
          ]));
        break;

      default:
        $this->messenger()
          ->addStatus($this->t('Saved the %label Contributor.', [
            '%label' => $entity->label(),
          ]));
    }
    $form_state->setRedirect('entity.bibcite_contributor.canonical', ['bibcite_contributor' => $entity->id()]);
  }

}
