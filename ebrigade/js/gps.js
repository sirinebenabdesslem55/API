function successCallback(position){
	var newLat = position.coords.latitude;
	var newLng = position.coords.longitude;
	$.post( "gps_save.php", { lat: newLat, lng: newLng, findAddress: 0 } );
};   

if (navigator.geolocation)
    var watchId = navigator.geolocation.watchPosition(successCallback, null, {enableHighAccuracy:true});
else
    alert('Votre navigateur ne prend pas en compte la g�olocalisation HTML5');