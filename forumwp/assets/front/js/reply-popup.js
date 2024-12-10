jQuery( document ).ready( function($) {

	$( document.body ).on( 'click', '#fmwp-reply-popup-preview-action', function() {
		var editors = $(this).parents( '#fmwp-reply-popup-editors' );
		if ( editors.hasClass( 'fmwp-reply-popup-preview-hidden' ) ) {
			editors.removeClass( 'fmwp-reply-popup-preview-hidden' );
			$(this).html( $(this).data( 'hide_label' ) );

			$('#fmwpreplycontent-preview').html( $('#fmwpreplycontent').val() );
		} else {
			editors.addClass( 'fmwp-reply-popup-preview-hidden' );
			$(this).html( $(this).data( 'show_label' ) );
		}

        fmwp_resize_popup();
	});


	$( document.body ).on( 'click', '.fmwp-reply-popup-discard', function() {
		if ( fmwp_is_busy( 'reply_popup' ) ) {
			return;
		}

		var popup = $(this).parents( '#fmwp-reply-popup-wrapper' );
		popup.hide( 1, function() {
			var form = popup.find( 'form' );
			form[0].reset();

			$('#fmwpreplycontent').val('');
			$('#fmwpreplycontent-preview').html('');
			popup.removeClass( 'fmwp-fullsize' );

			var editors_button = popup.find( '#fmwp-reply-popup-preview-action' );
			editors_button.html( editors_button.data( 'hide_label' ) );
			popup.find( '#fmwp-reply-popup-editors' ).removeClass( 'fmwp-reply-popup-preview-hidden' );

			popup.trigger( 'fmwp_reply_popup_discard' );
		});
	});


	$( document.body ).on( 'click', '.fmwp-reply-popup-submit', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'reply_popup' ) ) {
			return;
		}

		var obj = $(this);

		obj.siblings('.fmwp-ajax-loading').css('visibility','visible').show();
		obj.css('visibility','hidden');

		var form = $(this).parents( 'form' );
		var serialize_data = form.serializeArray();
		var editor_value;

		if ( typeof tinymce != "undefined" ) {
			var editor = tinyMCE.get('fmwpreplycontent');
			editor_value = editor.getContent({format : 'raw'});
		} else {
			editor_value = $('#fmwpreplycontent').val();
		}

		var data = {};
		$.each( serialize_data, function( i ) {
			if ( 'fmwp-reply[content]' === serialize_data[ i ].name ) {
				data[ serialize_data[ i ].name ] = editor_value;
			} else {
				data[ serialize_data[ i ].name ] = serialize_data[ i ].value;
			}
		});
		var topic_id = data[ 'fmwp-reply[topic_id]' ];
		var reply_id = data[ 'fmwp-reply[reply_id]' ];

		var ajax_action = data['fmwp-action'] === 'edit-reply' ? 'fmwp_edit_reply' : 'fmwp_create_reply';

		form.find('input, #wp-fmwpreplycontent-wrap').removeClass('fmwp-error-field').removeAttr('title');

		fmwp_set_busy( 'reply_popup', true );
		wp.ajax.send( ajax_action, {
			data: data,
			success: function( data ) {
				if ( ajax_action === 'fmwp_edit_reply' ) {
					fmwp_edit_reply_cb( data, topic_id, reply_id );
				} else {
					fmwp_create_reply_cb( data, topic_id );
				}

				fmwp_embed_resize_async();

				obj.siblings('.fmwp-ajax-loading').css('visibility','hidden').hide();
				obj.css('visibility','visible');
			},
			error: function( data ) {
				if ( data.errors ) {
					$.each( data.errors, function(i) {
						jQuery( '#' + data.errors[i].field ).addClass('fmwp-error-field').attr('title', data.errors[i].message );
					});
				} else {
					console.log( data );
					$(this).fmwp_notice({
						message: data,
						type: 'error'
					});
				}

				fmwp_set_busy( 'reply_popup', false );

				obj.siblings('.fmwp-ajax-loading').css('visibility','hidden').hide();
				obj.css('visibility','visible');
			}
		});
	});


	function fmwp_create_reply_cb( data, topic_id ) {
		var post_template;
		var layout;
		var wrapper;
		var order;

		if ( 0 === data.post_parent ) {
			post_template = wp.template( 'fmwp-parent-reply' );
			data['sub_template'] = false;
			layout = post_template( data );

			wrapper = $('.fmwp-topic-wrapper[data-fmwp_topic_id="' + topic_id + '"]');

			let $visibleSorting = wrapper.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-sort:visible');
			if ( $visibleSorting.length ) {
				order = $visibleSorting.val();
			} else {
				order = wrapper.data('order');
			}

			if ( order === 'date_desc' ) {
				wrapper.prepend( layout );
			} else if( order === 'date_asc' ) {
				wrapper.append( layout );
			}

			if ( wrapper.find('.fmwp-topic-no-replies').length ) {
				wrapper.find('.fmwp-topic-no-replies').remove();
			}
		} else {
			post_template = wp.template( 'fmwp-parent-reply' );
			data['sub_template'] = true;
			layout = post_template( data );

			wrapper = $('.fmwp-topic-wrapper[data-fmwp_topic_id="' + topic_id + '"]');

			let $visibleSorting = wrapper.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-sort:visible');
			if ( $visibleSorting.length ) {
				order = $visibleSorting.val();
			} else {
				order = wrapper.data('order');
			}

			var parent_reply = wrapper.find( '.fmwp-reply-row[data-reply_id="' + data.post_parent + '"]' ).find( '> .fmwp-reply-children' );

			if ( order === 'date_desc' ) {
				parent_reply.prepend( layout );
			} else if( order === 'date_asc' ) {
				parent_reply.append( layout );
			}

			if ( data.is_subsub ) {
				parent_reply.siblings('.fmwp-reply-child-connect').show();

				var parent_replies_total =  wrapper.find( '.fmwp-reply-row[data-reply_id="' + data.post_parent + '"]' ).closest('.fmwp-reply-row:not(.fmwp-child-reply)').find('> .fmwp-reply-base .fmwp-replies-count');
				parent_replies_total.html( parseInt( parent_replies_total.html() ) + 1 );
			}

			//upgrade counters
			var replies_total = wrapper.find( '.fmwp-reply-row[data-reply_id="' + data.post_parent + '"] > .fmwp-reply-base' ).find( '.fmwp-replies-count' );
			replies_total.html( parseInt( replies_total.html() ) + 1 );

			var sub_replies_template = wp.template( 'fmwp-subreplies-list' );
			var sub_replies_layout = sub_replies_template( data.parent_data );

			wrapper.find( '.fmwp-reply-row[data-reply_id="' + data.post_parent + '"] > .fmwp-reply-base' ).find('.fmwp-reply-left-panel' ).html( sub_replies_layout );
		}

		fmwp_set_busy( 'reply_popup', false );

		//close popup
		$('.fmwp-reply-popup-discard:first').trigger('click');

		//upgrade total counter
		var total_wrapper = $('#fmwp-replies-total');
		total_wrapper.html( parseInt( total_wrapper.html() ) + 1 );

		/*todo separate function to calculate replies after each action*/
		fmwp_change_topic_sorting_visibility( wrapper );
	}


	function fmwp_edit_reply_cb( data, topic_id, reply_id  ) {
		var post_template;
		var layout;
		var wrapper;

		if ( 0 === data.post_parent ) {
			post_template = wp.template( 'fmwp-parent-reply' );
			data['sub_template'] = false;
			layout = post_template( data );

			wrapper = $('.fmwp-topic-wrapper[data-fmwp_topic_id="' + topic_id + '"]');

			wrapper.find( '.fmwp-reply-row[data-reply_id="' + reply_id + '"]' ).replaceWith( layout );
		} else {
			post_template = wp.template( 'fmwp-parent-reply' );
			data['sub_template'] = true;
			layout = post_template( data );

			wrapper = $('.fmwp-topic-wrapper[data-fmwp_topic_id="' + topic_id + '"]');
			var parent_reply = wrapper.find( '.fmwp-reply-row[data-reply_id="' + data.post_parent + '"]' ).find( '> .fmwp-reply-children' );

			parent_reply.find( '.fmwp-reply-row[data-reply_id="' + reply_id + '"]' ).replaceWith( layout );
		}

		fmwp_set_busy( 'reply_popup', false );

		//close popup
		$('.fmwp-reply-popup-discard:first').trigger('click');
	}


	function fmwp_build_preview( callback, ms ) {
		var timer = 0;
		return function() {
			var context = this, args = arguments;
			clearTimeout( timer );
			timer = setTimeout(function () {
				callback.apply( context, args );
			}, ms || 0);
		};
	}


	$( document.body ).on( 'keyup', '#fmwpreplycontent', fmwp_build_preview(function (e) {
		var editors = $(this).parents( '#fmwp-reply-popup-editors' );
		if ( ! editors.hasClass( 'fmwp-reply-popup-preview-hidden' ) ) {

			if ( fmwp_is_busy( 'reply_popup_preview' ) ) {
				return;
			}

			// reduce AJAX queries for getting preview
			var hash = fmwp_stringToHash( this.value );
			var data_hash = $(this).data('content-hash');
			if ( hash == data_hash ) {
				return;
			}

			$(this).data( 'content-hash', hash );

			fmwp_set_busy( 'reply_popup_preview', true );
			wp.ajax.send( 'fmwp_reply_build_preview', {
				data: {
					content: this.value,
					nonce: fmwp_front_data.nonce
				},
				success: function( data ) {
					if ( $('#fmwpreplycontent-preview').is(':visible') ) {
						$('#fmwpreplycontent-preview').html( data );
					} else {
                        $('#fmwpreplycontent-preview').html( '' );
					}

					fmwp_set_busy( 'reply_popup_preview', false );
				},
				error: function( data ) {
					console.log( data );
					$(this).fmwp_notice({
						message: data,
						type: 'error'
					});

					fmwp_set_busy( 'reply_popup_preview', false );
				}
			});

		}
	}, 1000 ));
});
