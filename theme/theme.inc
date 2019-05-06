<?php

/**
 * @file
 * This file contains all theme and preprocess functions.
 */

use Drupal\Core\Url;
use Drupal\Core\Link;

use Drupal\islandora_openseadragon\Form\Admin as AdminForm;

/**
 * Implements template_preprocess_HOOK().
 *
 * @param array $variables
 *   The theme template variables.
 */
function template_preprocess_islandora_openseadragon_viewer(array &$variables) {
  module_load_include('inc', 'islandora_openseadragon', 'includes/utilities');
  $variables['id'] = $variables['viewer_id'] = 'islandora-openseadragon';

  if (isset($variables['token_header']) && isset($variables['token']) && $variables['token_header']) {
    $variables['settings']['options']['loadTilesWithAjax'] = TRUE;
    $variables['settings']['options']['ajaxHeaders'] = [
      'X-ISLANDORA-TOKEN' => $variables['token'],
    ];
  }
  $variables['use_djatoka'] = islandora_openseadragon_use_djatoka_server();
  $variables['settings'] = islandora_openseadragon_get_viewer_settings($variables);
}

/**
 * Get settings for the viewer.
 */
function islandora_openseadragon_get_viewer_settings(array $variables) {
  module_load_include('inc', 'islandora_openseadragon', 'includes/utilities');
  $library_path = libraries_get_path('openseadragon');
  $settings = [
    'pid' => $variables['pid'],
    'imageServer' => \Drupal::config('islandora_openseadragon.settings')->get('islandora_openseadragon_tilesource'),
    'fitToAspectRatio' => \Drupal::config('islandora_openseadragon.settings')->get('islandora_openseadragon_fit_to_aspect_ratio'),
    'options' => [
      'id' => 'islandora-openseadragon',
      'prefixUrl' => file_create_url("{$library_path}/images/"),
      'tileSources' => $variables['tile_sources'],
      'overlays' => islandora_openseadragon_viewer_query_solr_for_overlays($variables['pid']),
    ] + islandora_openseadragon_get_settings(),
  ];
  if (islandora_openseadragon_use_djatoka_server()) {
    $settings['djatokaServerBaseURL'] = Url::fromUri(AdminForm::stateGet('islandora_openseadragon_djatoka_url'),
      ['absolute' => TRUE]
    )->toString();
  }
  else {
    $settings['iiifServerBaseURL'] = Url::fromUri(AdminForm::stateGet('islandora_openseadragon_iiif_url'))->toString();
  }
  return $settings;
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
function islandora_openseadragon_clipper(array &$variables) {
  $image = [
    '#theme' => 'image',
    '#uri' => drupal_get_path('module', 'islandora_openseadragon') . '/images/clip_icon.png',
  ];
  $rendered_image = \Drupal::service('renderer')->render($image);
  $url = Url::fromRoute(
    'islandora.print_object',
    ['object' => $variables['pid']],
    [
      'attributes' => [
        'title' => t('Clip Image'),
        'id' => 'clip',
      ],
    ]
  );
  return Link::fromTextAndUrl($rendered_image, $url)->toString();
}
