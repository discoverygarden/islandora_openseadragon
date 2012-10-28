<?php

/**
 * @file
 * Admin callbacks for Islandora OpenSeadragon
 */
 
 /**
  * Admin form
  */
function islandora_openseadragon_admin() {
  // include css
  drupal_add_css(drupal_get_path('module', 'islandora_openseadragon') . '/css/islandora_openseadragon.admin.css');

  $form = array();
  $form['islandora_openseadragon_test'] = array(
    '#type' => 'checkbox',
    '#title' => t('Test option'),
    '#default_value' => variable_get('islandora_openseadragon_test', TRUE),
    '#description' => t('Test element.'),
    '#weight' => -10
  );

  // @TODO: include all/most of the OpenSeadragon settings here.

  return system_settings_form($form);
}