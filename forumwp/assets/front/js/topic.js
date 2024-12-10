var fmwp_replies_page = 2;

var fmwp_no_replies_template = fmwp_front_data.can_reply ? '<span class="fmwp-topic-no-replies">' + wp.i18n.__( 'No one has replied to this topic. Be the first and <a href="#" class="fmwp-write-reply fmwp-login-to-action" data-fmwp_popup_title="Login to reply to this topic">leave a reply</a>.', 'forumwp' ) + '</span>' : '<span class="fmwp-topic-no-replies">' + wp.i18n.__( 'No one has replied to this topic.', 'forumwp' ) + '</span>';
var fmwp_no_replies_locked_template = '<span class="fmwp-topic-no-replies">' + wp.i18n.__( 'No one has replied to this topic.', 'forumwp' ) + '</span>';

var fmwp_topic_hash = window.location.hash.substr( 1 );
var fmwp_selected_reply = false;
var fmwp_delay_reply_loading = false;

var fmwp_selected_reply_next_offset = false;

jQuery( document ).ready( function($) {

	fmwp_embed_containers.push( ".fmwp-topic-data-content" );
	fmwp_embed_containers.push( ".fmwp-reply-content" );

	if ( fmwp_topic_hash && fmwp_topic_hash.indexOf( 'reply' ) === 0 ) {
		fmwp_selected_reply = parseInt( fmwp_topic_hash.replace( 'reply-', '' ) );
		fmwp_delay_reply_loading = true;
	}

	$('.fmwp-topic-wrapper').each( function() {
		let order;
		let $visibleSorting = $(this).parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-sort:visible');
		if ( $visibleSorting.length ) {
			order = $visibleSorting.val();
		} else {
			order = $(this).data('order');
		}
		fmwp_get_replies( $(this), {
			page: 1,
			order: order
		});
	});

	var scrollHandling = {
		allow: true,
		reallow: function() {
			scrollHandling.allow = true;
		},
		delay: 400 //(milliseconds) adjust to the highest acceptable value
	};

	$( window ).on( 'scroll', function() {
		if ( ! fmwp_delay_reply_loading && ! fmwp_is_busy( 'individual_topic' ) && scrollHandling.allow ) {
			scrollHandling.allow = false;

			var load_block = $('.fmwp-topic-wrapper .fmwp-load-more');
			if ( load_block.length ) {
				setTimeout( scrollHandling.reallow, scrollHandling.delay );

				var offset = load_block.offset().top - $( window ).scrollTop();
				if ( 1000 > offset ) {
					let order;
					let $visibleSorting = load_block.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-sort:visible');
					if ( $visibleSorting.length ) {
						order = $visibleSorting.val();
					} else {
						order = $('.fmwp-topic-wrapper').data('order');
					}
					fmwp_get_replies( $('.fmwp-topic-wrapper'), {
						page: fmwp_replies_page,
						order: order
					});
				}
			}
		}
	});


	$( document.body ).on( 'change', '.fmwp-topic-sort', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var wrapper = $(this).parents('.fmwp-topic-content').find('.fmwp-topic-wrapper');
		var order = $(this).val();

		wrapper.find( '.fmwp-topic-sort' ).val(order);

		fmwp_replies_page = 1;

		fmwp_get_replies( wrapper, {
			page: 1,
			order: order
		});
	});


	$( document.body ).on( 'click', '.fmwp-show-child-replies', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = obj.closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');
		var child_wrapper = reply_row.find( '.fmwp-reply-children' );

		if ( obj.hasClass( 'fmwp-replies-loaded' ) ) {
			if ( child_wrapper.is( ':visible' ) ) {
				reply_row.find('> .fmwp-reply-child-connect').hide();
				child_wrapper.slideUp();
			} else {
				reply_row.find('> .fmwp-reply-child-connect').show();
				child_wrapper.slideDown();
			}
		} else {

			var wrapper = $(this).parents('.fmwp-topic-content').find('.fmwp-topic-wrapper');

			let order;
			let $visibleSorting = $(this).parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-sort:visible');
			if ( $visibleSorting.length ) {
				order = $visibleSorting.val();
			} else {
				order = wrapper.data('order');
			}

			var request_data = {
				reply_id:   reply_id,
				order:      order,
				nonce:      fmwp_front_data.nonce
			};
			if ( fmwp_selected_reply ) {
				request_data.search_reply = fmwp_selected_reply;
			}

			fmwp_set_busy( 'individual_topic', true );
			wp.ajax.send( 'fmwp_get_child_replies', {
				data: request_data,
				success: function( data ) {

					var template = wp.template( 'fmwp-replies-list' );
					var template_content = '';

					if ( fmwp_selected_reply ) {
						template_content = template({
							replies: data.replies,
							sub_template: true
						});
					} else {
						template_content = template({
							replies: data,
							sub_template: true
						});
					}

					child_wrapper.append( template_content );

					reply_row.find('> .fmwp-reply-child-connect').show();
					child_wrapper.slideDown();

					fmwp_embed_resize_async();

					obj.addClass('fmwp-replies-loaded');

					if ( fmwp_selected_reply && data.scroll_to ) {
						if ( jQuery( '#fmwp-reply-' +  data.scroll_to ).is(':visible') ) {
							jQuery( 'html' ).animate({
								scrollTop: jQuery( '#fmwp-reply-' +  data.scroll_to ).offset().top
							}, 1000, function() {
								if ( data.scroll_to == fmwp_selected_reply ) {
									jQuery( '#fmwp-reply-' +  data.scroll_to ).addClass('fmwp-focus-reply');
								}

								if ( data.expand_child ) {
									jQuery( '#fmwp-reply-' +  data.scroll_to ).find('.fmwp-show-child-replies').trigger('click');
								} else {
									fmwp_selected_reply = false;
									fmwp_delay_reply_loading = false;
								}
							});
						} else {
							fmwp_selected_reply = false;
							fmwp_delay_reply_loading = false;
						}
					}
					fmwp_set_busy( 'individual_topic', false );
				},
				error: function( data ) {
					console.log( data );
					$(this).fmwp_notice({
						message: data,
						type: 'error'
					});

					fmwp_set_busy( 'individual_topic', false );
				}
			});
		}
	});
});


