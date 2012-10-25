(function($) {
  Drupal.behaviors.djatoka_seadragon = {
    attach: function(context) {

      var viewer_id = Drupal.settings.djatoka_seadragon.viewer_id;
      var djatoka_url = Drupal.settings.djatoka_seadragon.djatoka_url;
      var resource_uri = Drupal.settings.djatoka_seadragon.resource_uri;

      var viewer = new OpenSeadragon.Viewer(viewer_id);
      var djtilesource = new OpenSeadragon.DjTileSource(djatoka_url, resource_uri);
      viewer.openTileSource(djtilesource);
    }
  };
})(jQuery);





