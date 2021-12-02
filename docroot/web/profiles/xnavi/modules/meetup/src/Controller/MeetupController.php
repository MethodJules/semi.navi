<?php

namespace Drupal\meetup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MeetupController extends ControllerBase {
    public function content() {
        return ['#markup' => 'Meetup Modul Content'];
    }

    public function participation($nid) {

        $uid = 0;
        //Check if user is anonymous
        if(\Drupal::currentUser()->isAuthenticated()) {
          $uid = \Drupal::currentUser()->id();
          //return $this->redirect('meetup.anonymous_user');
        }

        $this->writeToDatabase($uid, $nid, 'participation');
        return [];
    }

    public function interest($nid) {
        $uid = \Drupal::currentUser()->id();
        $this->writeToDatabase($uid, $nid, 'interest');
        return [];
    }

    public function no_participation($nid){
        $uid = \Drupal::currentUser()->id();
        $this->writeToDatabase($uid, $nid, 'no participation');
        return [];
    }

    public function no_interest($nid){
        $uid = \Drupal::currentUser()->id();
        $this->writeToDatabase($uid, $nid, 'no interest');
        return [];
    }

    public function participation_update($nid) {
        $uid = \Drupal::currentUser()->id();
        $this->writeToDatabase($uid, $nid, 'participation_update');
        return [];
    }

    public function interest_update($nid) {
        $uid = \Drupal::currentUser()->id();
        $this->writeToDatabase($uid, $nid, 'interest_update');
        return [];
    }

    //TODO: Write to database
    public function writeToDatabase($uid, $nid, $flag) {
        $connection = \Drupal::database();



        if($flag === 'participation') {
          if($uid === 0) {
            $anonParticipants = $this->getAnonymousParticipation($nid);
            $anonInterested = $this->getAnonymousInterest($nid);

            if($anonParticipants > 0) {
              $result = $connection->update('meetup')
                ->fields([
                  'participation' => $anonParticipants + 1,
                ])
                ->condition('uid', $uid)
                ->condition('nid', $nid)
                ->execute();
            } else {
              $result = $connection->insert('meetup')
                ->fields([
                  'uid' => $uid,
                  'nid' => $nid,
                  'participation' => 1,
                  'interest' => $anonInterested,
                ])
                ->execute();
            }
          } else {
            $result = $connection->insert('meetup')
              ->fields([
                'uid' => $uid,
                'nid' => $nid,
                'participation' => 1,
                'interest' => 1,
              ])
              ->execute();
          }
        }

        if($flag === 'participation_update') {
            $result = $connection->update('meetup')
                ->fields([
                    'participation' => 1,
                ])
                ->condition('uid', $uid)
                ->condition('nid', $nid)
                ->execute();
        }

      if($flag === 'interest') {
        if($uid === 0) {
          $anonParticipants = $this->getAnonymousParticipation($nid);
          $anonInterested = $this->getAnonymousInterest($nid);

          if($anonInterested > 0) {
            $result = $connection->update('meetup')
              ->fields([
                'interest' => $anonInterested + 1,
              ])
              ->condition('uid', $uid)
              ->condition('nid', $nid)
              ->execute();
          } else {
            $result = $connection->insert('meetup')
              ->fields([
                'uid' => $uid,
                'nid' => $nid,
                'participation' => $anonParticipants,
                'interest' => 1,
              ])
              ->execute();}
        }

        else {
          $result = $connection->insert('meetup')
            ->fields([
              'uid'           => $uid,
              'nid'           => $nid,
              'participation' => 0,
              'interest'      => 1,
            ])
            ->execute();
        }
      }

        if($flag === 'interest_update') {
            $result = $connection->update('meetup')
                ->fields([
                    'interest' => 1,
                ])
                ->condition('uid', $uid)
                ->condition('nid', $nid)
                ->execute();
        }

        if($flag === 'no participation') {
            $result = $connection->update('meetup')
                ->fields([
                    'participation' => 0,
                ])
                ->condition('uid', $uid)
                ->condition('nid', $nid)
                ->execute();
        }

        if($flag === 'no interest') {
            $result = $connection->update('meetup')
                ->fields([
                    'interest' => 0,
                ])
                ->condition('uid', $uid)
                ->condition('nid', $nid)
                ->execute();
        }
    }

    public function anonymous_user() {
        $link = \Drupal::service('link_generator')->generateFromLink(Link::createFromRoute(t('Hier'), 'user.login'));
        $html = '<p>Um die Teilnahme oder das Interesse an einer Veranstaltung zu bestätigen müssen Sie eingeloggt sein.</p>';
        $html .= '<p>' . $link . ' geht es zur Anmeldemaske </p>';

        return ['#markup' => $html];

    }

    public function getAnonymousParticipation($nid) {
      $connection = \Drupal::database();
      $query = $connection->select('meetup', 'meetup');
      $query->fields('meetup', ['participation']);
      $query->condition('meetup.uid', 0);
      $query->condition('meetup.nid', $nid);
      $count = array_sum($query->execute()->fetchCol());

      if ($count) {
        return $count;
      }

      return 0;
    }

    public function getAnonymousInterest($nid) {
      $connection = \Drupal::database();
      $query = $connection->select('meetup', 'meetup');
      $query->fields('meetup', ['interest']);
      $query->condition('meetup.uid', 0);
      $query->condition('meetup.nid', $nid);
      $count = array_sum($query->execute()->fetchCol());

      if ($count) {
        return $count;
      }

      return 0;
    }


}
