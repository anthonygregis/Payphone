<?php 
require_once "config.php";

/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$query = "SELECT * FROM phones";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
$tableArray = array();
$counter = 0;
while ($row = mysqli_fetch_array($result))
{
	 $tableArray[$counter]['xcoord'] = $row['xcoord'];
     $tableArray[$counter]['ycoord'] = $row['ycoord'];
     $counter++;
}

//Close Connection
mysqli_close($link);
?>
<style>	
body, html {
	padding:0px;
	margin:0px;
	
	background-color: black;
}

#map {
	width:1126.69px;
	height:600px;
	color: #000;
	background: #EFEFEF;
	margin:0 auto;
}
span.loading {
	display: block;
	text-align: center;
	font: 300 italic 72px/400px "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", sans-serif;
}
img[src="https://maps.gstatic.com/mapfiles/api-3/images/google4.png"], a[href^="https://maps.google.com/maps"]{
	display: none !important;
}
.gmnoprint a, .gmnoprint span {
    display:none;
}

</style>


<div id="map"><span class="loading">loading tiles...</span></div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCJ_60cFo6LSEhKoXowt229aUtCeN8uNxE&sensor=false"></script>
<script>
var repeatOnXAxis = false; // Do we need to repeat the image on the X-axis? Most likely you'll want to set this to false
function getNormalizedCoord(coord, zoom) {
	if (!repeatOnXAxis) return coord;
	var y = coord.y;
	var x = coord.x;
	// tile range in one direction range is dependent on zoom level
	// 0 = 1 tile, 1 = 2 tiles, 2 = 4 tiles, 3 = 8 tiles, etc
	var tileRange = 1 << zoom;
	// don't repeat across Y-axis (vertically)
	if (y < 0 || y >= tileRange) {
		return null;
	}
	// repeat across X-axis
	if (x < 0 || x >= tileRange) {
		x = (x % tileRange + tileRange) % tileRange;
	}
	return {
		x: x,
		y: y
	};
}

var map;
var static_markers = [];

// Define our custom map type
var satellite = new google.maps.ImageMapType({
	getTileUrl: function(coord, zoom) {
		var normalizedCoord = getNormalizedCoord(coord, zoom);
		if(normalizedCoord && (normalizedCoord.x < Math.pow(2, zoom)) && (normalizedCoord.x > -1) && (normalizedCoord.y < Math.pow(2, zoom)) && (normalizedCoord.y > -1)) {
			return 'Satellite/' + zoom + '_' + normalizedCoord.x + '_' + normalizedCoord.y + '.jpg';
		} else {
			return 'Satellite/empty.jpg';
		}
	},
	tileSize: new google.maps.Size(256, 256),
	maxZoom: 7,
	minZoom:2,
    zoom:2,
	name: 'Satellite'
});
var roadmap = new google.maps.ImageMapType({
	getTileUrl: function(coord, zoom) {
		var normalizedCoord = getNormalizedCoord(coord, zoom);
		if(normalizedCoord && (normalizedCoord.x < Math.pow(2, zoom)) && (normalizedCoord.x > -1) && (normalizedCoord.y < Math.pow(2, zoom)) && (normalizedCoord.y > -1)) {
			return 'Roadmap/' + zoom + '_' + normalizedCoord.x + '_' + normalizedCoord.y + '.jpg';
		} else {
			return 'Roadmap/empty.jpg';
		}
	},
	tileSize: new google.maps.Size(256, 256),
	maxZoom: 7,
	minZoom:2,
    zoom:2,
	name: 'Roadmap'
});
var atlas = new google.maps.ImageMapType({
	getTileUrl: function(coord, zoom) {
		var normalizedCoord = getNormalizedCoord(coord, zoom);
		if(normalizedCoord && (normalizedCoord.x < Math.pow(2, zoom)) && (normalizedCoord.x > -1) && (normalizedCoord.y < Math.pow(2, zoom)) && (normalizedCoord.y > -1)) {
			return 'Atlas/' + zoom + '_' + normalizedCoord.x + '_' + normalizedCoord.y + '.jpg';
		} else {
			return 'Atlas/empty.jpg';
		}
	},
	tileSize: new google.maps.Size(256, 256),
	maxZoom: 7,
	minZoom:2,
    zoom:2,
	name: 'Atlas'
});

// Basic options for our map
var myOptions = {
	center: new google.maps.LatLng(0, 0),
	zoom: 2,
	minZoom: 0,
	streetViewControl: false,
	mapTypeControl: true,
	mapTypeControlOptions: {
		mapTypeIds: ["gta_satellite", "gta_roadmap", "gta_atlas"]
	}
};

// Init the map and hook our custom map type to it
map = new google.maps.Map(document.getElementById('map'), myOptions);
map.mapTypes.set('gta_satellite', satellite);
map.mapTypes.set('gta_roadmap', roadmap);
map.mapTypes.set('gta_atlas', atlas);
// sets default 'startup' map
map.setMapTypeId('gta_roadmap');

var overlay = new google.maps.OverlayView();
overlay.draw = function() {};
overlay.setMap(map);

// Sets the map on all markers in the array.
function setAllMap(map) {
  for (var i = 0; i < static_markers.length; i++) {
	static_markers[i].setMap(map);
  }
}
	
// Removes the markers from the map, but keeps them in the array.
function clearMarkers() {
  setAllMap(null);
}
	
// Shows any markers currently in the array.
function showMarkers() {
  setAllMap(map);
}
	
	var contentString = '<div id="content">'+
	  '<div id="marker_infobox">'+
	  'this is a test'+
	  '</div>'+
	  '</div>';
	
	function gtamp2googlepx(x,y) {
		// IMPORTANT
		// for this to work #map must be width:1126.69px; height:600px;
		// you can change this AFTER all markers are placed...
		//--------------------------------------
		//conversion increment from x,y to px,py
		var mx = 0.05030;
		var my = -0.05030; //-0.05003
		//math mVAR * cVAR
		var x = mx * x;
		var y = my * y;
		//offset for correction
		var x = x -486.97;
		var y = y +408.9;
		//return latlong coordinates
		return pixelLatLng = overlay.getProjection().fromContainerPixelToLatLng(new google.maps.Point(x,y));
	}
	
	function addMarker(x,y, type, content_html, icon, ig) {
	  if(ig) {
			//to ingame 2 google coords here, use function.
			var location = gtamp2googlepx(x,y);
		} else {
			var location = new google.maps.LatLng(x,y);
		}
	  var marker = new google.maps.Marker({
		position: location,
		map: map,
		icon: icon+'.png',
		optimized: false //to prevent it from repeating on the x axis.
	  });
	  if(type == "static") { static_markers.push(marker); }
	  	
		var infowindow = new google.maps.InfoWindow({
			content: content_html+'<br><br></b>LatLong: '+location
		});
		//when you click anywhere on the map, close all open windows...
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
			
			google.maps.event.addListener(map, 'click', function() {
				infowindow.close();
            });
		});
	} 
	
	google.maps.event.addListenerOnce(map, 'idle', function(){
		<?php $i = 1; foreach($tableArray as $row) {?>
			addMarker(<?php echo $row['xcoord'] ?>,<?php echo $row['ycoord'] ?>, "static", 'Payphone', 'phone-dot', true);
		<?php } ?>
		document.getElementById("map").style.height="100%";
		document.getElementById("map").style.width="100%";
	});
</script>