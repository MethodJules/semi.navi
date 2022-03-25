<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 31.05.19
 * Time: 18:07
 */

namespace Drupal\xnavi_bi\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\xnavi_bi\Data\Data;
use Drupal\xnavi_bi\Logic\XNaviBILogic;
use Symfony\Component\HttpFoundation\JsonResponse;

class XNaviBIController extends ControllerBase
{
    public function content($chartType) {
        global $base_url;
        //$vocabularies = $this->getAllVocabularies();
        $logic = new XNaviBILogic();
        $vocabularies = $logic->getAllVocabularies();
        //filter vocabularies
        //$filter_elements = ['tags', 'forums', 'foren', 'forschungsergebnistyp'];
        $config = \Drupal::config('xnavi_bi.adminsettings');
        $filter_elements = explode(" ", $config->get('vocabularies'));

        foreach ($filter_elements as $filter_element) {
            unset($vocabularies[$filter_element]);
        }




        //get keys of vocabulary array before reindexing
        $voc_keys = array_keys($vocabularies);
        //reindex $vaocabularies
        $vocabularies = array_values($vocabularies);
        //dsm($vocabularies);

        //create HTML Container for the charts
        $html = '<div>';
        $html .= '<p>Hier können Sie sich die quantitativen Analysen über den Portalbestand anschauen. Die Grafiken sind interaktiv. Fahren Sie bpsw. über ein Tortenstück oder deaktivieren Sie Werte in dem Sie auf den jeweiligen Wert unterhalb der Tortendiagramm klicken.</p>';
        $html .= '<div><a class="btn btn-outline-info" href="' . $base_url . '/xnavi_bi/content/pie">Tortendiagramm</a>';
        $html .= '<a class="btn btn-outline-info" href="' . $base_url . '/xnavi_bi/content/bar">Stabdiagramm</a></div>';
        for($i=0;$i<count($vocabularies);$i++) {
            //$html .= '<h1>' . $vocabularies[$i] .'</h1><div id="chart' . $i . '"></div>';
            $html .= '<div id="accordion" role="tablist">';
                $html .= '<div class="card">';
                    $html .= '<div class="card-header" role="tab" id="heading' . $i . '">';
                        $html .= '<h5 class="mb-0">';
                            $html .= '<a data-toggle="collapse" href="#collapse' . $i . '" aria-expanded="true" aria-controls="collapse' . $i .'">';
                                $html .= $vocabularies[$i];
                            $html .= '</a>';
                        $html .= '</h5>';
                    $html .= '</div>';
                if($i == 0)  {
                    $html .= '<div id="collapse' . $i . '" class="collapse show" role="tabpanel" aria-labelledby="heading' . $i . '">';
                } else {
                    $html .= '<div id="collapse' . $i . '" class="collapse" role="tabpanel" aria-labelledby="heading' . $i . '">';
                }
                    $html .= '<div class="card-body">';
                        $html .= '<div class="w-100" id="chart' . $i . '"></div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
        }



        $html .= '</div>';


        //Create the Drupal specific render array
        $render_html = ['#markup' => $html];
        $render_html['#attached']['library'][] = 'xnavi_bi/xnavi-bi';
        $render_html['#attached']['drupalSettings']['baseUrl'] = $base_url;
        $render_html['#attached']['drupalSettings']['chartType'] = $chartType;
        $render_html['#attached']['drupalSettings']['vocabularies'] = $vocabularies;
        $render_html['#attached']['drupalSettings']['voc_keys'] = $voc_keys;

        return $render_html;
    }

    /**
     * This function provides the data (count of associated nodes)
     * in JSON Format
     * @param $vocabulary
     * @return JsonResponse
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    public function getData($vocabulary) {

        $data = new Data();
        $c3_data = $data->getData($vocabulary);
        return new JsonResponse($c3_data);


    }








}
