var caretEzGm_map = new Array();
var caretEzGm_geo = new Array();
var caretEzGm_marker = new Array();
function googlemap_init(map_div, addr)
{
	var opts = {
		zoom: 15,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	caretEzGm_map[map_div] = new google.maps.Map(document.getElementById(map_div), opts);
	caretEzGm_geo[map_div] = new google.maps.Geocoder();
	caretEzGm_geo[map_div].geocode({'address': addr}, function(results, status)
	{
		if (status == google.maps.GeocoderStatus.OK) {
			caretEzGm_map[map_div].setCenter(results[0].geometry.location);
			caretEzGm_marker[map_div] = new google.maps.Marker(
			{
				map: caretEzGm_map[map_div],
				title: addr,
				position: results[0].geometry.location
			});
		}
	});
}
