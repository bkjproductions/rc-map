// in our page, before this is loaded, we need to declare some globals, like myGoogleMap, etc.
// to customize this you need to go donw to initMap and put in some locations logo and whatever for the Primary location
var myGoogleMap;

// THIS initMap is called AFTER you do the SCRIPT SRC= google's map api + key
function initMap() {
	//define latitude/longitude for center TODO: GET PROPER LOCATION
	// You may want to have a different centerpoint than the actual main
	// attraction, of course, so consider not using myLatlng twice.
	var myLatLng = {lat: 42.347545772203596, lng: -71.14530998079026};
	// Create a map object and specify the DOM element for display.
	myGoogleMap = new google.maps.Map(document.getElementById('map'), {
		center: myLatLng,
		scrollwheel: false,
		zoom: 15,
		styles: get_map_style()

		});
	var myIcon = base_url + "/images/markers/marker_primary.png";	
	
	// PRIMARY marker: TODO: Change URL when time comes
	var contentString = '<div id="content">'+
		'<div id="bodyContent">'+
		//'<p><img src="' + base_url + '/images/logo.png" width="200"></p>' +
		'<h2>Stratus Residences</h2>'+
	    '<p><a href="tel:877.328.3312">877.328.3312</a></p>'+
		'<p> 191 Washington Street<br>Brighton, MA 02135 </p>' +
		'</div>'+
		'</div>';
	
	var infowindow = new google.maps.InfoWindow({
		content: contentString
	});
	
	var marker = new google.maps.Marker({
		position: myLatLng,
		map: myGoogleMap,
		title: 'Stratus Residences',
		icon: myIcon
	});
	marker.addEventListener('click', function() {
		hideAllInfoWindows();
		infowindow.open(myGoogleMap, marker);
	});
	initCategories();
	jQuery('body').addClass('map-loaded');
}

jQuery(document).ready(function($) {
	setTimeout(initMap,1000);
//jQuery( function($) {
	// Need to set up clicks for each Category button
	jQuery('#map-categories a').on('click', function() {
		var myid = $(this).attr('id');
		myid = myid.split('-')[0];
		showMarkers(myid);				
		return false;
	});
	
	jQuery('#All-link').on('click',showAllMarkers);	

	jQuery('#map-categories a').on('click',function() {
		var that = jQuery(this).blur();
		
		if (that.hasClass('selected')) {
			return;
		}

		jQuery('#map-categories a.selected').removeClass('selected');
		that.addClass('selected');	
		
	});

});


function createMarkers(markerData, markerIcon, markerArray,thecategory) {
	jQuery.each( markerData, function(i, info) {
		var myLatLng = {lat:info.positionLat, lng:info.positionLong};
		//alert(info.name);
		make_marker(info.name,info.html,markerIcon,myLatLng,thecategory,info);
	});	
}

function make_marker(title,html,theicon, myLatLng,thecategory,info) {
	var marker = new google.maps.Marker({
		position: myLatLng,
		map: myGoogleMap,
		title: title,
		icon: theicon
	});

	var contentString = '<div id="content">' +
		'<div id="bodyContent">' +
		'<h2>' + title + '</h2>' +
		html +
		'</div></div>';
	var myInfoWindow = new google.maps.InfoWindow({
		content: contentString
	});
	marker.category = thecategory;
	marker.url = info.url;	
	
	marker.addListener('click', function() {
		hideAllInfoWindows();
		myInfoWindow.open(myGoogleMap, marker);
	});
	marker.infowindow = myInfoWindow;
	// we store the markers in an array
	allMarkers.push(marker);
	console.log('marker');
	console.log(marker);
}

/***************************************
 *
 * Add functionality for hide/show links.
 *
 ***************************************/
function hideAllMarkers() {
	jQuery.each(allMarkers, function(i, marker) {
		marker.setVisible(false);
		marker.infowindow.close();
	});
}

function hideAllInfoWindows() {
	jQuery.each(allMarkers, function(i, marker) {
		marker.infowindow.close();
	});
}
function showAllMarkers() {
	console.log("showAllMarkers()");
	//alert("showAllMarkers()");
	var bounds = new google.maps.LatLngBounds();
	jQuery.each(allMarkers, function(i, marker) {
		console.log('marker');
		console.log(marker);
		bounds.extend(marker.position);
		marker.setVisible(true);
		marker.infowindow.close();
	});
	myGoogleMap.fitBounds(bounds);
	//bkjmap_extendBounds();
	return false; // stop default link behavior
}
function bkjmap_extendBounds () {
	var bounds = new google.maps.LatLngBounds();
	jQuery.each(allMarkers, function(i,marker) {
		console.log(marker);
		bounds.extend(marker.position);
	});
	myGoogleMap.fitBounds(bounds);
}


function showMarkers(whichCat) {	
	hideAllMarkers();
	var bounds = new google.maps.LatLngBounds();
	jQuery.each(allMarkers, function(i, marker) {
		marker.setVisible( whichCat == marker.category);
		if ( whichCat == marker.category) {
			bounds.extend(marker.position);
		}
		console.log(marker.category);
	});
	myGoogleMap.fitBounds(bounds);

}



//https://snazzymaps.com/style/97706/boston-east-website
function get_map_style() {
	var x = [
{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#e5e6e6"},{"saturation":"-61"},{"lightness":"-2"}]},{"featureType":"landscape.natural.landcover","elementType":"geometry.fill","stylers":[{"saturation":"-3"},{"color":"#c1aeae"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"saturation":"-14"},{"visibility":"on"},{"color":"#b2b5b6"},{"lightness":"9"},{"gamma":"1.10"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#b4b9ba"},{"lightness":"30"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#b9bfc0"}]},{"featureType":"road.highway.controlled_access","elementType":"geometry.fill","stylers":[{"color":"#afbaba"},{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"saturation":"31"},{"visibility":"on"},{"lightness":"43"},{"gamma":"2.02"},{"color":"#f1f6f7"}]},{"featureType":"road.arterial","elementType":"geometry.stroke","stylers":[{"color":"#c85151"},{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"#ecf6f7"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry.fill","stylers":[{"color":"#b3bbbc"},{"visibility":"on"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#b2babe"},{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"lightness":"29"}]}]
;

return x;
}
