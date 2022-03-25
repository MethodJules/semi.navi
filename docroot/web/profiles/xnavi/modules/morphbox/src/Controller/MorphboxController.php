<?php

namespace Drupal\morphbox\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class MorphboxController extends ControllerBase {
    public function getData(Request $request) {
        \Drupal::logger('moprhbox')->notice($request->getContent());
        return [];
    }
}