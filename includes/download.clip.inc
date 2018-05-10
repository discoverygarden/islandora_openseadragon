<?php

/**
 * @file
 * Defines menu callback for downloading a clip of the given image.
 */

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Menu callback downloads the given clip.
 *
 * @param AbstractObject $object
 *   An AbstractObject representing an object within Fedora.
 */
function islandora_openseadragon_download_clip(AbstractObject $object) {
  if (isset($_GET['clip'])) {
    module_load_include('inc', 'islandora_openseadragon', 'includes/utilities');
    $clip_parts = islandora_openseadragon_construct_clip_url($_GET['clip'], TRUE);
    if ($clip_parts) {
      $filename = $object->label;
      header("Content-Disposition: attachment; filename=\"{$filename}.jpg\"");
      header("Content-type: image/jpeg");
      header("Content-Transfer-Encoding: binary");
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $clip_parts['image_url']);
      $return = curl_exec($ch);
      curl_close($ch);
    }
    else {
      \Drupal::logger('islandora_openseadragon')->notice('Invalid parameters specified for downloading of a clip for @pid. Parameters attempted: @params.', [
        '@pid' => $object->id,
        '@params' => $_GET['clip'],
      ]);
      throw new NotFoundHttpException();
    }
  }
  return $return;
}
