function initMap(mapContainer, lat, lon) {
    $(function () {

        // "Refresh" map after slide down (mobile
		// devices)
        $('#'+mapContainer)
            .parents('.collapsible')
            // Custom event
            .on('afterSlideDown', function () {

                // Repaint map
                google.maps.event.trigger(map, 'resize');

                // Re-center map
                map.setCenter(new google.maps.LatLng(lat, lon));
            });
    });

    google.maps.event.addDomListener(window, 'load', function () {
        var mapOptions = {zoom: 14, zoomControlOptions: { position: google.maps.ControlPosition.LEFT_BOTTOM }};
        map = new google.maps.Map(document.getElementById(mapContainer), mapOptions);
        map.setCenter(new google.maps.LatLng(lat, lon));
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(lat, lon),
            map: map,
            icon:  markerImg
        });

        // Register click on marker
        google.maps.event.addListener(marker, 'click', function() {
            window.open('https://maps.google.com?q=' + lat + ',' + lon);
        });
    })
}

function initStaticMap(mapContainer, lat, lon,url){
	marker = url + markerImg;
	map = $('#'+mapContainer);
	parent = map.parent();
	map.attr("src", "https://maps.googleapis.com/maps/api/staticmap?center="+lat+","+lon+"&zoom=15&size="+parent.width()+"x80&markers=icon:"+marker+"|"+lat+","+lon+"&key=AIzaSyAoK_7LQ3D0oMjok03vxB50LXe37yKlteE");
}

var markerImg = "/themes/meta-bootstrap3/images/marker.png";