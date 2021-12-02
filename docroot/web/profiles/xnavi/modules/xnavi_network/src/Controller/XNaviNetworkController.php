<?php

namespace Drupal\xnavi_network\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class XNaviNetworkController extends ControllerBase {

    public function content() {
        return ['#markup' => 'XNavi Network'];
    }

    public function getNetworkData($term) {
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');

        $query = \Drupal::entityQuery('node')->condition('type', 'projekt');
        $nids = $query->execute();
        $graphdata = [];
        $nodes = $node_storage->loadMultiple($nids);

        $index = 0;
        foreach($nodes as $n) {
            //dsm($n->body->value);
            $wordsFound = [];
            // to search the whole node, not just the body field
            //$nodeView = \Drupal::entityTypeManager()->getViewBuilder('node')->view($n, 'full');
            //$nodeContent = \Drupal::service('renderer')->renderPlain($nodeView);
            //preg_match_all('/[\w-]*' . $term .'[\w-]*/iu', strip_tags(strtolower($nodeContent)), $wordsFound);
            preg_match_all('/[\w-]*' . $term .'[\w-]*/iu', strip_tags(strtolower($n->body->value)), $wordsFound);
            // Array-Format key = gefundenes Kompetenzwort, value = wie oft das Wort im Text vorkommt wurde
            if ($wordsFound[0]) {
                $projectTitle = $n->title->value;
                $projectTitleShort = Unicode::truncate($projectTitle, 50, true, true);
                $projectUrl = $n->toUrl()->toString();//Url::fromUri('internal:/node/' . $n->nid);
                $wordsFrequency = array_count_values($wordsFound[0]);

                $graphdata['nodes'][] = ['name' => $projectTitleShort, 'type' => 'project', 'link' => $projectUrl, 'title' => $projectTitle];
                $projectIndex = $index++;

                foreach ($wordsFrequency as $word => $wordcount) {
                  // if it's a new 'kompetenz' word, add it as a node
                  $wordIndex = array_search($word, array_column($graphdata['nodes'], 'name'));
                  if ($wordIndex === false) {
                    $graphdata['nodes'][] = ['name' => $word, 'type' => 'word'];
                    $wordIndex = $index++;
                  }

                  $graphdata['edges'][] = ['source' => $projectIndex, 'target' => $wordIndex];
                  $graphdata['praedikate'][] = $wordcount;
                }
            }
        }

        return new JsonResponse($graphdata);
    }
}
