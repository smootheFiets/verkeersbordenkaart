/*
 	fietsviewer - grafische weergave van fietsdata
    Copyright (C) 2018-2019 Gemeente Den Haag, Netherlands
    assetwebsite - viewer en aanvraagformulier voor verkeersmanagementassets
    Copyright (C) 2020 Gemeente Den Haag, Netherlands
	Developed by Jasper Vries
	Modified for Verkeersbordenkaart
    Copyright (C) 2020 Jasper Vries
 
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/*
* Initialize global variables
*/
var map;
var selectedMapStyle = 'map-style-lighter';
var selectedTileLayer = 0;
var tileLayers = [
	{
		name: 'OpenStreetMap',
		layer: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
		    minZoom: 6,
		    maxZoom: 19
		})
	},
	{
		name: 'BRT Achtergrondkaart',
		layer: L.tileLayer('https://service.pdok.nl/brt/achtergrondkaart/wmts/v2_0/standaard/EPSG:3857/{z}/{x}/{y}.png', {
			minZoom: 6,
			maxZoom: 19,
			bounds: [[50.5, 3.25], [54, 7.6]],
			attribution: 'Kaartgegevens &copy; <a href="https://www.kadaster.nl">Kadaster</a> | <a href="https://www.verbeterdekaart.nl">Verbeter de kaart</a>'
		})
	},
	{
		name: 'Luchtfoto Actueel Ortho 8cm RGB',
		layer: L.tileLayer('https://service.pdok.nl/hwh/luchtfotorgb/wmts/v1_0/Actueel_orthoHR/EPSG:3857/{z}/{x}/{y}.jpeg', {
			minZoom: 6,
			maxZoom: 21,
			bounds: [[50.5, 3.25], [54, 7.6]],
			attribution: 'Kaartgegevens &copy; <a href="https://www.kadaster.nl">Kadaster</a>'
		})
	},
	{
		name: 'Thunderforest Transport',
		layer: L.tileLayer('https://tile.thunderforest.com/transport/{z}/{x}/{y}.png?apikey=423cd178822a4d178e961233ebb95dcf', {
			attribution: 'Maps &copy; <a href="http://www.thunderforest.com">Thunderforest</a>, Data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'
		})
	},
	{
		name: 'Thunderforest Buurten',
		layer: L.tileLayer('https://tile.thunderforest.com/neighbourhood/{z}/{x}/{y}.png?apikey=423cd178822a4d178e961233ebb95dcf', {
			attribution: 'Maps &copy; <a href="http://www.thunderforest.com">Thunderforest</a>, Data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'
		})
	}
];
var onloadCookie;
var markers = {};
var maplayers = {};
var oms;

/*
* Initialize the map on page load
*/
function initMap() {
        // Save parameters from URL, if any
        // (needs to happen before URL is manipulated in setMapCookie() )
        const parms = getUrlVars();
	//create map
	map = L.map('map');
	oms = new OverlappingMarkerSpiderfier(map, {keepSpiderfied: true});
	oms.addListener('click', function(marker) {
		openMapPopup(marker);
	});
	//add methods
	map.on('load', function() {
		$('#map-loading').hide();
	});
	map.on('moveend', function() {
		//store map position and zoom in cookie
		setMapCookie();
		updateMapStyle();
		updateMapLayers();
	});
	map.on('contextmenu', function(e) {
	        console.log(e);
	        var target = e.latlng;
		L.popup()
		.setLatLng(target)
		.setContent('<h1>' + target.lat.toFixed(6) + ',' + target.lng.toFixed(6) + '</h1><p><a href="https://www.google.nl/maps/?q=' + target.lat + ',' + target.lng + '&amp;layer=c&cbll=' + target.lat + ',' + target.lng + '&amp;cbp=11,' + 0 + ',0,0,5" target="_blank">Open locatie in Google Street View&trade;</a></p> <p><a href="' + getPermalink(target,map) + '" target="_blank">Permalink naar deze locatie</a></p> <p><a href="http://127.0.0.1:8111/zoom?left=' + target.lng + '&bottom=' + target.lat + '&right=' + target.lng + '&top='  + target.lat + '" target="hiddenIframe">JOSM remote-control</a></p>')
		.openOn(map);
	})
	//set map position from cookie, if any
	if ((typeof onloadCookie !== 'undefined') && ($.isNumeric(onloadCookie[1]))) {
		//get and use center and zoom from cookie
		map.setView(onloadCookie[0], onloadCookie[1]);
		//get map style from cookie
		setMapStyle(onloadCookie[2]);
	}
	else {
		//set initial map view
	        map.setView([53.21129,6.56422],17);
	}
	//set tile layer
	setMapTileLayer(selectedTileLayer);
	//modify some map controls
	map.zoomControl.setPosition('topleft');
	L.control.scale().addTo(map);
        //set map position from URL if passed
        const centeratid = parms['id'];
	if (typeof centeratid !== 'undefined') {
	    centerMapAtId(centeratid);
	}
        if ( (typeof parms['lat'] !== 'undefined') && (typeof parms['lng'] != 'undefined') && (typeof parms['z']) !== 'undefined' ) {
	    centerMapAtCoords(parms);
	}
}

