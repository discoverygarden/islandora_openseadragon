<?php

/**
 * @file
 * Admin callbacks for Islandora OpenSeadragon.
 */

/**
 * Get the element to render for the AJAX event that triggered this callback.
 *
 * @param array $form
 *   The Drupal form definition.
 * @param array $form_state
 *   The Drupal form state.
 *
 * @return array
 *   The form element to render.
 */
function islandora_openseadragon_admin_ajax_djatoka_url(array $form, array $form_state) {
  return $form['djatoka']['islandora_openseadragon_djatoka_url'];
}

/**
 * Gets a message which describes if Adore-Djatoka is accessible.
 *
 * @param string $path
 *   The path to the Adore Djatoka image resolver.
 *
 * @return string
 *   A message describing the accessibility of the Adore-Djatoka image resolver.
 */
function islandora_openseadragon_admin_form_djatoka_access_message($path) {
  // @FIXME
// url() expects a route name or an external URI.
// $url = url("{$path}", array(
//     'absolute' => TRUE,
//     'query' => array(
//       'url_ver' => 'Z39.88-2004',
//       'rft_id' => 'http://memory.loc.gov/gmd/gmd433/g4330/g4330/np000066.jp2',
//       'svc_id' => 'info:lanl-repo/svc/getRegion',
//       'svc_val_fmt' => 'info:ofi/fmt:kev:mtx:jpeg2000',
//       'svc.format' => 'image/jpeg',
//       'svc.level' => '1',
//     ),
//   ));

  // @FIXME
// drupal_http_request() has been replaced by the Guzzle HTTP client, which is bundled
// with Drupal core.
//
//
// @see https://www.drupal.org/node/1862446
// @see http://docs.guzzlephp.org/en/latest
// $result = drupal_http_request($url);

  if ($result->code == 200) {
    $confirmation_message = theme_image(array('path' => 'misc/watchdog-ok.png', 'attributes' => array()));
    $confirmation_message .= t('Successfully connected to Adore Djatoka Server.');
  }
  else {
    $confirmation_message = theme_image(array('path' => 'misc/watchdog-error.png', 'attributes' => array()));
    $confirmation_message .= t('Unable to connect to Adore Djatoka Server at !path', array(
      '!path' => $path,
    ));
  }
  return $confirmation_message;
}

/**
 * Validates that the supplied djatoka base URL is a working djatoka install.
 *
 * @param array $element
 *   The element to validate.
 * @param array $form_state
 *   Drupal form state.
 * @param array $form
 *   Drupal form.
 */
function islandora_openseadragon_djatoka_url_validate(array $element, array &$form_state, array $form) {
  $path = $element['#value'];
  if (empty($path)) {
    form_error($element, t('This field is required.'));
  }
  else {
    // @FIXME
// url() expects a route name or an external URI.
// $url = url("{$path}", array(
//       'absolute' => TRUE,
//       'query' => array(
//         'url_ver' => 'Z39.88-2004',
//         'rft_id' => 'http://memory.loc.gov/gmd/gmd433/g4330/g4330/np000066.jp2',
//         'svc_id' => 'info:lanl-repo/svc/getRegion',
//         'svc_val_fmt' => 'info:ofi/fmt:kev:mtx:jpeg2000',
//         'svc.format' => 'image/jpeg',
//         'svc.level' => '1',
//       ),
//     ));

    // @FIXME
// drupal_http_request() has been replaced by the Guzzle HTTP client, which is bundled
// with Drupal core.
//
//
// @see https://www.drupal.org/node/1862446
// @see http://docs.guzzlephp.org/en/latest
// $result = drupal_http_request($url);

    if ($result->code != 200) {
      form_error($element, t('This does not seem to be a functioning Djatoka server.'));
    }
  }
}

/**
 * Validate the form.
 *
 * @param array $form
 *   Drupal form.
 * @param array $form_state
 *   Drupal form state.
 */
