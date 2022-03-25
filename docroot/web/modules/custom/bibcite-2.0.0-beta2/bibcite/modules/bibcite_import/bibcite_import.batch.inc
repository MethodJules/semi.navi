<?php

/**
 * @file
 * Batch functions.
 */

use Drupal\bibcite_entity\Entity\Reference;
use Drupal\bibcite\Plugin\BibciteFormatInterface;

/**
 * Batch operation callback. Denormalize entries and try to save entity.
 *
 * @param array $entries
 *   Array of parsed entries.
 * @param \Drupal\bibcite\Plugin\BibciteFormatInterface $format
 *   Instance of format plugin.
 * @param array $context
 *   The batch context array, passed by reference.
 */
function bibcite_import_batch_callback(array $entries, BibciteFormatInterface $format, array &$context) {
  if (empty($context['results'])) {
    $context['success'] = FALSE;
    $context['results']['success'] = [];
    $context['results']['errors'] = [];
    $context['results']['inaccessible_entities'] = 0;
  }

  /** @var \Symfony\Component\Serializer\Serializer $serializer */
  $serializer = \Drupal::service('serializer');

  $config = \Drupal::config('bibcite_import.settings');
  $denormalize_context = [
    'contributor_deduplication' => $config->get('settings.contributor_deduplication'),
    'keyword_deduplication' => $config->get('settings.keyword_deduplication'),
  ];

  foreach ($entries as $entry) {
    $entity = '';
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    try {
      $entity = $serializer->denormalize($entry, Reference::class, $format->getPluginId(), $denormalize_context);
    }
    catch (UnexpectedValueException $e) {
      $message = [
        t('Entry has not been parsed.'),
        $e->getMessage(),
      ];
      \Drupal::logger('bibcite_import')
        ->error(implode("\n", $message));
      $context['results']['errors'][] = implode("\n", $message);
    }

    if (!empty($entity)) {
      if (!$entity->access('create')) {
        $context['results']['inaccessible_entities']++;
        continue;
      }
      try {
        if ($entity->save()) {
          $context['results']['success'][] = $entity->id() . ' : ' . $entity->label();
          $context['success'] = TRUE;
        }
      }
      catch (Exception $e) {
        $message = [
          t('Entity can not be saved.'),
          t('Label: @label', ['@label' => $entity->label()]),
          '<pre>',
          $e->getMessage(),
          '</pre>',
        ];
        \Drupal::logger('bibcite_import')
          ->error(implode("\n", $message));
        $context['results']['errors'][] = $entity->label();
      }

      $context['message'] = $entity->label();
    }
  }
}

/**
 * Complete a batch process.
 *
 * @param bool $success
 *   A boolean indicating whether the batch has completed successfully.
 * @param array $results
 *   The value set in $context['results'] by callback_batch_operation().
 * @param array|bool $operations
 *   If $success is FALSE, contains the operations that remained unprocessed.
 */
function bibcite_import_batch_finished(bool $success, array $results, $operations) {
  $messenger = \Drupal::messenger();
  if ($success) {
    if (!empty($results['success'])) {
      $message = \Drupal::translation()
        ->formatPlural(
          count($results['success']),
          'One entity has been processed.',
          '@count entities have been processed.'
        );
      $messenger->addStatus($message);
    }
    else {
      $message = \Drupal::translation()
        ->translate('Entities have not been found. Please check file and format.');
      $messenger->addWarning($message);
    }
  }
  else {
    $message = t('Import has been finished with an error.');
    $messenger->addError($message);
  }

  if (!empty($results['errors'])) {
    $error_count_message = \Drupal::translation()
      ->formatPlural(
        count($results['errors']),
        'One entry has not been processed:',
        '@count entries have not been processed:'
      );
    $last_ten_errors = array_slice($results['errors'], -10);
    $error_message = [
      $error_count_message,
      implode("\n", $last_ten_errors),
    ];
    $messenger->addError(implode("\n", $error_message));
  }
  if (!empty($results['inaccessible_entities'])) {
    $inaccessible_entities = $results['inaccessible_entities'];
    $messenger->addWarning(\Drupal::translation()
      ->formatPlural(
        $inaccessible_entities,
        "@count reference has not been created because you do not have the necessary permissions.",
        "@count references have not been created because you do not have the necessary permissions."
      )
    );
  }
}
