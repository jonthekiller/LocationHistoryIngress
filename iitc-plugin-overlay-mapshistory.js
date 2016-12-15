// ==UserScript==
// @id             iitc-plugin-overlay-mapshistory@jonthekiller
// @name           IITC plugin: Maps History tiles
// @category       Map Tiles
// @version        0.2
// @namespace      https://github.com/jonatkins/ingress-intel-total-conversion
// @description    Add Maps History Overlay based on OSM layer plugin
// @include        https://www.ingress.com/intel*
// @include        http://www.ingress.com/intel*
// @match          https://www.ingress.com/intel*
// @match          http://www.ingress.com/intel*
// @include        https://www.ingress.com/mission/*
// @include        http://www.ingress.com/mission/*
// @match          https://www.ingress.com/mission/*
// @match          http://www.ingress.com/mission/*
// @grant          none
// ==/UserScript==


function wrapper(plugin_info) {
// ensure plugin framework is there, even if iitc is not yet loaded
if(typeof window.plugin !== 'function') window.plugin = function() {};

//PLUGIN AUTHORS: writing a plugin outside of the IITC build environment? if so, delete these lines!!
//(leaving them in place might break the 'About IITC' page or break update checks)
plugin_info.buildName = 'jonthekiller';
plugin_info.dateTimeVersion = '20161010.140900';
plugin_info.pluginId = 'overlay.mapshistory';
//END PLUGIN AUTHORS NOTE



// PLUGIN START ////////////////////////////////////////////////////////


// use own namespace for plugin
window.plugin.mapTileOpenStreetMap = function() {};

window.plugin.mapTileOpenStreetMap.addLayer = function() {

  osmAttribution = 'Maps History Overlay';
  var osmOpt = {attribution: osmAttribution, maxNativeZoom: 18, maxZoom: 21};
  var osm = L.tileLayer.wms('<Geoserver WMS URL>', {
    layers: '<Geoserver Layer Name>',
    format: 'image/png',
    transparent: true,
    version: '1.1.0',
     crs: L.CRS.EPSG4326,
      tiled:true
}).addTo(map);

    layerChooser.addOverlay(osm, "Maps History");
};

var setup =  window.plugin.mapTileOpenStreetMap.addLayer;

// PLUGIN END //////////////////////////////////////////////////////////


setup.info = plugin_info; //add the script info data to the function as a property
if(!window.bootPlugins) window.bootPlugins = [];
window.bootPlugins.push(setup);
// if IITC has already booted, immediately run the 'setup' function
if(window.iitcLoaded && typeof setup === 'function') setup();
} // wrapper end
// inject code into site context
var script = document.createElement('script');
var info = {};
if (typeof GM_info !== 'undefined' && GM_info && GM_info.script) info.script = { version: GM_info.script.version, name: GM_info.script.name, description: GM_info.script.description };
script.appendChild(document.createTextNode('('+ wrapper +')('+JSON.stringify(info)+');'));
(document.body || document.head || document.documentElement).appendChild(script);


