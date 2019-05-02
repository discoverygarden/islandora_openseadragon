<?php

namespace Drupal\islandora_openseadragon\Commands;

use Drupal\islandora\Commands\AbstractPluginAcquisition;

/**
 * Drush commandfile for Islandora Openseadragon.
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
  public function installPlugin($path = NULL) {
    return $this->plugin($path);
  }

}
