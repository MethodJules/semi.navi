<?php

namespace Drupal\content_entity_example\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\content_entity_example\ContactInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the ContentEntityExample entity.
 *
 * @ingroup content_entity_example
 *
 * [...]
 *
 *  The following annotation is the actual definition of the entity type which
 *  is read and cached. Don't forget to clear cache after changes.
 *
 * @ContentEntityType(
 *   id = "content_entity_example_contact",
 *   label = @Translation("Contact entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\content_entity_example\Entity\Controller\ContactListBuilder",
 *     "form" = {
 *       "add" = "Drupal\content_entity_example\Form\ContactForm",
 *       "edit" = "Drupal\content_entity_example\Form\ContactForm",
 *       "delete" = "Drupal\content_entity_example\Form\ContactDeleteForm",
 *     },
 *     "access" = "Drupal\content_entity_example\ContactAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "contact",
 *   admin_permission = "administer content_entity_example entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/content_entity_example_contact/{content_entity_example_contact}",
 *     "edit-form" = "/content_entity_example_contact/{content_entity_example_contact}/edit",
 *     "delete-form" = "/contact/{content_entity_example_contact}/delete",
 *     "collection" = "/content_entity_example_contact/list"
 *   },
 *   field_ui_base_route = "content_entity_example.contact_settings",
 * )
 */
 class Contact extends ContentEntityBase implements ContactInterface {
    
    /**
     * {@inheritdoc}
     * 
     * Define the field properties here.
     * 
     * Field name, type and size determine the table structure
     * 
     * In addition, we can define how the field and its content can be manipulated
     * in the GUI. The behaviour of the widgets used can be determined here.
     * 
     */

     public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
     {
         
        //Standard field, used as unique if primary index
        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setDescription(t('The ID of the Contact entity'))
            ->setReadOnly(TRUE);

        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The uuid of the Contact entity'))
            ->setReadOnly(TRUE);

        // Name field for the contact
        // We set display options for the view as well as the form.
        // Users with correct privileges can change the view and 
        // edit configuration
        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('The name of the Contact entity'))
            ->setSettings(array(
                'default_value' => '',
                'max_length' => 255,
                'text_processing' => 0,
            ))
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -6,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'string_textfield',
                'weight' => -6,
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['first_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('First Name'))
            ->setDescription(t('The first name of the Contact entity'))
            ->setSettings(array(
                'default_value' => '',
                'max_length' => 255,
                'text_processing' => 0,
            ))
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'string_textfield',
                'weight' => -5,
            ))
            ->setDisplayConfigurable('view', TRUE)
            ->setDisplayConfigurable('form', TRUE);

            // Favorite dessert field for the contact
            // ListTextType with a drop down menu widget
            // The values shown in the menu are 'ice cream' and 'cake' and 'pie'
            // In the view field content is shown as string.
            // In the form the choices are presented as options list
            $fields['favorite_dessert'] = BaseFieldDefinition::create('list_string')
                ->setLabel(t('Favorite Dessert'))
                ->setDescription((t('The favorite dessert of the Contact entity')))
                ->setSettings(array(
                    'allowed_values' => array(
                        'ice_cream' => 'Ice cream',
                        'cake' => 'Cake',
                        'pie' => 'Pie'
                    )
                ))
                ->setDisplayOptions('view', array(
                    'label' => 'above',
                    'type' => 'string',
                    'weight' => -4,
                  ))
                ->setDisplayOptions('form', array(
                    'type' => 'options_select',
                    'weight' => -4,
                  ))
                ->setDisplayConfigurable('form', TRUE)
                ->setDisplayConfigurable('view', TRUE);

                // Owner field of the contact.
                // Entity reference field, holds the reference to the user object
                // The view shows the user name field of the user.
                // The form presents a auto complete field for the user name
                $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
                    ->setLabel(t('User Name'))
                    ->setDescription(t('The name of the associated user'))
                    ->setSetting('target_type', 'user')
                    ->setSetting('handler', 'default')
                    ->setDisplayOptions('view', array(
                        'label' => 'above',
                        'type'  => 'author',
                        'weight' => -3,
                    ))
                    ->setDisplayConfigurable('form', TRUE)
                    ->setDisplayConfigurable('view', TRUE);
                
                $fields['langcode'] = BaseFieldDefinition::create('language')
                    ->setLabel(t('Language code'))
                    ->setDescription(t('The language code of ContentEntityExample entity.'));
                $fields['created'] = BaseFieldDefinition::create('created')
                    ->setLabel(t('created'))
                    ->setDescription(t('The time that the entity was created.'));
                $fields['changed'] = BaseFieldDefinition::create('changed')
                    ->setLabel(t('Changed'))
                    ->setDescription(t('The time that the entity was last edited'));

                return $fields;
        
     }
 }