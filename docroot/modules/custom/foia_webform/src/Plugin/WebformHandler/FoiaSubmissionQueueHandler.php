<?php

namespace Drupal\foia_webform\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "foia_submission_queue",
 *   label = @Translation("FOIA Submission Queue"),
 *   category = @Translation("Queueing"),
 *   description = @Translation("Queues a webform submission to be sent later."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class FoiaSubmissionQueueHandler extends EmailWebformHandler {

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $state = $webform_submission->getWebform()->getSetting('results_disabled') ? WebformSubmissionInterface::STATE_COMPLETED : $webform_submission->getState();
    if (in_array($state, $this->configuration['states'])) {
      $this->queueSubmission($webform_submission);
    }
  }

  /**
   * Adds the submission to the foia_submissions queue.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   The webform submission object.
   */
  private function queueSubmission(WebformSubmissionInterface $webform_submission) {
    // @var QueueFactory $queue_factory
    $queue_factory = \Drupal::service('queue');

    // @var QueueInterface $queue
    $foia_submission_queue = $queue_factory->get('foia_submissions');
    $submission = new \stdClass();
    $submission->sid = $webform_submission->id();

    // Log the form submission.
    \Drupal::logger('foia_webform')
      ->info('FOIA form submission #%sid added to queue.',
        [
          '%sid' => $webform_submission->id(),
          'link' => $webform_submission->toLink($this->t('View'))->toString(),
        ]
      );

    $foia_submission_queue->createItem($submission);
  }

}
