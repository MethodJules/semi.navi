<?php

namespace Drupal\bibcite_entity\Plugin\views\filter;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\bibcite_entity\Entity\Keyword;
use Drupal\views\Plugin\views\filter\ManyToOne;

/**
 * Filter handler for keywords.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("bibcite_keyword_id")
 */
class KeywordId extends ManyToOne {

  protected $alwaysMultiple = TRUE;

  /**
   * Validated input values.
   *
   * @var array
   */
  private $validatedExposedInput;

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $keywords = $this->value ? Keyword::loadMultiple($this->value) : [];
    $default_value = EntityAutocomplete::getEntityLabels($keywords);
    $form['value'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Keywords'),
      '#description' => $this->t('Enter a comma separated list of keywords.'),
      '#target_type' => 'bibcite_keyword',
      '#tags' => TRUE,
      '#default_value' => $default_value,
      '#process_default_value' => $this->isExposed(),
    ];

    $user_input = $form_state->getUserInput();
    if ($form_state->get('exposed') && !isset($user_input[$this->options['expose']['identifier']])) {
      $user_input[$this->options['expose']['identifier']] = $default_value;
      $form_state->setUserInput($user_input);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function valueValidate($form, FormStateInterface $form_state) {
    $ids = [];
    if ($values = $form_state->getValue(['options', 'value'])) {
      foreach ($values as $value) {
        $ids[] = $value['target_id'];
      }
      sort($ids);
    }
    $form_state->setValue(['options', 'value'], $ids);
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    $rc = parent::acceptExposedInput($input);

    if ($rc) {
      // If we have previously validated input, override.
      if (isset($this->validatedExposedInput)) {
        $this->value = $this->validatedExposedInput;
      }
    }

    return $rc;
  }

  /**
   * {@inheritdoc}
   */
  public function validateExposed(&$form, FormStateInterface $form_state) {
    if (empty($this->options['exposed'])) {
      return;
    }

    if (empty($this->options['expose']['identifier'])) {
      return;
    }

    $identifier = $this->options['expose']['identifier'];
    $input = $form_state->getValue($identifier);

    if ($this->options['is_grouped'] && isset($this->options['group_info']['group_items'][$input])) {
      $this->operator = $this->options['group_info']['group_items'][$input]['operator'];
      $input = $this->options['group_info']['group_items'][$input]['value'];
    }

    $ids = [];
    $values = $form_state->getValue($identifier);
    if ($values && (!$this->options['is_grouped'] || ($this->options['is_grouped'] && ($input != 'All')))) {
      foreach ($values as $value) {
        $ids[] = $value['target_id'];
      }
    }

    if ($ids) {
      $this->validatedExposedInput = $ids;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid::valueSubmit()
   * @see \Drupal\user\Plugin\views\filter\Name::valueSubmit()
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    // Do nothing and do not execute parent method.
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    // Set up for the parent summary.
    $this->valueOptions = [];

    if ($this->value) {
      $result = \Drupal::entityTypeManager()->getStorage('bibcite_keyword')
        ->loadByProperties(['id' => $this->value]);
      /** @var \Drupal\bibcite_entity\Entity\Keyword $keyword */
      foreach ($result as $keyword) {
        if ($keyword->id()) {
          $this->valueOptions[$keyword->id()] = $keyword->getName();
        }
      }
    }

    return parent::adminSummary();
  }

}
