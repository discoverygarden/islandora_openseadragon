<?php

namespace Drupal\islandora_openseadragon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

use AbstractObject;

/**
 * Default controller for the islandora_openseadragon module.
 */
class DefaultController extends ControllerBase {

  /**
   * Downloads the given clip.
   */
  public function downloadClip(AbstractObject $object) {
    module_load_include('inc', 'islandora_openseadragon', 'includes/download.clip');
    $data = islandora_openseadragon_download_clip($object);
    $response = new Response();
    $response->setContent($data);
    return $response;
  }

}