/*
* Set the map tileset
*/
function setMapTileLayer(tile_id) {
	for (var i = 0; i < tileLayers.length; i++) {
		if (i == tile_id) {
			map.addLayer(tileLayers[i].layer);
		}
		else {
			map.removeLayer(tileLayers[i].layer);
		}
	}
	selectedTileLayer = tile_id;
	updateMapStyle();
	setMapCookie();
}

/*
* Get maps style on page load
*/
function getMapStyle() {
	//get map style
	if ((typeof onloadCookie !== 'undefined') && ((onloadCookie[2] == 'map-style-grayscale') || (onloadCookie[2] == 'map-style-lighter')  || (onloadCookie[2] == 'map-style-dark') || (onloadCookie[2] == 'map-style-oldskool') || (onloadCookie[2] == 'map-style-cycle'))) {
		selectedMapStyle = onloadCookie[2];
	}
	else {
		selectedMapStyle = 'map-style-default';
	}
	//set correct radio button
	$('#' + selectedMapStyle).prop('checked', true);
	//update map style
	updateMapStyle();
}

/*
* Set the map style and store it in the cookie
*/
function setMapStyle(style_id) {
	if ((style_id == 'map-style-grayscale') || (style_id == 'map-style-lighter') || (style_id == 'map-style-dark') || (style_id == 'map-style-oldskool') || (style_id == 'map-style-cycle')) {
		selectedMapStyle = style_id;
	}
	else {
		selectedMapStyle = 'map-style-default';
	}
	setMapCookie();
}

/*
* Apply or remove a CSS style when the user changes the map style or the map
*/
function updateMapStyle() {
	$('img.leaflet-tile').removeClass('map-style-grayscale');
	$('img.leaflet-tile').removeClass('map-style-lighter');
	$('img.leaflet-tile').removeClass('map-style-dark');
	$('img.leaflet-tile').removeClass('map-style-oldskool');
	//map recolor
	if ((selectedMapStyle == 'map-style-grayscale') || (selectedMapStyle == 'map-style-lighter') ||  (selectedMapStyle == 'map-style-dark') || (selectedMapStyle == 'map-style-oldskool')) {
		$('img.leaflet-tile').addClass(selectedMapStyle);
	}
}

/*
* Update map layers
*/
function updateMapLayers() {
	var num_activelayers = 0;
	$.each(maplayers, function(layer, options) {
		if (options.active == true) {
			loadMarkers(layer);
			num_activelayers++;
		}
		else {
			unloadMarkers(layer);
		}
	});
	//show no-layer activated message
	if (num_activelayers == 0) {
		$('#map-nolayersactive').show();
		$('#map-zoomwarning').hide();
	}
	else {
		$('#map-nolayersactive').hide();
		//show insufficient zoom warning
		if (map.getZoom() <= 14) {
			$('#map-zoomwarning').show();
		}
		else {
			$('#map-zoomwarning').hide();
		}
	}
}

