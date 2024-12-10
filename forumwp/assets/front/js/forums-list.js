var fmwp_forums_page = 2;

var fmwp_no_forums_search_template = '<span class="fmwp-no-forums">' + wp.i18n.__( 'No results found.', 'forumwp' ) + '</span>';

jQuery( document ).ready( function($) {
	$('.fmwp-forums-wrapper').each( function() {
		var order = $(this).parents('.fmwp-archive-forums-wrapper').data('order');
		fmwp_get_forums( $(this), {
			page: 1,
			order: order,
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

		if ( ! fmwp_is_busy( 'forums_list' ) && scrollHandling.allow ) {
			scrollHandling.allow = false;
			var search_line = $('.fmwp-forums-wrapper').parents('.fmwp-archive-forums-wrapper').find('.fmwp-forums-search-line').val();
			var order = $('.fmwp-forums-wrapper').parents('.fmwp-archive-forums-wrapper').data('order');

			var load_block = $('.fmwp-forums-wrapper .fmwp-load-more');
			if ( load_block.length ) {
				setTimeout( scrollHandling.reallow, scrollHandling.delay );

				var offset = load_block.offset().top - $( window ).scrollTop();
				if ( 1000 > offset ) {
					fmwp_get_forums( $('.fmwp-forums-wrapper'), {
						page: fmwp_forums_page,
						order: order,
						search: search_line
					});
				}
			}
		}
	});


	$( document.body ).on( 'click', '.fmwp-search-forum', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'forums_list' ) ) {
			return;
		}

		var search_line = $(this).siblings('label').find('.fmwp-forums-search-line').val();
		var search_data = $(this).parents('.fmwp-forums-list-head').data( 'fmwp_search' );
		if ( typeof search_data == 'undefined' && search_line === '' || search_data === search_line ) {
			return false;
		}

		var order = $(this).parents('.fmwp-archive-forums-wrapper').data('order');
		$(this).parents('.fmwp-forums-list-head').data( 'fmwp_search', search_line );

		fmwp_forums_page = 1;

		fmwp_get_forums( $(this).parents('.fmwp-forums-list-head').siblings('.fmwp-forums-wrapper'), {
			page: 1,
			search: search_line,
			order: order,
		});
	});


	$( document.body ).on( 'keypress', '.fmwp-forums-search-line', function(e) {
		if ( e.which === 13 ) {
			$(this).parents('label').siblings('.fmwp-search-forum').trigger('click');
		}
	});
});


/**
 * Function for loading forums
 *
 * @param obj
 * @param args
 */
function fmwp_get_forums( obj, args ) {
	fmwp_set_busy( 'forums_list', true );

	var ajax_data = {
		page: args.page,
		order: args.order,
		category: obj.data( 'category_id' ),
		with_sub: obj.data( 'with_subcategories' ),
		nonce: fmwp_front_data.nonce
	};

	if ( args.search ) {
		ajax_data.search = args.search;
	}

	wp.ajax.send( 'fmwp_get_forums', {
		data: ajax_data,
		success: function( data ) {
			if ( ! data.forums.length ) {
				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 ) {
					if ( args.search ) {
						obj.html( fmwp_no_forums_search_template );
					} else {
						obj.html( '<span class="fmwp-no-forums">' + obj.data( 'no-forums-text' ) + '</span>' );
					}

                    obj.siblings('.fmwp-forums-wrapper-heading').addClass('fmwp-no-actions-heading');
				}
			} else {
				if ( data.actions ) {
                	obj.siblings('.fmwp-forums-wrapper-heading').removeClass('fmwp-no-actions-heading');
				} else {
                    if ( args.page === 1 ) {
                    	obj.siblings('.fmwp-forums-wrapper-heading').addClass('fmwp-no-actions-heading');
                    }
				}

				var template = wp.template( 'fmwp-forums-list' );
				var template_content = template({
					forums: data.forums
				});

				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 ) {
					obj.html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_forums_page = parseInt( args.page ) + 1;
			}

			fmwp_set_busy( 'forums_list', false );
			wp.hooks.doAction( 'fmwp_forums_load_finish' );
		},
		error: function( data ) {
			console.log( data );
			fmwp_set_busy( 'forums_list', false );
		}
	});
}
