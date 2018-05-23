<?php

/**
 * @file
 * Admin callbacks for Islandora OpenSeadragon.
 */

use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;

/**
 * Get the element to render for the AJAX event that triggered this callback.
 *
 * @param array $form
 *   The Drupal form definition.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   The Drupal form state.
 *
 * @return array
 *   The form element to render.
 */
function islandora_openseadragon_admin_ajax_djatoka_url(array $form, FormStateInterface $form_state) {
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
  try {
    $url = Url::fromUri(
      $path,
      [
        'absolute' => TRUE,
        'query' => [
          'url_ver' => 'Z39.88-2004',
          'rft_id' => 'http://memory.loc.gov/gmd/gmd433/g4330/g4330/np000066.jp2',
          'svc_id' => 'info:lanl-repo/svc/getRegion',
          'svc_val_fmt' => 'info:ofi/fmt:kev:mtx:jpeg2000',
          'svc.format' => 'image/jpeg',
          'svc.level' => '1',
        ],
      ]
    )->toString();

    $client = \Drupal::httpClient();
    $result = $client->request('GET', $url);
    if ($result->getStatusCode() == 200) {
      $image = [
        '#theme' => 'image',
        '#uri' => Url::fromUri('base:core/misc/icons/73b355/check.svg')->toString(),
      ];
      $message = t('Successfully connected to Adore Djatoka Server.');
    }
    else {
      $image = [
        '#theme' => 'image',
        '#uri' => Url::fromUri('base:core/misc/icons/e32700/error.svg')->toString(),
      ];
      $message = t('Unable to connect to Adore Djatoka Server at @path', [
        '@path' => $path,
      ]);
    }
  }
  catch (Exception $e) {
    $image = [
      '#theme' => 'image',
      '#uri' => Url::fromUri('base:core/misc/icons/e32700/error.svg')->toString(),
    ];
    $message = t('An exception occurred unable to connect to Adore Djatoka Server at @path', [
      '@path' => $path,
    ]);
  }
  return \Drupal::service('renderer')->render($image) . $message;
}

/**
 * Validates that the supplied djatoka base URL is a working djatoka install.
 *
 * @param array $element
 *   The element to validate.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   Drupal form state.
 * @param array $form
 *   Drupal form.
 */
function islandora_openseadragon_djatoka_url_validate(array $element, FormStateInterface $form_state, array $form) {
  $path = $element['#value'];
  if (empty($path)) {
    $form_state->setError($element, t('This field is required.'));
  }
  else {
    try {
      $url = Url::fromUri(
        $path,
        [
          'absolute' => TRUE,
          'query' => [
            'url_ver' => 'Z39.88-2004',
            'rft_id' => 'http://memory.loc.gov/gmd/gmd433/g4330/g4330/np000066.jp2',
            'svc_id' => 'info:lanl-repo/svc/getRegion',
            'svc_val_fmt' => 'info:ofi/fmt:kev:mtx:jpeg2000',
            'svc.format' => 'image/jpeg',
            'svc.level' => '1',
          ],
        ]
      )->toString();
      $client = \Drupal::httpClient();
      $result = $client->request('GET', $url);
      if ($result->getStatusCode() != 200) {
        $form_state->setError($element, t('This does not seem to be a functioning Djatoka server.'));
      }
    }
    catch (Exception $e) {
      $form_state->setError($element, t('This does not seem to be a functioning Djatoka server.'));
    }
  }
}

/**
 * Normalizes the settings so they can be used by Openseadragon.
 *
 * @param array $form
 *   The Drupal form.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   The Drupal form state.
 */
function islandora_openseadragon_admin_submit_normalize(array $form, FormStateInterface $form_state) {
  module_load_include('inc', 'islandora_openseadragon', 'includes/utilities');
  _islandora_openseadragon_normalize_settings($form_state->getValue('islandora_openseadragon_settings'));
  // Get default to match array formatting.
  $default_settings = islandora_openseadragon_default_options();
  _islandora_openseadragon_filter_settings($form_state->getValue('islandora_openseadragon_settings'), $default_settings);
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
    return Html::escape($value);
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