/*
* Load/update markers for map layer
*/
function loadMarkers(layer) {
	$('#map-loading').show();
	//check if layer has entry in makers object and add it if not
	if (!markers.hasOwnProperty(layer)) {
		markers[layer] = [];
	}
	//draw new markers if they are not already drawn
	var visibleMarkerIds = [];
	$.getJSON('maplayer.php', { layer: layer, bounds: map.getBounds().toBBoxString(), zoom: map.getZoom() })
	.done( function(json) {
		$.each(json, function(index, v) {
			visibleMarkerIds.push(v.id);
			//find if marker is already drawn
			var markerfound = false;
			for (var i = 0; i < markers[layer].length; i++) {
				if (markers[layer][i].options.x_id == v.id) {
					markerfound = true;
					break;
				}
			}
			//add new marker
			if (markerfound == false) {
				var marker;
				/*if (layer == 'hecto') {
					marker = L.marker([v.lat, v.lon], {
						x_id: v.id,
						icon: L.icon({	iconUrl: 'style/milemarker.png', iconSize: [4,4] }),
						title: v.id
					}).bindTooltip(v.id, {
						permanent: true, 
						direction: 'right',
						className: 'hectolabel'
					});
				}
				else {*/
					marker = L.marker([v.lat, v.lon], {
						x_id: v.id,
						icon: L.icon({	iconUrl: 'image.php?s=24&i=' + v.code, iconSize: [24,24] }),
						//zIndexOffset: ((layer == 2) ? 1000: 0), //TODO manage this from database, this assumes layer 2 is CAM layer, which is the case in the default install
						//rotationAngle: v.heading,
						//rotationOrigin: 'center',
						title: v.code
					});
				/*}*/
				marker.addTo(map);
				markers[layer].push(marker);
				oms.addMarker(marker);
			}
		});

		//remove markers that should not be drawn (both out of bound and as a result of filtering)
		for (var i = markers[layer].length - 1; i >= 0; i--) {
			if (visibleMarkerIds.indexOf(markers[layer][i].options.x_id) === -1) {
				oms.removeMarker(markers[layer][i]);
				markers[layer][i].remove();
				markers[layer].splice(i, 1);
				
			}
		}
		//remove loading indicator
		$('#map-loading').hide();
	});
}

/*
* Load marker's popup content
*/
function openMapPopup(marker) {
	console.log(marker);
	$.getJSON('maplayer.php', { get: 'popup', id: marker.options.x_id })
	.done( function(json) {
        marker.bindPopup(json.html).openPopup();
		marker._popup.update();
		//bind onclick to details window link
		$( "#popup_details" ).bind( "click", function() {
			openDetailsWindow(marker.options.x_id);
		  });
	})
	.fail( function() {
		marker.bindPopup('Fout: kan gegevens niet laden').openPopup();
	});
}

/*
* remove all markers for map layer
*/
function unloadMarkers(layer) {
	//check if layer has markers
	if (markers.hasOwnProperty(layer)) {
		for (var i = markers[layer].length - 1; i >= 0; i--) {
			markers[layer][i].remove();
			markers[layer].splice(i, 1);
		}
	}
}

/*
* center map at location of provided sign id
*/
function centerMapAtId(id) {
	//get coordinates from database
	//$.getJSON('maplayer.php', { get: 'coordinates', id: id })
	//.done( function(json) {
	//	////enable layer if necessary
	//	//maplayers[json['layer']].active = true;
	//	//$('#map-layer-' + json['layer']).prop('checked', true);
	//	//updateMapLayers();
	//        //center map and set zoom
	//	map.setView([json['latitude'], json['longitude']], 16);
	//        setMapCookie();
    //});
    if ( Number.isInteger(parseInt(id)) ) {
        openDetailsWindow( parseInt(id) );
    }
}

/*
* center map at coordinates passed in URL
*/
function centerMapAtCoords(parms){
    map.setView([ parseFloat(parms['lat']), parseFloat(parms['lng']) ], Math.round(parseFloat(parms['z'])));
    setMapCookie();
}

/*
* Set the cookie to remember map center, zoom, style and active layers
* Also change URL in address bar to permalink w/ coordinates + zoom
*/
function setMapCookie() {
	var activeMapLayers = [];
	$.each(maplayers, function(layer, options) {
		if (options.active == true) {
			activeMapLayers.push(layer);
		}
	});
        const stateObj =  [map.getCenter(), map.getZoom(), selectedMapStyle, activeMapLayers, selectedTileLayer];
        Cookies.set('verkeersbordenkaart_map', stateObj, {expires: 1000});
        const permalink = getPermalink( map.getCenter(), map );
        history.replaceState( stateObj, '', permalink);
        // Should probably add treatment of stateObj for when users hit back button...
}

