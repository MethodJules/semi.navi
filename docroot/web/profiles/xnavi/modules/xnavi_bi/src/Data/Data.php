<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 03.06.19
 * Time: 13:59
 */

namespace Drupal\xnavi_bi\Data;

use Drupal\xnavi_bi\Logic\XNaviBILogic;

class Data
{

    public function getData($vocabulary) {

        $logic = new XNaviBILogic();
        $data = array();
        $terms = $logic->getAllTaxonomyTermsOfAVocabulary($vocabulary);
        if(!is_null($terms)) {
            foreach ($terms as $term) {
                //dsm($term);
                $term_data[] =
                    [
                        'term' => $term['name'],
                        //'count' => $this->getCountOfNodesByTaxonomyTerms($term['id']),
                        'count' => $logic->getCountOfNodesByTaxonomyTerms($term['id']),
                    ];

            }
            $voc_data[$vocabulary] = $term_data;
            $data[] = $voc_data;
        }

        $c3_data = array();
        foreach ($data as $dimension) {
            foreach ($dimension as $values) {
                foreach ($values as $value) {
                    $c3_data[$value['term']] = array($value['count']);
                };

            }
        }

        return $c3_data;
    }
}