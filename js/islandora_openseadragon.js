(function($) {
  Drupal.behaviors.islandora_openseadragon = {
    attach: function(context) {
      // get variables
      var base_path = Drupal.settings.basePath;
      // islandora_openseadragon variables
      var viewer_id = Drupal.settings.islandora_openseadragon.viewer_id;
      var djatoka_url = Drupal.settings.islandora_openseadragon.djatoka_url;
      var resource_uri = Drupal.settings.islandora_openseadragon.resource_uri;
      var settings = Drupal.settings.islandora_openseadragon.settings;

      /*
      var resource_uri2 = 'http://localhost:8080/fedora/objects/islandora:313/datastreams/JP2/content';
      var djtilesource2 = new OpenSeadragon.DjTileSource(djatoka_url, resource_uri2);
      */

      var tile_sources = new Array();
      if (resource_uri instanceof Array) {
        // array
        resource_uri.each(function() {
          var djtilesource = new OpenSeadragon.DjTileSource(djatoka_url, this);
          tile_sources.push(djtilesource);
        });
      }
      else {
        // not array
        var djtilesource = new OpenSeadragon.DjTileSource(djatoka_url, resource_uri);
        tile_sources.push(djtilesource);
      }

      // set config
      var configuration = {
        id: viewer_id,
        prefixUrl: base_path + "sites/all/libraries/openseadragon",
        tileSources: tile_sources
      };
      // merge settings
      $.extend(configuration, settings);

      // init viewer
      var viewer = new OpenSeadragon(configuration); 
    }
  };
})(jQuery);




