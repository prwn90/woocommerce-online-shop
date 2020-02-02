jQuery( document ).ready( function($) {
	var $body = $( 'body' );
	$body.on( 'change', 'input[name=payment_method], #shipping_state, #billing_state', function() {
		$body.trigger( 'update_checkout' );
	});

	$body.on( 'focusout', '#billing_postcode, #shipping_postcode, #shipping_city, #billing_city', function() {
		$body.trigger( 'update_checkout' );
	});

})
