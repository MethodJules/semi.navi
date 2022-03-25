<?php

namespace Drupal\bibcite_entity\Entity;

use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the Reference entity.
 *
 * @ingroup bibcite_entity
 *
 * @ContentEntityType(
 *   id = "bibcite_reference",
 *   label = @Translation("Reference"),
 *   label_singular = @Translation("Reference"),
 *   label_plural = @Translation("References"),
 *   bundle_label = @Translation("Reference type"),
 *   handlers = {
 *     "storage_schema" = "Drupal\bibcite_entity\ReferenceStorageSchema",
 *     "storage" = "Drupal\bibcite_entity\ReferenceStorage",
 *     "view_builder" = "Drupal\bibcite_entity\ReferenceViewBuilder",
 *     "list_builder" = "Drupal\bibcite_entity\ReferenceListBuilder",
 *     "views_data" = "Drupal\bibcite_entity\ReferenceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\bibcite_entity\Form\ReferenceForm",
 *       "add" = "Drupal\bibcite_entity\Form\ReferenceForm",
 *       "edit" = "Drupal\bibcite_entity\Form\ReferenceForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\bibcite_entity\ReferenceAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "revision" = "Drupal\entity\Routing\RevisionRouteProvider",
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *   },
 *   show_revision_ui = TRUE,
 *   base_table = "bibcite_reference",
 *   revision_table = "bibcite_reference_revision",
 *   admin_permission = "administer bibcite_reference",
 *   permission_granularity = "bundle",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "status" = "status",
 *     "published" = "status",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "uid" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   },
 *   common_reference_target = TRUE,
 *   bundle_entity_type = "bibcite_reference_type",
 *   links = {
 *     "canonical" = "/bibcite/reference/{bibcite_reference}",
 *     "version-history" = "/bibcite/reference/{bibcite_reference}/revisions",
 *     "revision" = "/bibcite/reference/{bibcite_reference}/revisions/{bibcite_reference_revision}/view",
 *     "revision-revert-form" = "/bibcite/reference/{bibcite_reference}/revisions/{bibcite_reference_revision}/revert",
 *     "revision-delete-form" = "/bibcite/reference/{bibcite_reference}/revisions/{bibcite_reference_revision}/delete",
 *     "edit-form" = "/bibcite/reference/{bibcite_reference}/edit",
 *     "delete-form" = "/bibcite/reference/{bibcite_reference}/delete",
 *     "add-page" = "/bibcite/reference/add",
 *     "delete-multiple-form" = "/admin/content/bibcite/reference/delete",
 *     "collection" = "/admin/content/bibcite/reference",
 *   },
 *   field_ui_base_route = "entity.bibcite_reference_type.edit_form",
 * )
 */
