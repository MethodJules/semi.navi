<?php

namespace Drupal\knowledgemap\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'knowledgemap_widget' widget.
 *
 * @FieldWidget(
 *   id = "knowledgemap_widget",
 *   module = "knowledgemap",
 *   label = @Translation("Knowledgemap widget"),
 *   field_types = {
 *     "knowledgemap_field_type"
 *   }
 * )
 */
class KnowledgemapWidget extends ImageWidget {


  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

/*    $elements['#attached']['library'][] = 'knowledgemap/knowledgemap-base';
    $elements['#attached']['library'][] = 'knowledgemap/knowledgemap-add';*/
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    return $element;
  }

  public function formSingleElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formSingleElement($items, $delta, $element, $form, $form_state);
    return $element;
  }


}
