var fmwp_user_replies_page = 2;
var fmwp_user_no_replies_template = '<span class="fmwp-user-no-replies">' + wp.i18n.__( 'No replies', 'forumwp' ) + '</span>';

jQuery( document ).ready( function($) {
	fmwp_embed_containers.push( ".fmwp-reply-content" );

	$('.fmwp-replies-wrapper').each( function() {
		fmwp_user_replies( $(this), {
			page: 1,
			user_id: $(this).parents('.fmwp-user-replies').data('user_id')
		});
	});


	$( window ).scroll( function() {
		var scrollHandling = {
			allow: true,
			reallow: function() {
				scrollHandling.allow = true;
			},
			delay: 400 //(milliseconds) adjust to the highest acceptable value
		};

		if( ! fmwp_is_busy( 'user_replies' ) && scrollHandling.allow ) {
			scrollHandling.allow = false;

			var load_block = $('.fmwp-replies-wrapper .fmwp-load-more');
			if ( load_block.length ) {
				setTimeout( scrollHandling.reallow, scrollHandling.delay );

				var offset = load_block.offset().top - $( window ).scrollTop();
				if ( 1000 > offset ) {
					fmwp_user_replies( $('.fmwp-replies-wrapper'), {
						page: fmwp_user_replies_page,
						user_id: $('.fmwp-replies-wrapper').parents('.fmwp-user-replies').data('user_id')
					});
				}
			}
		}
	});
});

/**
 * Function for loading topics
 *
 * @param obj
 * @param args
 */
function fmwp_user_replies( obj, args ) {
	fmwp_set_busy( 'user_replies', true );

	wp.ajax.send( 'fmwp_profile_replies', {
		data: {
			user_id: args.user_id,
			page: args.page,
			nonce: fmwp_front_data.nonce
		},
		success: function( data ) {
			obj.find( '.fmwp-ajax-loading' ).hide();
			obj.find( '.fmwp-load-more' ).remove();

			if ( ! data.length ) {
				if ( args.page === 1 ) {
					obj.html( fmwp_user_no_replies_template );
				}
			} else {
				var template = wp.template( 'fmwp-replies-list' );
				var template_content = template({
					replies: data
				});

				if ( args.page === 1 ) {
					obj.html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_embed_resize_async();

				fmwp_user_replies_page = parseInt( args.page ) + 1;
			}

			fmwp_set_busy( 'user_replies', false );
		},
		error: function( data ) {
			console.log( data );
			obj.find( '.fmwp-ajax-loading' ).hide();
			jQuery(this).fmwp_notice({
				message: data,
				type: 'error'
			});
			fmwp_set_busy( 'user_replies', false );
		}
	});
}