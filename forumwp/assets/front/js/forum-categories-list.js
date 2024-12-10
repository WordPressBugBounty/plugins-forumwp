var fmwp_forum_categories_page = 2;
var fmwp_forum_categories_loading = false;

var fmwp_no_forum_categories_template = '<span class="fmwp-no-forum-categories">' + wp.i18n.__( 'No one created the forum categories.', 'forumwp' ) + '</span>';
var fmwp_no_forum_categories_search_template = '<span class="fmwp-no-forum-categories">' + wp.i18n.__( 'No results found.', 'forumwp' ) + '</span>';

jQuery( document ).ready( function($) {

	$('.fmwp-forum-categories-wrapper').each( function() {
		fmwp_get_forum_categories( $(this), {
			page: 1
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

		if( ! fmwp_forum_categories_loading && scrollHandling.allow ) {
			scrollHandling.allow = false;

			var search_line = $('.fmwp-forum-categories-wrapper').parents('.fmwp-archive-forums-wrapper').find('.fmwp-forum-categories-search-line').val();
			var load_block = $('.fmwp-forum-categories-wrapper .fmwp-load-more');
			if ( load_block.length ) {
				setTimeout( scrollHandling.reallow, scrollHandling.delay );

				var offset = load_block.offset().top - $( window ).scrollTop();
				if ( 1000 > offset ) {
					fmwp_get_forum_categories( $('.fmwp-forum-categories-wrapper'), {
						page: fmwp_forum_categories_page,
						search: search_line
					});
				}
			}
		}
	});


	$( document.body ).on( 'click', '.fmwp-search-forum-category', function(e) {
		e.preventDefault();

		var search_line = $(this).siblings('label').find('.fmwp-forum-categories-search-line').val();
		var search_data = $(this).parents('.fmwp-forum-categories-list-head').data( 'fmwp_search' );
		if ( typeof search_data == 'undefined' && search_line === '' || search_data === search_line ) {
			return false;
		}

		$(this).parents('.fmwp-forum-categories-list-head').data( 'fmwp_search', search_line );

		fmwp_get_forum_categories( $(this).parents('.fmwp-forum-categories-list-head').siblings('.fmwp-forum-categories-wrapper'), {
			page: 1,
			search: search_line
		});
	});


	$( document.body ).on( 'keypress', '.fmwp-forum-categories-search-line', function(e) {
		if ( e.which === 13 ) {
			$(this).siblings('.fmwp-search-forum-category').trigger('click');
		}
	});
});


/**
 * Function for loading topics
 *
 * @param obj
 * @param args
 */
function fmwp_get_forum_categories( obj, args ) {
	fmwp_forum_categories_loading = true;

	var data = {
		nonce: fmwp_front_data.nonce
	};

	if ( args.page !== 1 ) {
		data.offset = obj.data( 'fmwp_offset' );
		data.child_offset = obj.data( 'fmwp_child_offset' );
	}

	if ( args.search ) {
		data.search = args.search;
	}

	wp.ajax.send( 'fmwp_get_forum_categories', {
		data: data,
		success: function( data ) {
			if ( ! data.categories.length ) {
				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 ) {
					if ( args.search ) {
						obj.html( fmwp_no_forum_categories_search_template );
					} else {
						obj.html( fmwp_no_forum_categories_template );
					}
				}
			} else {
				var template = wp.template( 'fmwp-forum-categories-list' );
				var template_content = template({
					categories: data.categories
				});

				obj.data( 'fmwp_offset', data.pagination.offset );
				obj.data( 'fmwp_child_offset', data.pagination.child_offset );

				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 ) {
					obj.html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_forum_categories_page = parseInt( args.page ) + 1;
			}

			fmwp_forum_categories_loading = false;
			wp.hooks.doAction( 'fmwp_forum_categories_load_finish' );
		},
		error: function( data ) {
			console.log( data );
		}
	});
}
