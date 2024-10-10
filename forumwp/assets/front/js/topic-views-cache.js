jQuery( document ).ready( function() {
	wp.ajax.send( 'fmwp_topic_views', {
		data: {
			post_id: fmwp_topic_views.post_id,
			nonce: fmwp_front_data.nonce
		},
		success: function( data ) {
			jQuery( '#fmwp-views-total' ).html( data );
		},
		error: function( data ) {
			console.log( data );
		},
		cache: !1
	});
});