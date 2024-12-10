var fmwp_topics_page = 2;

var fmwp_no_topics_template = fmwp_front_data.can_topic ? '<span class="fmwp-forum-no-topics">' + wp.i18n.__( 'No one has created a topic in this forum. Be the first and <a href="#" class="fmwp-create-topic fmwp-login-to-action">create a topic</a>.', 'forumwp' ) + '</span>' : '<span class="fmwp-forum-no-topics">' + wp.i18n.__( 'No one has created a topic in this forum.', 'forumwp' ) + '</span>';
var fmwp_no_topics_locked_template = '<span class="fmwp-forum-no-topics">' + wp.i18n.__( 'No one has created a topic in this forum.', 'forumwp' ) + '</span>';

var fmwp_no_topics_search_template = '<span class="fmwp-forum-no-topics">' + wp.i18n.__( 'No topics found for this search.', 'forumwp' ) + '</span>';

jQuery( document ).ready( function($) {

	fmwp_embed_containers.push( ".fmwp-forum-content" );

	$('.fmwp-forum-wrapper .fmwp-topics-wrapper').each( function() {
		let order = $(this).parents('.fmwp-forum-wrapper').find('.fmwp-forum-sort:visible').val();

		fmwp_get_topics_for_forum( $(this), {
			page: 1,
			order: order
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

		if ( ! fmwp_is_busy( 'individual_forum' ) && scrollHandling.allow ) {
			scrollHandling.allow = false;

			var load_block = $('.fmwp-forum-wrapper .fmwp-topics-wrapper .fmwp-load-more');
			var search_line = $('.fmwp-forum-wrapper .fmwp-topics-wrapper').parents('.fmwp-forum-content').siblings('.fmwp-forum-head').data( 'fmwp_search' );
			if ( load_block.length ) {
				setTimeout( scrollHandling.reallow, scrollHandling.delay );

				var offset = load_block.offset().top - $( window ).scrollTop();
				if ( 1000 > offset ) {
					let order = load_block.parents('.fmwp-forum-wrapper').find('.fmwp-forum-sort:visible').val();

					fmwp_get_topics_for_forum( $('.fmwp-forum-wrapper .fmwp-topics-wrapper'), {
						page: fmwp_topics_page,
						order: order,
						search: search_line
					});
				}
			}
		}
	});

	$( document.body ).on( 'change', '.fmwp-forum-sort', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var forum_id = $(this).parents('.fmwp-forum-head').data('fmwp_forum_id');
		var wrapper = $('.fmwp-topics-wrapper[data-fmwp_forum_id="' + forum_id + '"]');
		var order = $(this).val();
		var search_line = wrapper.parents('.fmwp-forum-content').siblings('.fmwp-forum-head').data( 'fmwp_search' );

		wrapper.find('.fmwp-forum-sort').val( order );

		fmwp_get_topics_for_forum( wrapper, {
			page: 1,
			order: order,
			search: search_line
		});
	});


	$( document.body ).on( 'click', '.fmwp-search-toggle', function(e) {
		e.preventDefault();

		if ( ! $(this).parents('.fmwp-forum-nav-bar-mobile').find('.fmwp-search-wrapper').is(':visible') ) {
			$(this).parents('.fmwp-forum-nav-bar-mobile').find('.fmwp-search-wrapper').css('display', 'flex');
			$(this).addClass('fmwp-active');
		} else {
			$(this).removeClass('fmwp-active');
			$(this).parents('.fmwp-forum-nav-bar-mobile').find('.fmwp-search-wrapper').css('display', 'none');
		}
	});


	$( document.body ).on( 'click', '.fmwp-search-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var search_line = $(this).siblings('label').find('.fmwp-forum-search-line').val();
		var search_data = $(this).parents('.fmwp-forum-head').data( 'fmwp_search' );
		if ( typeof search_data == 'undefined' && search_line === '' || search_data === search_line ) {
			return false;
		}

		$(this).parents('.fmwp-forum-head').find( '.fmwp-forum-search-line' ).val( search_line );
		$(this).parents('.fmwp-forum-head').data( 'fmwp_search', search_line );

		var forum_id = $(this).parents('.fmwp-forum-head').data('fmwp_forum_id');
		var wrapper = $('.fmwp-topics-wrapper[data-fmwp_forum_id="' + forum_id + '"]');
		var order = $(this).parents('.fmwp-forum-head').find('.fmwp-forum-sort:visible').val();

		fmwp_get_topics_for_forum( wrapper, {
			page: 1,
			order: order,
			search: search_line
		});
	});


	$( document.body ).on( 'keypress', '.fmwp-forum-search-line', function(e) {
		if ( e.which === 13 ) {
			$(this).parents('label').siblings('.fmwp-search-topic').trigger( 'click' );
		}
	});
});


/**
 * Function for loading topics
 *
 * @param obj
 * @param args
 */
function fmwp_get_topics_for_forum( obj, args ) {
	fmwp_set_busy( 'individual_forum', true );

	var ajax_data = {
		forum_id: obj.data( 'fmwp_forum_id' ),
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
						if ( obj.parents('.fmwp-forum-wrapper').hasClass('fmwp-forum-locked') || obj.parents('.fmwp-forum-wrapper').hasClass('fmwp-forum-pending') ) {
							obj.html( fmwp_no_topics_locked_template );
						} else {
							obj.html( fmwp_no_topics_template );
						}
					}

					obj.siblings('.fmwp-topics-wrapper-heading').addClass('fmwp-no-actions-heading');
				}
			} else {
				obj.siblings('.fmwp-topics-wrapper-heading').removeClass('fmwp-no-actions-heading');

				if( jQuery('#tmpl-fmwp-topics-list').length ) {
					var template = wp.template( 'fmwp-topics-list' );
					var template_content = template({
						topics: data
					});

					obj.data( 'order', args.order );

					obj.find( '.fmwp-load-more' ).remove();
					if ( args.page === 1 ) {
						obj.html( template_content + '<span class="fmwp-load-more"></span>' );
					} else {
						obj.append( template_content + '<span class="fmwp-load-more"></span>' );
					}
				}

				fmwp_topics_page = parseInt( args.page ) + 1;
			}

			fmwp_set_busy( 'individual_forum', false );
			wp.hooks.doAction( 'fmwp_topics_load_finish' );
		},
		error: function( data ) {
			fmwp_set_busy( 'individual_forum', false );
			console.log( data );
		}
	});
}
