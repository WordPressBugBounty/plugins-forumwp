var fmwp_user_topics_page = 2;
var fmwp_user_topics_loading = false;
var fmwp_user_no_topics_template = '<span class="fmwp-user-no-topics">' + wp.i18n.__( 'No topics', 'forumwp' ) + '</span>';

jQuery( document ).ready( function($) {

	$('.fmwp-user-topics .fmwp-topics-wrapper').each( function() {
		fmwp_user_topics( $(this), {
			page: 1,
			user_id: $(this).parents('.fmwp-user-topics').data('user_id')
		});
	});


	$( window ).on( 'scroll', function() {
		var scrollHandling = {
			allow: true,
			reallow: function() {
				scrollHandling.allow = true;
			},
			delay: 400 //(milliseconds) adjust to the highest acceptable value
		};

		if ( ! fmwp_is_busy( 'user_topics' ) && scrollHandling.allow ) {
			scrollHandling.allow = false;

			var load_block = $('.fmwp-user-topics .fmwp-topics-wrapper .fmwp-load-more');
			if ( load_block.length ) {
				setTimeout( scrollHandling.reallow, scrollHandling.delay );

				var offset = load_block.offset().top - $( window ).scrollTop();
				if ( 1000 > offset ) {
					fmwp_user_topics( $('.fmwp-user-topics .fmwp-topics-wrapper'), {
						page: fmwp_user_topics_page,
						user_id: $('.fmwp-user-topics .fmwp-topics-wrapper').parents('.fmwp-user-topics').data('user_id')
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
function fmwp_user_topics( obj, args ) {
	if ( ! args.user_id ) {
		return;
	}
	fmwp_set_busy( 'user_topics', true );

	wp.ajax.send( 'fmwp_profile_topics', {
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
					obj.html( fmwp_user_no_topics_template );
				}
			} else {
				var template = wp.template( 'fmwp-topics-list' );
				var template_content = template({
					topics: data
				});

				if ( args.page === 1 ) {
					obj.html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_embed_resize_async();

				fmwp_user_topics_page = parseInt( args.page ) + 1;
			}

			fmwp_set_busy( 'user_topics', false );
			wp.hooks.doAction( 'fmwp_user_topics_load_finish' );
		},
		error: function( data ) {
			console.log( data );
			obj.find( '.fmwp-ajax-loading' ).hide();
			jQuery(this).fmwp_notice({
				message: data,
				type: 'error'
			});
			fmwp_set_busy( 'user_topics', false );
		}
	});
}
