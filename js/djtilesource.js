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
   * @param {string} djatoka
   *   The URL of the Djatoka resolver.
   * @param {string} imageID
   *   The UR{I,L} of the image.
   */ 
  $.DjTileSource = function(djatoka, imageID) {
    var djts = this;
    var tileOverlap = 0;
    var tileSize = 256;

    this.baseURL = djatoka;
    this.imageID = imageID;
    jQuery.ajaxSetup({async: false}); 
    jQuery.get(djatoka, {
        'url_ver': 'Z39.88-2004',
        'rft_id': imageID,
        'svc_id': 'info:lanl-repo/svc/getMetadata'
      },
      function(data, textStatus, jqXHR) {
        $.TileSource.call(
          djts, //XXX: "this" loses it's context?
          parseInt(data.width), //get the width from
          parseInt(data.height), //get the height
          tileSize,
          tileOverlap,
          0,
          parseInt(data.levels)
        );
        //XXX:  Kinda gross, but Seadragon.TileSource put the methods directly
        //  on the object, not through "prototype"...
        djts.getTileUrl = $.DjTileSource.prototype.getTileUrl;
        //djts.getLevelScale = function (level) {return 1;}; 
      },
      'json'
    );
    jQuery.ajaxSetup({async:true});
  };

  //Inherit from TileSource.
  jQuery.extend($.DjTileSource.prototype, $.TileSource.prototype);

  /**
   * Implement the abstract function.
   *
   * @function
   * @name Seadragon.DjTileSource.prototype.getTileUrl
   * @param {Number} level
   * @param {Number} x
   * @param {Number} y
   */
  $.DjTileSource.prototype.getTileUrl = function(level, x, y) {
    var bounds = this.getTileBounds(level, x, y);

    //XXX:  According to docs at http://sourceforge.net/apps/mediawiki/djatoka/index.php?title=Djatoka_OpenURL_Services#info:lanl-repo.2Fsvc.2FgetRegion,
    //  something like the following should work; however, seems that djatoka
    //  dies with a number format error while trying to parse as integers.
    //  (The bounds returned are "normalized" such that the width is between 
    //  0 and 1, and the height is between 0 and the aspect ratio)
    //var region = bounds.y + ',' + bounds.x +  ',' + bounds.height + ',' + bounds.width;

    var scale = this.getLevelScale(level);
    
    //XXX:  Instead, we have to multiply and cast.
    var region = (bounds.y * this.dimensions.y * this.aspectRatio * scale).toFixed() + ',' + 
      (bounds.x * this.dimensions.x * scale).toFixed() +  ',' + 
      //Math.min(this.dimensions.y, 
        (bounds.height * this.dimensions.y * this.aspectRatio * scale).toFixed()
      //)
      + ',' + 
      //Math.min(this.dimensions.x, 
        (bounds.width * this.dimensions.x * scale).toFixed()
      //)
      ;

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
}(Seadragon));
