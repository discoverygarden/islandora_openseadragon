(function($) {
  Drupal.behaviors.islandora_seadragon = {
    attach: function(context) {

      var viewer_id = Drupal.settings.islandora_openseadragon.viewer_id;
      var djatoka_url = Drupal.settings.islandora_openseadragon.djatoka_url;
      var resource_uri = Drupal.settings.islandora_openseadragon.resource_uri;

/*       var viewer = new OpenSeadragon.Viewer(viewer_id); */

      var viewer = new OpenSeadragon({
        id: viewer_id,
        prefixUrl: "sites/all/libraries/openseadragon",
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

      var djtilesource = new OpenSeadragon.DjTileSource(djatoka_url, resource_uri);
      viewer.openTileSource(djtilesource);

    }
  };
})(jQuery);