/*
* initialize layer GUI
*/
function initLayerGUI() {
    //get map layers
    $('#map-layers input[type=checkbox]').each(function() {
		var layer = this.id.substr(10);
		if (typeof onloadCookie !== 'undefined') {
			if (onloadCookie[3].indexOf(layer) >= 0) {
                maplayers[layer] = {active: true};
				$('#map-layer-' + layer).prop('checked', true);
			}
			else {
				maplayers[layer] = {active: false};
			}
		}
		else {
		    maplayers[layer] = {active: true};
		    $('#map-layer-' + layer).prop('checked', true);
		}
	});
	$('#map-layers input[type=checkbox]').change( function() {
		var layer = this.id.substr(10);
		var enableState = $(this).prop('checked');
		maplayers[layer].active = enableState;
		updateMapLayers();
		setMapCookie();
    });
	updateMapLayers();
	setMapCookie();
}

/*
* Get maps tileset on page load
*/
function getMapTileLayer() {
	//get map style
	if ((typeof onloadCookie !== 'undefined') && (typeof onloadCookie[4] == 'number')) {
		selectedTileLayer = onloadCookie[4];
	}
	//set correct radio button
	$('#map-tile-' + selectedTileLayer).prop('checked', true);
}

/*
* draw tilelayer GUI
*/
function drawTileLayerGUI() {
	$.each(tileLayers, function(id, options) {
		$('#map-tile').append('<li><input type="radio" name="map-tile" id="map-tile-' + id + '"><label for="map-tile-' + id + '">' + options.name + '</label></li>');
	});
	$('#map-tile input[type=radio]').change( function() {
		var tile_id = this.id.substr(9);
		setMapTileLayer(parseInt(tile_id));
		$(this).prop('checked');
	});
}

/*
* details window, same as in tabel.js, except for map display and request from tabel.php
*/
function openDetailsWindow(id) {
    if ($('#detailsdialog').length == 0) {
        $('html').append('<div id="detailsdialog"></div>');
    }
    $('#detailsdialog').html('');
    $('#detailsdialog').dialog({
        autoOpen: false,
        title: 'laden...',
        height: 'auto',
        width: $(window).width() - 60,
        height: $(window).height() - 60,
        position: { my: 'center', at: 'center', of: window }
    });
    $("#detailsdialog").parent().css({position : 'fixed'}).end().dialog('open');
    $.getJSON('maplayer.php', { data: 'details', id: id } )
    .done (function(json) {
	map.setView(json.center,map.getZoom());
        $('#detailsdialog').html(json.html);
        $('#detailsdialog').dialog('option', 'title', json.title);
        //open map
        initMiniMap();
        const permalink = getPermaSign( id );
        history.replaceState( '', '', permalink);
        // Didn't create (meaningful) stateObj, might need it in future
    })
    .fail( function() {
        $('#detailsdialog').html('Kan gegevens niet laden');
        $('#detailsdialog').dialog('option', 'title', 'Fout');
    });
}
/* minimap for contrent from tabel.php*/
function initMiniMap() {
    var minimapposition = [$('#latitude').val(), $('#longitude').val()];

	var minimap = L.map('minimap').setView(minimapposition, 18);

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(minimap);
    
    L.marker(minimapposition, {
		draggable: true, 
		rotationAngle: $('#heading').val(),
		rotationOrigin: 'center',
		icon: L.icon({	iconUrl: 'style/milemarker.png', iconSize: [4,4] }),
	}).addTo(minimap);
}

/*
* document.ready
*/
$(function() {
	onloadCookie = Cookies.getJSON('verkeersbordenkaart_map');
	//initialize map
	drawTileLayerGUI();
	getMapTileLayer();
	initMap();
	getMapStyle();
	//handle to change map style
	$('#map-style input').change( function() {
		setMapStyle(this.id);
		updateMapStyle();
	});
	initLayerGUI();
});


// Read a page's GET URL variables and return them as an "associative array."
// from https://snipplr.com/view/19838/get-url-parameters
// 
function getUrlVars() {
	var map = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		map[key] = value;
	});
	return map;
}


// Return a permalink to the current location and zoom level.
function getPermalink(center,map) {
    return baseURL() + '?lat='+ center.lat.toFixed(9) +'&lng='+ center.lng.toFixed(9) + '&z=' + map.getZoom() ;
}

// Return a permalink to the current traffic sign ID
function getPermaSign( id ){
    return baseURL() + '?id=' + id;
}

// Aux: URL of index.php
function baseURL() {
    return location.protocol.concat("//").concat(window.location.host) + '/html/verkeersbordenkaart/index.php';
}
