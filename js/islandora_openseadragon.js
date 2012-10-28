(function($) {
  Drupal.behaviors.islandora_openseadragon = {
    attach: function(context) {
      // get variables
      var base_path = Drupal.settings.basePath;
      // islandora_openseadragon variables
      var viewer_id = Drupal.settings.islandora_openseadragon.viewer_id;
      var djatoka_url = Drupal.settings.islandora_openseadragon.djatoka_url;
      var resource_uri = Drupal.settings.islandora_openseadragon.resource_uri;
      var options = Drupal.settings.islandora_openseadragon.options;

      // init viewer
      var viewer = new OpenSeadragon({
        id: viewer_id,
        prefixUrl: base_path + "sites/all/libraries/openseadragon",
        showNavigationControl: true/*,
        tileSources: "/openseadragon/examples/images/highsmith/highsmith.js",
        wrapVertical: true,
        wrapHorizontal: true,
        visibilityRatio: 0,
        minPixelRatio: 1,
        immediateRender: true,
        minZoomImageRatio: 1,
        maxZoomPixelRatio: 1,

        */
      }); 
      // get djatoka tilesource
      var djtilesource = new OpenSeadragon.DjTileSource(djatoka_url, resource_uri);
      // apply djatoka tilesource
      viewer.openTileSource(djtilesource);
    }
  };
})(jQuery);