function islandora_openseadragon_admin_validate(array $form, array &$form_state) {
  if ($form_state['values']['islandora_openseadragon_tilesource'] == 'djatoka') {
    $element = $form['djatoka']['islandora_openseadragon_djatoka_url'];
    islandora_openseadragon_djatoka_url_validate($element, $form_state, $form);
  }
}

/**
 * Handles a submit from the admin pane.
 *
 * @param array $form
 *   Drupal form.
 * @param array $form_state
 *   Drupal form state.
 */
function islandora_openseadragon_admin_submit_reset(array $form, array &$form_state) {
  $op = $form_state['clicked_button']['#id'];
  switch ($op) {
    case 'edit-reset':
      \Drupal::config('islandora_openseadragon.settings')->clear('islandora_openseadragon_settings')->save();
      \Drupal::config('islandora_openseadragon.settings')->clear('islandora_openseadragon_tile_size')->save();
      \Drupal::config('islandora_openseadragon.settings')->clear('islandora_openseadragon_tile_overlap')->save();
      break;
  }
}

/**
 * Normalizes the settings so they can be used by Openseadragon.
 *
 * @param array $form
 *   The Drupal form.
 * @param array $form_state
 *   The Drupal form state.
 */
function islandora_openseadragon_admin_submit_normalize(array $form, array &$form_state) {
  module_load_include('inc', 'islandora_openseadragon', 'includes/utilities');
  _islandora_openseadragon_normalize_settings($form_state['values']['islandora_openseadragon_settings']);
  // Get default to match array formatting.
  $default_settings = islandora_openseadragon_default_options();
  _islandora_openseadragon_filter_settings($form_state['values']['islandora_openseadragon_settings'], $default_settings);
}

/**
 * Casts the settings to appropriate types so they work in javascript.
 *
 * @param array $settings
 *   The Openseadragon settings to be normalized.
 *
 * @return array
 *   Normalized settings.
 */
function _islandora_openseadragon_normalize_settings(array &$settings) {
  foreach ($settings as $key => $value) {
    $settings[$key] = _islandora_openseadragon_normalize_setting($value);
  }
  return $settings;
}

/**
 * Normalizes the given setting.
 *
 * @param mixed $value
 *   The setting to be normalized.
 *
 * @return array|float|int|string
 *   The normalized setting.
 */
function _islandora_openseadragon_normalize_setting($value) {
  if (is_array($value)) {
    return _islandora_openseadragon_normalize_settings($value);
  }
  elseif (filter_var($value, FILTER_VALIDATE_INT) !== FALSE) {
    return (int) $value;
  }
  elseif (filter_var($value, FILTER_VALIDATE_FLOAT) !== FALSE) {
    return (float) $value;
  }
  elseif (filter_var($value, FILTER_VALIDATE_URL) !== FALSE) {
    return \Drupal\Component\Utility\Html::escape($value);
  }
  return $value;
}

/**
 * Casts the settings to appropriate types so they work in javascript.
 *
 * @param array $settings
 *   The Openseadragon settings to be normalized.
 *
 * @return array
 *   Normalized settings.
 */
function _islandora_openseadragon_filter_settings(array &$settings, array $default_settings) {
  foreach ($settings as $key => $value) {
    // We don't want the nested containers in the settings
    // so we strip them out.
    if (is_array($value) && !isset($default_settings[$key])) {
      $tmp = _islandora_openseadragon_filter_settings($value, $default_settings);
      $settings = array_merge($settings, $tmp);
      unset($settings[$key]);
    }
    elseif (is_array($value)) {
      $settings[$key] = _islandora_openseadragon_filter_settings($value, $default_settings[$key]);
    }
    if (is_string($value) && empty($value)) {
      unset($settings[$key]);
    }
    elseif (is_null($value)) {
      unset($settings[$key]);
    }
  }
  return $settings;
}
