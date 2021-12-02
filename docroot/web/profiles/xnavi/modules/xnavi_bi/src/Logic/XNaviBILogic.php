<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 06.09.19
 * Time: 12:23
 */

namespace Drupal\xnavi_bi\Logic;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\node\Entity\Node;



class XNaviBILogic
{

    /**
     * This function gets all Vocabularies that are
     * @return array
     */
    public function getAllVocabularies() {
        $vocabularies = Vocabulary::loadMultiple();
        $vocabulariesList = [];
        foreach ($vocabularies as $vid => $vocabulary) {
            $vocabulariesList[$vid] = $vocabulary->get('name');
        }

        //dsm($vocabulariesList);
        return $vocabulariesList;
    }

    /**
     * This functions counts the frequency of a term that is
     * associated with a node
     * @param $termId
     * @return int|null
     */
    public function getCountOfNodesByTaxonomyTerms($termId) {
        //cast to an array
        $termIds = (array) $termId;
        if(empty($termIds)) {
            return NULL;
        }
        //get terms from the database
        $query = \Drupal::database()->select('taxonomy_index', 'ti');
        $query->fields('ti', array('nid'));
        $query->condition('ti.tid', $termIds, 'IN');
        $query->distinct(TRUE);
        $result = $query->execute();

        $nodeIds = $result->fetchCol();
        $nodes = Node::loadMultiple($nodeIds);

        return count($nodes);

    }

    /**
     * This function returns all terms of the given vocabulary
     * @param $vocabulary
     * @return array
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    public function getAllTaxonomyTermsOfAVocabulary($vocabulary) {
        $vid = $vocabulary;
        $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
        $term_data = array();
        foreach ($terms as $term) {
            $term_data[] = array(
                'id' => $term->tid,
                'name' => $term->name
            );
        }

        return $term_data;
    }
}