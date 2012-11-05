(function($) {
  Drupal.behaviors.islandoraOpenSeadragon = {
    attach: function(context, settings) {
      console.log(context);
      console.log(settings);
      /*var resource_uri = Drupal.settings.islandoraOpenSeaDragon.resourceUri;
	var settings = Drupal.settings.islandora_openseadragon.settings;
	settings.tileSources = new Array();
	resource_uri = (resource_uri instanceof Array) ? resource_uri : new Array(resource_uri);
	resource_uri.each(function() {
        var djtilesource = new OpenSeadragon.DjatokaTileSource(this, settings);  // XXX: Requires proxy setup
        settings.tileSources.push(djtilesource);
	});
	new OpenSeadragon(settings);*/
    }
  };
})(jQuery);
