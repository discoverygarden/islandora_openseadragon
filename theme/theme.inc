<?php

/**
 * @file
 * This file contains all theme and preprocess functions.
 */

use Drupal\Component\Utility\Html;

/**
 * Implements template_preprocess_HOOK().
 *
 * @param array $variables
 *   The theme template variables.
 */
function template_preprocess_islandora_openseadragon_viewer(array &$variables) {
  module_load_include('inc', 'islandora_openseadragon', 'includes/utilities');
  $library_path = libraries_get_path('openseadragon');
  if (isset($variables['fedora_object']) && $variables['fedora_object'] !== NULL) {
    $message = t("The parameter 'fedora_object' in theme('islandora_openseadragon_viewer') has been deprecated. As of 7.x-1.10, please update your code before the next release.");
    trigger_error(Html::escape($message), E_USER_DEPRECATED);
    // Attempt to convert 'fedora_object', to 'pid'.
    $variables['pid'] = $variables['fedora_object']->id;
  }
  if (isset($variables['uri']) && $variables['uri'] !== '') {
    $message = t("The parameter 'uri' in theme('islandora_openseadragon_viewer') has been deprecated. As of 7.x-1.10, please update your code before the next release.");
    trigger_error(Html::escape($message), E_USER_DEPRECATED);
    $variables['tile_sources'] = [islandora_openseadragon_identifier_tile_source($variables['uri'])];
  }
  $variables['id'] = $variables['viewer_id'] = 'islandora-openseadragon';
  // @FIXME
// url() expects a route name or an external URI.
// $variables['settings'] = array(
//     'pid' => $variables['pid'],
//     'imageServer' => \Drupal::config('islandora_openseadragon.settings')->get('islandora_openseadragon_tilesource'),
//     'djatokaServerBaseURL' => url(\Drupal::config('islandora_openseadragon.settings')->get('islandora_openseadragon_djatoka_url'),
//       array('absolute' => TRUE)
//     ),
//     'iiifServerBaseURL' => url(\Drupal::config('islandora_openseadragon.settings')->get('islandora_openseadragon_iiif_url')),
//     'fitToAspectRatio' => \Drupal::config('islandora_openseadragon.settings')->get('islandora_openseadragon_fit_to_aspect_ratio'),
//     'options' => array(
//       'id' => $variables['id'],
//       'prefixUrl' => file_create_url("{$library_path}/images/"),
//       'tileSources' => $variables['tile_sources'],
//       'overlays' => islandora_openseadragon_viewer_query_solr_for_overlays($variables['pid']),
//     ) + islandora_openseadragon_get_settings(),
//   );

  if (isset($variables['token_header']) && isset($variables['token']) && $variables['token_header']) {
    $variables['settings']['options']['loadTilesWithAjax'] = TRUE;
    $variables['settings']['options']['ajaxHeaders'] = [
      'X-ISLANDORA-TOKEN' => $variables['token'],
    ];
  }
}

/**
 * Implements template_process_HOOK().
 *
 * @param array $variables
 *   The theme template variables.
 */
function template_process_islandora_openseadragon_viewer(array &$variables) {
  $library_path = libraries_get_path('openseadragon');
  $module_path = drupal_get_path('module', 'islandora_openseadragon');

  // @FIXME
// The Assets API has totally changed. CSS, JavaScript, and libraries are now
// attached directly to render arrays using the #attached property.
//
//
// @see https://www.drupal.org/node/2169605
// @see https://www.drupal.org/node/2408597
// drupal_add_js(array(
//     'islandoraOpenSeadragon' => $variables['settings'],
//   ), 'setting');


  // @FIXME
// The Assets API has totally changed. CSS, JavaScript, and libraries are now
// attached directly to render arrays using the #attached property.
//
//
// @see https://www.drupal.org/node/2169605
// @see https://www.drupal.org/node/2408597
// drupal_add_js("$library_path/openseadragon.js", array('weight' => -4));

  if (islandora_openseadragon_use_djatoka_server()) {
    // @FIXME
// The Assets API has totally changed. CSS, JavaScript, and libraries are now
// attached directly to render arrays using the #attached property.
//
//
// @see https://www.drupal.org/node/2169605
// @see https://www.drupal.org/node/2408597
// drupal_add_js("$module_path/js/djtilesource.js", array('weight' => -3));

  }
  // @FIXME
// The Assets API has totally changed. CSS, JavaScript, and libraries are now
// attached directly to render arrays using the #attached property.
//
//
// @see https://www.drupal.org/node/2169605
// @see https://www.drupal.org/node/2408597
// drupal_add_js("$module_path/js/islandora_openseadragon.js", array('weight' => -2));

  // @FIXME
// The Assets API has totally changed. CSS, JavaScript, and libraries are now
// attached directly to render arrays using the #attached property.
//
//
// @see https://www.drupal.org/node/2169605
// @see https://www.drupal.org/node/2408597
// drupal_add_css("$module_path/css/islandora_openseadragon.theme.css");

}

/**
 * Theme function to create a clipper link.
 *
 * @param array $variables
 *   The theme function variables.
 *
 * @return string
 *   The clipped image rendered into html.
 */
function theme_islandora_openseadragon_clipper(array &$variables) {
  // @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
//
//
// @see https://www.drupal.org/node/2195739
// $image = theme(
//     'image',
//     array(
//       'path' => drupal_get_path('module', 'islandora_openseadragon') . '/images/clip_icon.png',
//     )
//   );

  // @FIXME
// l() expects a Url object, created from a route name or external URI.
// return l(
//     $image,
//     "islandora/object/{$variables['pid']}/print",
//     array(
//       'attributes' => array(
//         'title' => t('Clip Image'),
//         'id' => 'clip',
//       ),
//       'html' => TRUE,
//     )
//   );

}
