var map,infoWindow ;
var myLatLng;

$(document).ready(function() {
  geoLocationInit();
  function geoLocationInit()
  {
    if (navigator.geolocation){
      navigator.geolocation.getCurrentPosition(success,fail);
    }else{
      alert("Browser not supported");
      //handleLocationError(false, infoWindow, map.getCenter());
    }
  }
  function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ?
                          'Error: The Geolocation service failed.' :
                          'Error: Your browser doesn\'t support geolocation.');
    infoWindow.open(map);
  }


  function success(position) {
       console.log(position);
       var latval = position.coords.latitude;
       var lngval = position.coords.longitude;
       document.getElementById("langitude_txt").value=latval;
       document.getElementById("longitude_txt").value=lngval;
       myLatLng = new google.maps.LatLng(latval, lngval);
       createMap(myLatLng);
      //  nearbySearch(myLatLng, "school");
       //searchGirls(latval,lngval);
   }

   function fail() {
        alert("You must share location before login!");
        document.getElementById("btnlogin").disabled =true;
    }

  //var myLatLng = new google.maps.LatLng(-33.8665433,151.1956316);

  function createMap(myLatLng){
    map = new google.maps.Map(document.getElementById('map'), {
            center: myLatLng,
            zoom: 18
          });
    var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            draggable:true,
            label:"Posisi outlet"
        });
    //infoWindow = new google.maps.InfoWindow;
    google.maps.event.addListener(marker, 'dragend', function (event) {
  	  document.getElementById("langitude_txt").value=this.getPosition().lat();
         document.getElementById("longitude_txt").value=this.getPosition().lng();
    });
  }

//this is marker 
  function createMarker(latlng,icn,name){ 
    var markers = new google.maps.Marker({
              position: latlng,
              map: map,
              icon:icn,
              label: name
            });
  }

  function nearbySearch(myLatLng, myType){
    var request = {
            location: myLatLng,
            radius: '500',
            type: [myType]
          };

    service = new google.maps.places.PlacesService(map);
    service.nearbySearch(request, callback);

    function callback(results, status) {
      console.log(results);
      if (status == google.maps.places.PlacesServiceStatus.OK) {
        for (var i = 0; i < results.length; i++) {
          var place = results[i];
          latlng =place.geometry.location;
          icn = "https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png";
          name = place.name
          createMarker(latlng, icn,name);
        }
      }
    }
  }



});