class Reference extends EditorialContentEntityBase implements ReferenceInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;
  use EntityOwnerTrait;

  /**
   * Whether the reference is being previewed or not.
   *
   * The variable is set to public as it will give a considerable performance
   * improvement. See https://www.drupal.org/node/2498919.
   *
   * @var true|null
   *   TRUE if the reference is being previewed and NULL if it is not.
   */
  public $inPreview = NULL;

  /**
   * {@inheritdoc}
   */
  public function cite($style = NULL) {
    // @todo Make a better dependency injection.
    /** @var \Drupal\bibcite\CitationStylerInterface $styler */
    $styler = \Drupal::service('bibcite.citation_styler');

    if ($style) {
      $styler->setStyleById($style);
    }

    $serializer = \Drupal::service('serializer');

    $data = $serializer->normalize($this, 'csl');
    return $styler->render($data);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    /*
     * Main attributes.
     */

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Reference.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setSettings([
        'text_processing' => 0,
      ])
      ->setDefaultValue('');

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\bibcite_entity\Entity\Reference::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 100,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['author'] = BaseFieldDefinition::create('bibcite_contributor')
      ->setLabel(t('Author'))
      ->setRevisionable(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'bibcite_contributor_widget',
        'weight' => 3,
      ])
      ->setDisplayOptions('view', [
        'type' => 'bibcite_contributor_label',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['keywords'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Keywords'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'bibcite_keyword')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete_tags',
        'weight' => 4,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setSettings([
        'handler' => 'default:bibcite_keyword',
        'target_bundles' => ['bibcite_keyword'],
        'auto_create' => TRUE,
        'handler_settings' => [
          'target_bundles' => ['bibcite_keyword'],
          'auto_create' => TRUE,
        ],
      ]);

    /*
     * CSL fields.
     */

    $weight = 5;

    $default_string = function ($label, $hint = '') use (&$weight) {
      $weight++;
      return BaseFieldDefinition::create('string')
        ->setLabel($label)
        ->setDescription($hint)
        ->setRevisionable(TRUE)
        ->setDisplayOptions('view', [
          'label' => 'above',
          'type' => 'string',
          'weight' => $weight,
        ])
        ->setDisplayOptions('form', [
          'type' => 'string_textfield',
          'weight' => $weight,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE)
        ->setDefaultValue('');
    };

    $default_integer = function ($label, $hint = '') use (&$weight) {
      $weight++;
      return BaseFieldDefinition::create('integer')
        ->setLabel($label)
        ->setDescription($hint)
        ->setRevisionable(TRUE)
        ->setDisplayOptions('view', [
          'type' => 'number_integer',
          'weight' => $weight,
        ])
        ->setDisplayOptions('form', [
          'type' => 'number',
          'weight' => $weight,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE)
        ->setDefaultValue(NULL);
    };

    $default_string_long = function ($label, $rows = 1, $hint = '') use (&$weight) {
      $weight++;
      return BaseFieldDefinition::create('string_long')
        ->setLabel($label)
        ->setDescription($hint)
        ->setRevisionable(TRUE)
        ->setDisplayOptions('view', [
          'type' => 'text_default',
          'weight' => $weight,
        ])
        ->setDisplayOptions('form', [
          'type' => 'string_textarea',
          'settings' => [
            'rows' => $rows,
          ],
          'weight' => $weight,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);
    };

    $default_text_long = function ($label, $rows = 1, $hint = '') use (&$weight) {
      $weight++;
      return BaseFieldDefinition::create('text_long')
        ->setLabel($label)
        ->setDescription($hint)
        ->setRevisionable(TRUE)
        ->setDisplayOptions('view', [
          'type' => 'text_default',
          'weight' => $weight,
        ])
        ->setDisplayOptions('form', [
          'type' => 'text_textarea',
          'settings' => [
            'rows' => $rows,
          ],
          'weight' => $weight,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);
    };

    /*
     * Text fields.
     */
    $fields['bibcite_abst_e'] = $default_text_long(t('Abstract'), 4);
    $fields['bibcite_abst_f'] = $default_text_long(t('French Abstract'), 4);
    $fields['bibcite_notes'] = $default_string_long(t('Notes'), 4);
    $fields['bibcite_custom1'] = $default_string_long(t('Custom 1'));
    $fields['bibcite_custom2'] = $default_string_long(t('Custom 2'));
    $fields['bibcite_custom3'] = $default_string_long(t('Custom 3'));
    $fields['bibcite_custom4'] = $default_string_long(t('Custom 4'));
    $fields['bibcite_custom5'] = $default_string_long(t('Custom 5'));
    $fields['bibcite_custom6'] = $default_string_long(t('Custom 6'));
    $fields['bibcite_custom7'] = $default_string_long(t('Custom 7'));
    $fields['bibcite_auth_address'] = $default_string_long(t('Author Address'));

    /*
     * Number fields.
     */
    $fields['bibcite_year'] = $default_integer(t('Year of Publication'), t('Format: yyyy'));

    /*
     * String fields.
     */
    $fields['bibcite_secondary_title'] = $default_string(t('Secondary Title'));
    $fields['bibcite_volume'] = $default_string(t('Volume'));
    $fields['bibcite_edition'] = $default_string(t('Edition'));
    $fields['bibcite_section'] = $default_string(t('Section'));
    $fields['bibcite_issue'] = $default_string(t('Issue'));
    $fields['bibcite_number_of_volumes'] = $default_string(t('Number of Volumes'));
    $fields['bibcite_number'] = $default_string(t('Number'));
    $fields['bibcite_pages'] = $default_string(t('Number of Pages'));
    $fields['bibcite_date'] = $default_string(t('Date Published'), t('Format: mm/yyyy'));
    $fields['bibcite_type_of_work'] = $default_string(t('Type of Work'), t('Masters Thesis'));
    $fields['bibcite_lang'] = $default_string(t('Publication Language'));
    $fields['bibcite_reprint_edition'] = $default_string(t('Reprint Edition'));
    $fields['bibcite_publisher'] = $default_string(t('Publisher'));
    $fields['bibcite_place_published'] = $default_string(t('Place Published'));
    $fields['bibcite_issn'] = $default_string(t('ISSN Number'));
    $fields['bibcite_isbn'] = $default_string(t('ISBN Number'));
    $fields['bibcite_accession_number'] = $default_string(t('Accession Number'));
    $fields['bibcite_call_number'] = $default_string(t('Call Number'));
    $fields['bibcite_other_number'] = $default_string(t('Other Numbers'));
    $fields['bibcite_citekey'] = $default_string(t('Citation Key'));
    $fields['bibcite_citekey']->setCustomStorage(TRUE);
    $fields['bibcite_url'] = $default_string(t('URL'));
    $fields['bibcite_doi'] = $default_string(t('DOI'));
    $fields['bibcite_research_notes'] = $default_string(t('Research Notes'));
    $fields['bibcite_tertiary_title'] = $default_string(t('Tertiary Title'));
    $fields['bibcite_short_title'] = $default_string(t('Short Title'));
    $fields['bibcite_alternate_title'] = $default_string(t('Alternate Title'));
    $fields['bibcite_translated_title'] = $default_string(t('Translated Title'));
    $fields['bibcite_original_publication'] = $default_string(t('Original Publication'));
    $fields['bibcite_other_author_affiliations'] = $default_string(t('Other Author Affiliations'));
    $fields['bibcite_remote_db_name'] = $default_string(t('Remote Database Name'));
    $fields['bibcite_remote_db_provider'] = $default_string(t('Remote Database Provider'));
    $fields['bibcite_label'] = $default_string(t('Label'));
    $fields['bibcite_access_date'] = $default_string(t('Access Date'));
    $fields['bibcite_refereed'] = $default_string(t('Refereed Designation'));

    $fields['bibcite_pmid'] = $default_string(t('PMID'));

    /*
     * Entity dates.
     */

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setRevisionable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
      ->setRevisionable(TRUE);

    $fields['status']
      ->setTranslatable(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 120,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no revision author has been set explicitly, make the revision owner
    // the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {

    parent::preSaveRevision($storage, $record);
    /* @see \Drupal\node\Entity\Node::preSaveRevision() */
    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log_message) || $record->revision_log_message === '')) {
      $record->revision_log_message = $this->original->revision_log_message->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if (strpos($this->getEntityType()->getLinkTemplate($rel), $this->getEntityTypeId() . '_revision') !== FALSE) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function generateCitekey() {
    $pattern = '';

    $type_storage = $this->entityTypeManager()->getStorage('bibcite_reference_type');
    /** @var \Drupal\bibcite_entity\Entity\ReferenceTypeInterface $bundle */
    $bundle = $type_storage->load($this->bundle());
    if ($bundle && !$pattern = $bundle->getCitekeyPattern()) {
      // Fallback to global pattern if it's not configured on bundle level.
      $pattern = \Drupal::config('bibcite_entity.reference.settings')->get('citekey.pattern');
    }

    return \Drupal::token()->replace($pattern, ['bibcite_reference' => $this]);
  }

}
