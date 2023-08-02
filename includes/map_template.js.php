// map_template.js.php

// This is a JavaScript template file with placeholders for data






var markers = {{MARKERS}};
var myGoogleMap;
var allMarkers = [];
var allInfoWindows = [];
var principalInfo = {{PRINCIPAL_INFO}};
var snazzyStyle = {{MAP_STYLE}};
var mapSettings = {{MAP_SETTINGS}};

function initMap() {
    // Initialize Google Maps

    var myLatLng = {lat: parseFloat(principalInfo.latitude), lng: parseFloat(principalInfo.longitude) };
    var mapCenter = {lat: parseFloat(mapSettings.center_latitude), lng: parseFloat(mapSettings.center_longitude) };
    var mapOptions = {
        center: mapCenter,
        zoom: parseFloat(mapSettings.zoom),
        scrollwheel: false,
        styles:snazzyStyle
        // Other map options here
        //mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    myGoogleMap = new google.maps.Map(document.getElementById('map'), mapOptions);


    // PRIMARY marker: TODO: Change URL when time comes
    var contentString = '<div id="content">'+
        '<div id="bodyContent">'+
        //'<p><img src="' + base_url + '/images/logo.png" width="200"></p>' +
       '<h2>'+ principalInfo.name +'</h2>'+
        '<p><a href="tel:' + principalInfo.phone + '">'+ principalInfo.phone +'</a></p>'+
        '<p>' + principalInfo.address +'<br>' + principalInfo.city + ', ' + principalInfo.state + ' ' + principalInfo.zip_code + '</p>' +
        '</div> '+
        '</div>';

    var infowindow = new google.maps.InfoWindow({
        content: contentString
    });


    var marker = new google.maps.Marker({
        position: myLatLng,
        map: myGoogleMap,
        title: principalInfo.name,
        icon: principalInfo.icon_url
    });
    marker.addListener("click", ({domEvent, latLng}) => {
        const {target} = domEvent;
        infowindow.open({
            anchor: marker,
            myGoogleMap,
        });
    });

   initCategories();
   jQuery('body').addClass('map-loaded');
}



// Need to set up clicks for each Category button
const legendItems = document.querySelectorAll('#map-categories a');
legendItems.forEach(item=>{
    item.addEventListener('click',(evt)=>{
        evt.preventDefault();
        let id = item.getAttribute('id');
        id = id.split('-')[0];
        showMarkers(id)
    })
})

const showAllLegendButton = document.getElementById('All-link');
showAllLegendButton.addEventListener('click',()=>{
    showAllMarkers();
})

    //jQuery('#All-link').on('click',showAllMarkers);

    jQuery('#map-categories a').on('click',function() {
        var that = jQuery(this).blur();

        if (that.hasClass('selected')) {
            return;
        }

        jQuery('#map-categories a.selected').removeClass('selected');
        that.addClass('selected');

    });
    function showMarkers(category) {

        hideAllMarkers();

        var bounds = new google.maps.LatLngBounds();
        //allMarkers.forEach(marker=> {
        //    if (category )
        //})
        jQuery.each(allMarkers, function(i, marker) {
            marker.setVisible( category == marker.category);
            if ( category == marker.category) {
                bounds.extend(marker.position);
            }
        });
        myGoogleMap.fitBounds(bounds);

    }

    function showAllMarkers() {

        var bounds = new google.maps.LatLngBounds();
        hideAllInfoWindows();
        jQuery.each(allMarkers, function(i, marker) {
            bounds.extend(marker.position);
            marker.setVisible(true);
        });
        myGoogleMap.fitBounds(bounds);
        return false; // stop default link behavior
    }




function createMarkers(markerData, markerIcon, markerArray,thecategory) {
    jQuery.each( markerData, function(i, info) {
        var myLatLng = {lat:info.positionLat, lng:info.positionLong};
        //alert(info.name);
        make_marker(info.name,info.html,markerIcon,myLatLng,thecategory,info);
    });
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



function initCategories(){

    markers.forEach(marker => {

        const svgMarker = {
            path: "M12 0C7 0 3 4 3 9c0 6 9 15 9 15s9-9 9-15c0-5-4-9-9-9zm0 13c-2 0-4-2-4-4s2-4 4-4 4 2 4 4-2 4-4 4zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z",
            fillColor: marker.color,
            fillOpacity: 1,
            strokeWeight: 0,
            rotation: 0,
            scale: parseFloat(mapSettings.scale),
            anchor: new google.maps.Point(0, 20),
        };
        const coords = marker.geo_code.split(', ');
        const latLon =  { lat: parseFloat(coords[0]), lng: parseFloat(coords[1]) };

        const newMarker = new google.maps.Marker({
            category: marker.category,
            position: latLon,
            map: myGoogleMap,
            title: marker.post_title,
            icon: svgMarker
        });

        let contentString = '<div id="content">' +
            '<div id="bodyContent">' +
            '<h2>' + marker.post_title + '</h2>' +
            '<p>' + marker.address +'<br>' +
            '<br>' + marker.city + ', ' +  marker.state + ' ' + marker.zip_code +
            '<br>' + marker.phone +'<p>' +
            '</div></div>';

        if (marker.url > 'https://'){

            contentString += `<p><a href="${marker.url}" target="_blank">Visit website</a></p>`
        }

        const infoWindow = new google.maps.InfoWindow({
            content: contentString,
            ariaLabel: "Uluru",
        })
        newMarker.addListener("click", () => {

            hideAllInfoWindows();
            infoWindow.open({
                myGoogleMap,
                anchor: newMarker,
            });
        });
        allInfoWindows.push(infoWindow)
        allMarkers.push(newMarker);
    })

}
/***************************************
 *
 * Add functionality for hide/show links.
 *
 ***************************************/
function hideAllMarkers() {
    allMarkers.forEach(marker => {
        marker.setVisible(false)
    })
    hideAllInfoWindows();
    //jQuery.each(allMarkers, function(i, marker) {
    //    marker.setVisible(false);
    //    marker.infowindow.close();
    //});
}

function hideAllInfoWindows() {

    allInfoWindows.forEach(window => {
        window.close();
    })

}
function showAllMarkers() {
    //alert("showAllMarkers()");
    hideAllInfoWindows();
    var bounds = new google.maps.LatLngBounds();
    jQuery.each(allMarkers, function(i, marker) {
        bounds.extend(marker.position);
        marker.setVisible(true);

    });
    myGoogleMap.fitBounds(bounds);
    //bkjmap_extendBounds();
    return false; // stop default link behavior
}
function bkjmap_extendBounds () {
    var bounds = new google.maps.LatLngBounds();
    jQuery.each(allMarkers, function(i,marker) {
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