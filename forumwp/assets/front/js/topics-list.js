var fmwp_topics_page = 2;

var fmwp_no_topics_template = '<span class="fmwp-forum-no-topics">' + wp.i18n.__( 'No topics have been created.', 'forumwp' ) + '</span>';
var fmwp_no_topics_search_template = '<span class="fmwp-forum-no-topics">' + wp.i18n.__( 'No results found.', 'forumwp' ) + '</span>';

jQuery( document ).ready( function($) {

	$('.fmwp-topics-wrapper').each( function() {
		var order = $(this).parents('.fmwp-archive-topics-wrapper').find('.fmwp-topics-sort').val();
		fmwp_get_topics( $(this), {
			page: 1,
			order: order,
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

		if( ! fmwp_is_busy( 'topics_list' ) && scrollHandling.allow ) {
			scrollHandling.allow = false;

			var load_block = $('.fmwp-topics-wrapper .fmwp-load-more');
			var search_line = $('.fmwp-topics-wrapper').parents('.fmwp-archive-topics-wrapper').data( 'fmwp_search' );
			var order = $('.fmwp-topics-wrapper').parents('.fmwp-archive-topics-wrapper').find('.fmwp-topics-sort').val();
			if ( load_block.length ) {
				setTimeout( scrollHandling.reallow, scrollHandling.delay );

				var offset = load_block.offset().top - $( window ).scrollTop();
				if ( 1000 > offset ) {
					fmwp_get_topics( $('.fmwp-topics-wrapper'), {
						page: fmwp_topics_page,
						order: order,
						search: search_line
					});
				}
			}
		}
	});


	$( document.body ).on( 'change', '.fmwp-topics-sort', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'topics_list' ) ) {
			return;
		}

		var order = $(this).val();
		var search_line = $(this).parents('.fmwp-archive-topics-wrapper').data( 'fmwp_search' );

		fmwp_topics_page = 1;

		fmwp_get_topics( $(this).parents('.fmwp-archive-topics-wrapper').find('.fmwp-topics-wrapper'), {
			page: 1,
			search: search_line,
			order: order
		});
	});


	$( document.body ).on( 'click', '.fmwp-search-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'topics_list' ) ) {
			return;
		}

		var search_line = $(this).siblings('label').find('.fmwp-topics-search-line').val();
		var search_data = $(this).parents('.fmwp-archive-topics-wrapper').data( 'fmwp_search' );
		if ( typeof search_data == 'undefined' && search_line === '' || search_data === search_line ) {
			return false;
		}

		$(this).parents('.fmwp-archive-topics-wrapper').find( '.fmwp-topics-search-line' ).val( search_line );
		$(this).parents('.fmwp-archive-topics-wrapper').data( 'fmwp_search', search_line );

		var order = $(this).parents('.fmwp-archive-topics-wrapper').find('.fmwp-topics-sort').val();

		fmwp_topics_page = 1;

		fmwp_get_topics( $(this).parents('.fmwp-archive-topics-wrapper').find('.fmwp-topics-wrapper'), {
			page: 1,
			search: search_line,
			order: order
		});
	});


	$( document.body ).on( 'keypress', '.fmwp-topics-search-line', function(e) {
		if ( e.which === 13 ) {
			$(this).parents('label').siblings('.fmwp-search-topic').trigger('click');
		}
	});

	$( document.body ).on( 'click', '.fmwp-search-toggle', function(e) {
		e.preventDefault();

		if ( ! $(this).parents('.fmwp-topics-list-head-mobile').find('.fmwp-search-wrapper').is(':visible') ) {
			$(this).parents('.fmwp-topics-list-head-mobile').find('.fmwp-search-wrapper').css('display', 'flex');
			$(this).addClass('fmwp-active');
		} else {
			$(this).removeClass('fmwp-active');
			$(this).parents('.fmwp-topics-list-head-mobile').find('.fmwp-search-wrapper').css('display', 'none');
		}
	});
});


/**
 * Function for loading topics
 *
 * @param obj
 * @param args
 */
function fmwp_get_topics( obj, args ) {
	fmwp_set_busy( 'topics_list', true );

	var ajax_data = {
		tag: obj.data('topic_tag_id'),
		status: obj.data('status'),
		type: obj.data('type'),
		page: args.page,
		order: args.order,
		nonce: fmwp_front_data.nonce
	};

	if ( args.search ) {
		ajax_data.search = args.search;
	}

	wp.ajax.send( 'fmwp_get_topics', {
		data: ajax_data,
		success: function( data ) {
			if ( ! data.length ) {
				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 ) {
					if ( args.search ) {
						obj.html( fmwp_no_topics_search_template );
					} else {
						obj.html( fmwp_no_topics_template );
					}

                    obj.siblings('.fmwp-topics-wrapper-heading').addClass('fmwp-no-actions-heading');
				}
			} else {
                obj.siblings('.fmwp-topics-wrapper-heading').removeClass('fmwp-no-actions-heading');

				var template = wp.template( 'fmwp-topics-list' );
				var template_content = template({
					topics: data
				});

				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 ) {
					obj.html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_topics_page = parseInt( args.page ) + 1;
			}

			fmwp_set_busy( 'topics_list', false );
		},
		error: function( data ) {
			console.log( data );
			fmwp_set_busy( 'topics_list', false );
		}
	});
}