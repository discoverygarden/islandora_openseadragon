(function($) {
  /**
   * An OpenSeadragon interface for the Djatoka tile server.  It is based
   * on Doug Reside's DjatokaSeadragon, but modified to work with the newer fork
   * of OpenSeadragon that's being developed by Chris Thatcher at LoC.
   *
   * https://github.com/dougreside/DjatokaSeadragon
   * https://github.com/thatcher/openseadragon
   *
   * @class
   * @extends Seadragon.TileSource
   * @param {string} baseURL
   *   The base URL of the djatoka server.
   * @param {string} imageID
   *   The image identifier.
   */
  $.DjatokaTileSource = function(baseURL, imageID, settings) {
    var that = this;
    var djatoka_get_params = {
      'url_ver': 'Z39.88-2004',
      'rft_id': imageID,
      'svc_id': 'info:lanl-repo/svc/getMetadata'
    };
    this.baseURL = baseURL;
    this.imageID = imageID;
    var djatoka_get_success = function(data, textStatus, jqXHR) {
      // Determine if the current platform is Windows.
      if (navigator.platform.toLowerCase().indexOf('win') !== -1) {
        // slanger, 2013-10-28: The data object includes a path to the
        // tomcat temp directory, where the JP2 file has been cached.
        // In Windows, this path contains backslashes, which will be
        // misinterpreted as escaped characters, causing the viewer to
        // break. The backslashes need to be replaced with forwardslashes.
	    data = data.replace(/\\/g, '/');
	    data = jQuery.parseJSON(data);
      }
      $.TileSource.call(
	that,
	parseInt(data.width),
	parseInt(data.height),
	parseInt(settings.tileSize),
	parseInt(settings.tileOverlap),
	1,
	parseInt(data.levels)
      );
      //XXX:  Kinda gross, but Seadragon.TileSource put the methods directly
      // on the object, not through "prototype"...
      that.getTileUrl = $.DjatokaTileSource.prototype.getTileUrl;
    };
    jQuery.ajaxSetup({async: false});
    // slanger, 2013-10-28: If the platform is Windows, the data needs to remain a
    // string so that backslashes in paths can be replaced with forwardslashes --
    // otherwise, the viewer will break. It can then become a JSON object.
    dataType = (navigator.platform.toLowerCase().indexOf('win') !== -1) ? 'string' : 'json';
    jQuery.get(this.baseURL, djatoka_get_params, djatoka_get_success, dataType);
    jQuery.ajaxSetup({async:true});
  };
  jQuery.extend($.DjatokaTileSource.prototype, $.TileSource.prototype); // Inherit from TileSource.
  /**
   * Implement the abstract function.
   *
   * @function
   * @name Seadragon.DjatokaTileSource.prototype.getTileUrl
   *
   * @param {Number} level
   * @param {Number} x
   * @param {Number} y
   */
  $.DjatokaTileSource.prototype.getTileUrl = function(level, x, y) {
    /**
     *  XXX:  According to docs at http://sourceforge.net/apps/mediawiki/djatoka/index.php?title=Djatoka_OpenURL_Services#info:lanl-repo.2Fsvc.2FgetRegion,
     *  something like the following should work; however, seems that djatoka
     *  dies with a number format error while trying to parse as integers.
     *  (The bounds returned are "normalized" such that the width is between
     *  0 and 1, and the height is between 0 and the aspect ratio)
     *  var region = bounds.y + ',' + bounds.x +  ',' + bounds.height + ',' + bounds.width;
     */
    var bounds = this.getTileBounds(level, x, y);
    var scale = this.getLevelScale(level);
    // XXX:  Instead, we have to multiply and cast.
    var region = (bounds.y * this.dimensions.y * this.aspectRatio).toFixed() + ',' +
      (bounds.x * this.dimensions.x).toFixed() +  ',' +
      this.tileSize + ',' +
      this.tileSize;
    // Djatoka parameters
    var params = {
      'url_ver': 'Z39.88-2004',
      'rft_id': this.imageID,
      'svc_id': 'info:lanl-repo/svc/getRegion',
      'svc_val_fmt': 'info:ofi/fmt:kev:mtx:jpeg2000',
      'svc.region': region,
      'svc.level': level
    };
    return this.baseURL + '?' + jQuery.param(params);
  };
}(OpenSeadragon));
