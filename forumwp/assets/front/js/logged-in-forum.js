jQuery( document ).ready( function($) {


	fmwp_init_tags_suggest( $('#fmwp-topic-tags') );


	$( document.body ).on( 'click', '.fmwp-edit-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var popup = $('#fmwp-topic-popup-wrapper');
		var popup_textarea = $('#fmwptopiccontent');

		var topic_id = $(this).closest('.fmwp-topic-row').data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_get_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_init_tags_suggest( $('input[name="fmwp-topic[tags]"]') );

				popup.find('input[name="fmwp-action"]').val( 'edit-topic' );
				popup.find('input[name="fmwp-topic[topic_id]"]').val( topic_id );

				popup.find('input[name="fmwp-topic[title]"]').val( data.title );
				popup.find('input[name="fmwp-topic[tags]"]').val( data.tags );

				wp.hooks.doAction( 'fmwp_on_open_edit_topic_popup', data, popup );

				if ( typeof tinymce != "undefined" ) {
					var editor = tinymce.get( 'fmwptopiccontent' );
					if( editor && editor instanceof tinymce.Editor ) {
						editor.setContent( data.orig_content, {format : 'html'} );
					}

					tinymce.execCommand('mceFocus',false,'fmwptopiccontent');
				}

				popup_textarea.val( data.orig_content ).trigger('keyup');
				$('#fmwptopiccontent-preview').html( data.content );

				popup.trigger( 'fmwp_topic_popup_loaded', {data:data} );

				popup.find('input, #wp-fmwptopiccontent-wrap').removeClass('fmwp-error-field').removeAttr('title');

				if ( popup.is(':visible') ) {
					popup_textarea.focus();
					fmwp_resize_popup();
					fmwp_autocomplete_mentions();

					$( document ).trigger( 'fmwp_edit_topic' );
				} else {
					popup.show( 1, function() {
						popup_textarea.focus();
						fmwp_resize_popup();
						fmwp_autocomplete_mentions();

						$( document ).trigger( 'fmwp_edit_topic' );
					});
				}

				popup.data( 'fmwp-target', 'forum-page' );

				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});

				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-create-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var forum_id = $(this).data('fmwp_forum_id');
		if ( ! forum_id ) {
			forum_id = $(this).parents('.fmwp-forum-wrapper').find('.fmwp-forum-head').data('fmwp_forum_id');
		}
		var popup = $('#fmwp-topic-popup-wrapper');
		var popup_textarea = $('#fmwptopiccontent');

		if ( popup.is(':visible') ) {
			popup_textarea.focus();
			return;
		}

		popup.find('input[name="fmwp-action"]').val( 'create-topic' );
		popup.find('input[name="fmwp-topic[topic_id]"]').val( '' );
		popup.find('input[name="fmwp-topic[forum_id]"]').val( forum_id );

		popup_textarea.val( '' ).trigger('keyup');

		popup.find('input, #wp-fmwptopiccontent-wrap').removeClass('fmwp-error-field').removeAttr('title');

		popup.show( 1, function() {
			popup_textarea.focus();
			fmwp_resize_popup();
			fmwp_autocomplete_mentions();

			$( document ).trigger( 'fmwp_create_topic' );
		});
	});


	$( document.body ).on( 'click', '.fmwp-report-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_report_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.addClass('fmwp-topic-reported');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-unreport-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_unreport_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.removeClass('fmwp-topic-reported');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-clear-reports-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_clear_reports_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.removeClass('fmwp-topic-reported');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-mark-spam-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_mark_spam_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('spam', true).addClass('fmwp-topic-spam');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-restore-spam-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_restore_spam_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('spam', false).removeClass('fmwp-topic-spam');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-lock-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_lock_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('locked', true).addClass('fmwp-topic-locked');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-unlock-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_unlock_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('locked', false).removeClass('fmwp-topic-locked');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-pin-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_pin_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('pinned', true).addClass('fmwp-topic-pinned');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-unpin-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_unpin_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('pinned', false).removeClass('fmwp-topic-pinned');
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-trash-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_trash_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.addClass('fmwp-topic-trashed').removeClass('fmwp-topic-pending').data('trashed', true);
				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-restore-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_restore_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.removeClass('fmwp-topic-trashed').data('trashed', false);

				if ( data.status === 'fmwp_locked' ) {
					topic_row.addClass('fmwp-topic-locked');
				} else if ( data.status === 'pending' ) {
					topic_row.addClass('fmwp-topic-pending');
				}

				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});

				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-remove-topic', function() {
		if ( fmwp_is_busy( 'individual_forum' ) ) {
			return;
		}

		if ( ! confirm( wp.i18n.__( 'Are you sure to delete permanently this topic. This operation can not be canceled.', 'forumwp' ) ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-row');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_forum', true );
		wp.ajax.send( 'fmwp_delete_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				if ( parseInt( data.statistic.topics ) === 0 ) {
					obj.parents('.fmwp-topics-wrapper').html( fmwp_no_topics_template );
				}
				topic_row.remove();

				fmwp_set_busy( 'individual_forum', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});

				fmwp_set_busy( 'individual_forum', false );
			}
		});
	});
});
