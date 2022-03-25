<?php

namespace Drupal\bibcite_entity\Form;

use Drupal\bibcite_entity\Entity\ReferenceType;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a reference revision.
 *
 * @internal
 */
class ReferenceRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The reference revision.
   *
   * @var \Drupal\bibcite_entity\entity\Reference
   */
  protected $revision;

  /**
   * The reference storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $referenceStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new ReferenceRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $referenceStorage
   *   The reference storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $referenceStorage, Connection $connection, DateFormatterInterface $date_formatter) {
    $this->referenceStorage = $referenceStorage;
    $this->connection = $connection;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var \Drupal\Core\Database\Connection $connection */
    $connection = $container->get('database');
    return new static(
      $container->get('entity_type.manager')->getStorage('bibcite_reference'),
      $connection,
      $date_formatter
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bibcite_reference_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.bibcite_reference.version_history', ['bibcite_reference' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bibcite_reference_revision = NULL) {
    $this->revision = $bibcite_reference_revision;
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->referenceStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('bibcite')->notice('@type: deleted %title revision %revision.', ['@type' => $this->revision->bundle(), '%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $type = ReferenceType::load($this->revision->bundle());
    $type_label = $type ? $type->label() : $this->revision->bundle();
    $this->messenger()
      ->addStatus($this->t('Revision from %revision-date of @type %title has been deleted.', [
        '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
        '@type' => $type_label,
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.bibcite_reference.canonical',
      ['bibcite_reference' => $this->revision->id()]
    );
    $query = $this->connection->select('bibcite_reference_revision', 'br');
    $query->addExpression('COUNT(DISTINCT(revision_id))');
    $query->condition('id', $this->revision->id());
    $count = $query->execute()->fetchField();
    if ($count > 1) {
      $form_state->setRedirect(
        'entity.bibcite_reference.version_history',
        ['bibcite_reference' => $this->revision->id()]
      );
    }
  }

}
