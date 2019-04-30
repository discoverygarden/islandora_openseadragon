<?php

namespace Drupal\islandora_openseadragon\Commands;

use Drupal\islandora\Commands\AbstractPluginAcquisition;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class IslandoraOpenseadragonCommands extends AbstractPluginAcquisition {

  /**
   * {@inheritdoc}
   */
  protected function getDownloadUri() {
    return 'https://github.com/openseadragon/openseadragon/releases/download/v2.3.1/openseadragon-bin-2.3.1.zip';
  }

  /**
   * {@inheritdoc}
   */
  protected function getInstallDir($path) {
    return implode('/', [
      $path,
      'openseadragon',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getOriginalDir($path) {
    return implode('/', [
      $path,
      'openseadragon-bin-2.3.1',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getUnpackDir($path) {
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDescriptor() {
    return 'Openseadragon plugin';
  }

  /**
   * Download and install the plugin.
   *
   * @param string $path
   *   Optional. A path where to install the plugin. If omitted Drush
   *   will use the default location.
   *
   * @command islandora_openseadragon:plugin
   * @aliases openseadragonplugin,openseadragon-plugin
   */
  public function plugin($path = NULL) {
    return parent::plugin($path);
  }

}
