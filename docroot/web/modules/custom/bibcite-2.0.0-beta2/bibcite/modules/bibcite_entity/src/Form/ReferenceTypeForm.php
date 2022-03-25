<?php

namespace Drupal\bibcite_entity\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Reference type form.
 */
class ReferenceTypeForm extends BundleEntityFormBase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs the NodeTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager */
    $entity_field_manager = $container->get('entity_field.manager');
    return new static(
      $entity_field_manager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\bibcite_entity\Entity\ReferenceTypeInterface $reference_type */
    $reference_type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $reference_type->label(),
      '#description' => $this->t('Label for the Reference type.'),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $reference_type->getDescription(),
      '#description' => $this->t('Short description of Reference type.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $reference_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\bibcite_entity\Entity\ReferenceType::load',
      ],
      '#disabled' => !$reference_type->isNew(),
    ];

    $form['citekey_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Citation key pattern'),
      '#description' => $this->t('Pattern for citation key automatic generation if value is not set. If pattern is not set <a href=":settings">global pattern</a> will be used.', [':settings' => Url::fromRoute('bibcite_entity.reference.settings')->toString()]),
      '#maxlength' => 255,
      '#default_value' => $reference_type->getCitekeyPattern(),
    ];

    $form['token_help'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['bibcite_reference'],
      '#global_types' => TRUE,
    ];

    $form['override'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override default properties'),
      '#default_value' => $reference_type->isRequiredOverride(),
    ];

    $form['overrides'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          [':input[name="override"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $form['overrides']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field name'),
        $this->t('Label'),
        $this->t('Hint'),
        $this->t('Required'),
      ],
      '#tree' => TRUE,
    ];

    $excluded_fields = [
      'id',
      'uuid',
      'langcode',
      'created',
      'changed',
      'type',
      'author',
      'revision_id',
      'revision_created',
      'revision_user',
      'revision_log_message',
      'status',
      'revision_default',
      'path',
      'metatag',
    ];

    $fields_configuration = $reference_type->getFields();
    $fields = \Drupal::service('entity_field.manager')->getBaseFieldDefinitions('bibcite_reference', 'bibcite_reference');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field */
    foreach ($fields as $field) {
      $field_name = $field->getName();
      if (in_array($field_name, $excluded_fields)) {
        continue;
      }

      $field_configuration = !empty($fields_configuration[$field_name])
        ? $fields_configuration[$field_name]
        : [];

      $form['overrides']['fields'][$field_name] = [
        'name' => [
          '#markup' => new FormattableMarkup('@label (@name)', [
            '@label' => $field->getLabel(),
            '@name' => $field_name,
          ]),
        ],
        'label' => [
          '#type' => 'textfield',
          '#size' => 30,
          '#default_value' => isset($field_configuration['label']) ? $field_configuration['label'] : '',
        ],
        'hint' => [
          '#type' => 'textfield',
          '#size' => 30,
          '#default_value' => isset($field_configuration['hint']) ? $field_configuration['hint'] : $field->getDescription(),
        ],
        'required' => [
          '#type' => 'checkbox',
          '#default_value' => isset($field_configuration['required']) ? $field_configuration['required'] : FALSE,
        ],
      ];
    }

    /* @see \Drupal\node\NodeTypeForm::form() */
    $form['additional_settings'] = [
      '#type' => 'vertical_tabs',
    ];
    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add reference type');
      /**
       * Create a reference with a fake bundle using the type's UUID so that we can
       * get the default values for workflow settings.
       *
       * @todo Make it possible to get default values without an entity.
       *   https://www.drupal.org/node/2318187
       */
      $reference = $this->entityTypeManager->getStorage('bibcite_reference')->create(['type' => $this->getEntity()->uuid()]);
    }
    else {
      $form['#title'] = $this->t('Edit %label reference type', ['%label' => $reference_type->label()]);
      // Create a reference to get the current values for workflow settings fields.
      $reference = $this->entityTypeManager->getStorage('bibcite_reference')->create(['type' => $this->getEntity()->id()]);
    }

    $form['workflow'] = [
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'additional_settings',
    ];
    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => t('Published'),
      '#default_value' => $reference->status->value,
      '#group' => 'workflow',
    ];
    $form['revision'] = [
      '#type' => 'checkbox',
      '#title' => t('Create new revision'),
      '#default_value' => $reference_type->shouldCreateNewRevision(),
      '#description' => t('Users with the <em>Administer Bibliography &amp; Citation</em> permission will be able to override these options.'),
      '#group' => 'workflow',
    ];

    $form['submission'] = [
      '#type' => 'details',
      '#title' => t('Submission form settings'),
      '#group' => 'additional_settings',
      '#open' => TRUE,
    ];
    $form['submission']['preview_mode'] = [
      '#type' => 'radios',
      '#title' => t('Preview before submitting'),
      '#default_value' => $reference_type->getPreviewMode(),
      '#options' => [
        DRUPAL_DISABLED => t('Disabled'),
        DRUPAL_OPTIONAL => t('Optional'),
        DRUPAL_REQUIRED => t('Required'),
      ],
    ];
    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\bibcite_entity\Entity\ReferenceTypeInterface $reference_type */
    $reference_type = $this->entity;
    $reference_type->setNewRevision($form_state->getValue('revision'));
    $fields = $this->entityFieldManager->getFieldDefinitions('bibcite_reference', $reference_type->id());
    $status = $reference_type->save();
    $fields['status']->getConfig($reference_type->id())
      ->setDefaultValue($form_state->getValue('status'))
      ->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('The %label Reference type has been added.', [
          '%label' => $reference_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('The %label Reference type has been updated.', [
          '%label' => $reference_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($reference_type->toUrl('collection'));
  }

}