/**
 * Function for loading topics
 *
 * @param obj
 * @param args
 */
function fmwp_get_replies( obj, args ) {
	fmwp_set_busy( 'individual_topic', true );

	var request = {
		topic_id: obj.data( 'fmwp_topic_id' ),
		page: args.page,
		order: args.order,
		nonce: fmwp_front_data.nonce
	};

	if ( fmwp_selected_reply && parseInt( args.page ) === 1 ) {
		request.reply_id = fmwp_selected_reply;
	}

	if ( fmwp_selected_reply_next_offset ) {
		request.offset = fmwp_selected_reply_next_offset;
	}

	wp.ajax.send( 'fmwp_get_replies', {
		data: request,
		success: function( data ) {
			if ( ! fmwp_selected_reply && ! data.length ) {
				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 && ! fmwp_selected_reply_next_offset ) {
					if ( obj.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-locked') || obj.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-pending') || obj.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-spam') ) {
						obj.html( fmwp_no_replies_locked_template );
					} else {
						obj.html( fmwp_no_replies_template );
					}

				} else if ( fmwp_selected_reply_next_offset ) {
					// if there will not be other content after searched reply
					// searched reply is the latest at the screen
					fmwp_selected_reply_next_offset = false;
					obj.find( '.fmwp-load-more' ).remove();
				}
			} else {
				var template = wp.template( 'fmwp-replies-list' );
				var template_content = '';
				if ( fmwp_selected_reply ) {
					template_content = template({
						replies: data.replies,
						sub_template: false
					});
				} else {
					template_content = template({
						replies: data,
						sub_template: false
					});
				}

				obj.data( 'order', args.order );

				obj.find( '.fmwp-load-more' ).remove();
				if ( args.page === 1 && ! fmwp_selected_reply_next_offset ) {
					obj.html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_replies_page = parseInt( args.page ) + 1;
				fmwp_selected_reply_next_offset = false;

				if ( fmwp_selected_reply && data.scroll_to ) {
					if ( jQuery( '#fmwp-reply-' +  data.scroll_to ).is(':visible') ) {

						fmwp_replies_page = data.next_page;
						fmwp_selected_reply_next_offset = data.next_offset;

						jQuery( 'html' ).animate({
							scrollTop: jQuery( '#fmwp-reply-' +  data.scroll_to ).offset().top
						}, 1000, function() {
							if ( data.scroll_to == fmwp_selected_reply ) {
								jQuery( '#fmwp-reply-' +  data.scroll_to ).addClass('fmwp-focus-reply');
							}

							if ( data.expand_child ) {
								if ( jQuery( '#fmwp-reply-' +  data.scroll_to ).find('.fmwp-show-child-replies').length ) {
									jQuery( '#fmwp-reply-' +  data.scroll_to ).find('.fmwp-show-child-replies').trigger('click');
								} else {
									fmwp_selected_reply = false;
									fmwp_delay_reply_loading = false;
								}

							} else {
								fmwp_selected_reply = false;
								fmwp_delay_reply_loading = false;
							}
						});
					} else {
						fmwp_selected_reply = false;
						fmwp_delay_reply_loading = false;
					}
				}
			}

			fmwp_embed_resize_async();
			fmwp_set_busy( 'individual_topic', false );
			wp.hooks.doAction( 'fmwp_replies_load_finish' );
		},
		error: function( data ) {
			console.log( data );
			jQuery(this).fmwp_notice({
				message: data,
				type: 'error'
			});
			fmwp_set_busy( 'individual_topic', false );
		}
	});
}
