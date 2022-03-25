<?php

namespace Drupal\bibcite_entity\Plugin\Field\FieldWidget;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'bibcite_parse_name' widget.
 *
 * @FieldWidget(
 *   id = "bibcite_parse_name",
 *   label = @Translation("Parse name"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ParseNameWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];
    /** @var \Drupal\bibcite_entity\Entity\ContributorInterface $entity */
    $entity = $form_state->getFormObject()->getEntity();

    $element['value'] = [
      '#title' => $this->t('Full name'),
      '#title_display' => 'invisible',
      '#type' => 'textfield',
      '#description' => $this->t('Parse full name and overwrite name parts by parse result.<br><strong>Note:</strong> parts can be set to empty value if not presented in entered name.'),
      '#default_value' => $entity->getName(),
    ];

    $element['parse'] = [
      '#type' => 'submit',
      '#name' => 'parse',
      '#value' => $this->t('Parse'),
      '#limit_validation_errors' => [array_merge($parents, [$field_name])],
      // Add custom validate callback so the entity form's class ::validateForm()
      // method is not executed.
      '#validate' => [[$this, 'validate']],
      '#submit' => [[$this, 'submit']],
      '#ajax' => [
        'callback' => [$this, 'ajaxParseName'],
        'wrapper' => 'bibcite-contributor-form-wrapper',
      ],
    ];
    $element['#theme_wrappers'] = ['fieldset' => ['#title' => $this->t('Full name')]];
    return $element;
  }

  /**
   * Widget validate callback for the "Parse" button.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * Widget submit callback for the "Parse" button.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function submit(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\bibcite_entity\Entity\ContributorInterface $entity */
    $entity = $form_state->getFormObject()->getEntity();

    $entity->name = $form_state->getValue('name');

    // Unset name parts from user input so default values on form are
    // populated from entity instead of submitted values.
    $user_input = $form_state->getUserInput();
    foreach (Contributor::getNameParts() as $part) {
      unset($user_input[$part]);
    }
    $form_state->setUserInput($user_input);
    $form_state->setRebuild();
  }

  /**
   * Ajax widget submit callback for the "Parse" button.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|array
   *   Ajax Response.
   */
  public function ajaxParseName(array &$form, FormStateInterface $form_state) {
    // Just refresh the whole form.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return parent::isApplicable($field_definition) && $field_definition->getName() === 'name' && $field_definition->getTargetEntityTypeId() === 'bibcite_contributor';
  }

}
