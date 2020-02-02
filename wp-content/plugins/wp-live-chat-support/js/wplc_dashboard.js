var wplcApiUrls = {
  	visitorURL: WPLC_SOCKET_URI + '/api/v1/total-visitors-online?api_key='+nifty_api_key
}

function getTotalVisitors() {
	jQuery.getJSON( wplcApiUrls.visitorURL, function( data ) {
		jQuery('#totalVisitors').html( data );
	});
}

jQuery(document).ready(function($){
	getTotalVisitors();
});