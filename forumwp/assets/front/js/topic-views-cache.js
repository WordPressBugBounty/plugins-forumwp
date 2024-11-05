jQuery( document ).ready( function() {
	let storageKey = 'topic_view_' + fmwp_topic_views.auth_id;

	// check topic ids
	let postIds = [];
	if ( localStorage.getItem('viewed_posts') ) {
		postIds = localStorage.getItem('viewed_posts');
		if (postIds) {
			postIds = JSON.parse(postIds);
		}
	}

	if ( ! localStorage.getItem( storageKey ) && ! postIds.includes( fmwp_topic_views.post_id ) ) {
		wp.ajax.send( 'fmwp_topic_views', {
			data: {
				post_id: fmwp_topic_views.post_id,
				auth_id: fmwp_topic_views.auth_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				jQuery( '#fmwp-views-total' ).html( data );
				localStorage.setItem( storageKey, 'viewed' );
			},
			error: function( data ) {
				console.log( data );
				if( 'storage' === data ) {
					localStorage.setItem( storageKey, 'viewed' );
				}
			},
			cache: !1
		});
	}

	// store topic ids
	if ( ! postIds.includes( fmwp_topic_views.post_id ) ) {
		postIds.push( fmwp_topic_views.post_id );
		localStorage.setItem('viewed_posts', JSON.stringify( postIds ) );
	}
});
