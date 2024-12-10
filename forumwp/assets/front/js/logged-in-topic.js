jQuery( document ).ready( function($) {

	$( document.body ).on( 'click', '.fmwp-edit-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var popup = $('#fmwp-topic-popup-wrapper');
		var popup_textarea = $('#fmwptopiccontent');

		if ( popup.is(':visible') ) {
			popup_textarea.focus();
			return;
		}

		var topic_id = $(this).closest('.fmwp-topic-base').data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
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
				}

				popup_textarea.val( data.orig_content ).trigger('keyup');
				$('#fmwptopiccontent-preview').html( data.content );

				popup.trigger( 'fmwp_topic_popup_loaded', {data:data} );

				popup.find('input, #wp-fmwptopiccontent-wrap').removeClass('fmwp-error-field').removeAttr('title');

				popup.show( 1, function() {
					popup_textarea.focus();
					fmwp_resize_popup();
					fmwp_autocomplete_mentions();

					$( document ).trigger( 'fmwp_edit_topic' );
				});

				popup.data( 'fmwp-target', 'topic-page' );

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
	});


	$( document.body ).on( 'click', '.fmwp-write-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		if ( $(this).parents('.fmwp-reply-row').hasClass('fmwp-reply-trashed') ||
			$(this).parents('.fmwp-reply-row').hasClass('fmwp-reply-spam') ||
			$(this).parents('.fmwp-reply-row').hasClass('fmwp-reply-pending') ) {
			return;
		}

		var popup = $('#fmwp-reply-popup-wrapper');
		var popup_textarea = $('#fmwpreplycontent');

		popup.find('input[name="fmwp-action"]').val( 'create-reply' );
		popup.find('input[name="fmwp-reply[reply_id]"]').val( '' );

		var mention = $(this).data( 'mention' );
		if ( typeof mention !== 'undefined' ) {
			popup_textarea.val( mention + ' ' ).trigger('keyup');
			popup.find('input[name="fmwp-reply[parent_id]"]').val( $(this).data( 'reply_id' ) );


			if ( $('.fmwp-reply-row[data-reply_id="' + $(this).data( 'reply_id' ) + '"] .fmwp-reply-children' ).html() === '' ||
				! $('.fmwp-reply-row[data-reply_id="' + $(this).data( 'reply_id' ) + '"] .fmwp-reply-children' ).is(':visible') ) {
				$('.fmwp-reply-row[data-reply_id="' + $(this).data( 'reply_id' ) + '"] .fmwp-reply-base').find( '.fmwp-show-child-replies' ).trigger('click');
			}
		} else {
			popup_textarea.val( '' ).trigger('keyup');
			popup.find('input[name="fmwp-reply[parent_id]"]').val( '' );
		}

		popup.find('input, #wp-fmwpreplycontent-wrap').removeClass('fmwp-error-field').removeAttr('title');

		if ( popup.is(':visible') ) {
			fmwp_responsive();
			popup_textarea.focus();
			if ( typeof tinymce != "undefined" ) {
				var editor = tinymce.get( 'fmwpreplycontent' );
				if( editor && editor instanceof tinymce.Editor ) {
					editor.setContent( popup_textarea.val(), {format : 'html'} );
				}

				tinymce.execCommand('mceFocus',false,'fmwpreplycontent');
				tinymce.activeEditor.focus();
				tinymce.activeEditor.selection.select(tinymce.activeEditor.getBody(), true);
				tinymce.activeEditor.selection.collapse(false);
			}

			fmwp_resize_popup();
			fmwp_autocomplete_mentions();

			$( document ).trigger( 'fmwp_create_reply' );
		} else {
			popup.show( 1, function() {
				fmwp_responsive();
				popup_textarea.focus();
				if ( typeof tinymce != "undefined" ) {
					var editor = tinymce.get( 'fmwpreplycontent' );
					if( editor && editor instanceof tinymce.Editor ) {
						editor.setContent( popup_textarea.val(), {format : 'html'} );
					}

					tinymce.execCommand('mceFocus',false,'fmwpreplycontent');
					tinymce.activeEditor.focus();
					tinymce.activeEditor.selection.select(tinymce.activeEditor.getBody(), true);
					tinymce.activeEditor.selection.collapse(false);
				}

				fmwp_resize_popup();
				fmwp_autocomplete_mentions();

				$( document ).trigger( 'fmwp_create_reply' );
			});
		}
	});


	$( document.body ).on( 'click', '.fmwp-edit-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var popup = $('#fmwp-reply-popup-wrapper');
		var popup_textarea = $('#fmwpreplycontent');

		var reply_id = $(this).closest('.fmwp-reply-row').data('reply_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_get_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				popup.find('input[name="fmwp-action"]').val( 'edit-reply' );
				popup.find('input[name="fmwp-reply[parent_id]"]').val( data.parent_id );
				popup.find('input[name="fmwp-reply[reply_id]"]').val( reply_id );

				if ( typeof tinymce != "undefined" ) {
					var editor = tinymce.get( 'fmwpreplycontent' );
					if( editor && editor instanceof tinymce.Editor ) {
						editor.setContent( data.orig_content, {format : 'html'} );
					}
				}

				popup_textarea.val( data.orig_content ).trigger('keyup');
				$('#fmwpreplycontent-preview').html( data.content );

				popup.trigger( 'fmwp_reply_popup_loaded', {data:data} );

				popup.find('input, #wp-fmwpreplycontent-wrap').removeClass('fmwp-error-field').removeAttr('title');

				if ( popup.is(':visible') ) {
					popup_textarea.focus();
					fmwp_resize_popup();
					fmwp_autocomplete_mentions();

					$( document ).trigger( 'fmwp_edit_reply' );
				} else {
					popup.show( 1, function() {
						popup_textarea.focus();
						fmwp_resize_popup();
						fmwp_autocomplete_mentions();

						$( document ).trigger( 'fmwp_edit_reply' );
					});
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
	});


	$( document.body ).on( 'click', '.fmwp-report-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');


		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_report_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				reply_row.addClass('fmwp-reply-reported');
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
	});


	$( document.body ).on( 'click', '.fmwp-unreport-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_unreport_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				reply_row.removeClass('fmwp-reply-reported');
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
	});


	$( document.body ).on( 'click', '.fmwp-clear-reports-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_clear_reports_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				reply_row.removeClass('fmwp-reply-reported');
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
	});


	$( document.body ).on( 'click', '.fmwp-mark-spam-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_mark_spam_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				reply_row.data('spam', true).addClass('fmwp-reply-spam');
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
	});


	$( document.body ).on( 'click', '.fmwp-restore-spam-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_restore_spam_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				reply_row.data('spam', false).removeClass('fmwp-reply-spam');
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
	});


	$( document.body ).on( 'click', '.fmwp-trash-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		if ( ! confirm( wp.i18n.__( 'Are you sure to trash this reply?', 'forumwp' ) ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_trash_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				reply_row.addClass('fmwp-reply-trashed').removeClass('fmwp-reply-pending').data('trashed', true);
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
	});


	$( document.body ).on( 'click', '.fmwp-restore-reply', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		if ( ! confirm( wp.i18n.__( 'Are you sure to restore this reply?', 'forumwp' ) ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_restore_reply', {
			data: {
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				reply_row.removeClass('fmwp-reply-trashed').data('trashed', false);

				if ( data.status === 'pending' ) {
					reply_row.addClass('fmwp-reply-pending');
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
	});


	$( document.body ).on( 'click', '.fmwp-remove-reply', function() {
		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		if ( ! confirm( wp.i18n.__( 'Are you sure to delete permanently this reply. This operation can not be canceled.', 'forumwp' ) ) ) {
			return;
		}

		var obj = $(this);
		var reply_row = $(this).closest('.fmwp-reply-row');
		var reply_id = reply_row.data('reply_id');

		var is_sub = reply_row.parent().closest('.fmwp-reply-row').length;
		var is_sub_sub = reply_row.parent().closest('.fmwp-reply-row').parent().closest('.fmwp-reply-row').length;

		let order;
		let $visibleSorting = reply_row.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-sort:visible');
		if ( $visibleSorting.length ) {
			order = $visibleSorting.val();
		} else {
			order = $('.fmwp-topic-wrapper').data('order');
		}

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_delete_reply', {
			data: {
				order: order,
				reply_id: reply_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				var wrapper = reply_row.parents('.fmwp-topic-wrapper');

				if ( is_sub_sub ) {
					var parent_wrapper = reply_row.parent().closest('.fmwp-reply-row');
					var parent_parent_wrapper = parent_wrapper.parent().closest('.fmwp-reply-row');
				} else if ( is_sub ) {
					var parent_wrapper = reply_row.parent().closest('.fmwp-reply-row');
				}

				if ( data.sub_delete === 'sub_delete' ) {

					if ( parseInt( data.statistic.replies ) === 0 ) {
						obj.parents('.fmwp-topic-wrapper').html( fmwp_no_replies_template );
					}
					reply_row.remove();

				} else if ( data.sub_delete === 'change_level' ) {
					if ( is_sub_sub ) {
						reply_row.remove();
					} else if ( is_sub ) {
						var replace_content = '';
						$.each( data.child_replies, function(i) {
							var post_template = wp.template( 'fmwp-parent-reply' );
							data.child_replies[ i ]['sub_template'] = true;
							replace_content += post_template( data.child_replies[ i ] );
						});
						reply_row.replaceWith( replace_content );
					} else {
						var replace_content = '';
						$.each( data.child_replies, function(i) {
							var post_template = wp.template( 'fmwp-parent-reply' );
							data.child_replies[ i ]['sub_template'] = false;
							replace_content += post_template( data.child_replies[ i ] );
						});

						if ( parseInt( data.statistic.replies ) === 0 ) {
							obj.parents('.fmwp-topic-wrapper').html( fmwp_no_replies_template );
						}
						reply_row.replaceWith( replace_content );
					}

					fmwp_embed_resize_async();
				}

				// change the count of the parent reply's replies
				if ( is_sub_sub ) {
					var parent_replies_layout = wp.template( 'fmwp-subreplies-list' )( data.parent_data );
					var parent_parent_replies_layout = wp.template( 'fmwp-subreplies-list' )( data.parent_parent_data );

					parent_wrapper.find('> .fmwp-reply-base .fmwp-reply-avatars').replaceWith( parent_replies_layout );
					parent_parent_wrapper.find('> .fmwp-reply-base .fmwp-reply-avatars').replaceWith( parent_parent_replies_layout );

					if ( data.parent_data.total_replies == 0 ) {
						parent_wrapper.find('> .fmwp-reply-child-connect').hide();
					}
				} else if ( is_sub ) {
					var parent_replies_layout = wp.template( 'fmwp-subreplies-list' )( data.parent_data );

					parent_wrapper.find('> .fmwp-reply-base .fmwp-reply-avatars').replaceWith( parent_replies_layout );
				}

				//change total replies count
				$('#fmwp-replies-total').html( data.statistic.replies );

				fmwp_change_topic_sorting_visibility( wrapper );

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
	});


	$( document.body ).on( 'click', '.fmwp-report-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_report_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.parents('.fmwp-topic-main-wrapper').addClass('fmwp-topic-reported');
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
	});


	$( document.body ).on( 'click', '.fmwp-unreport-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_unreport_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.parents('.fmwp-topic-main-wrapper').removeClass('fmwp-topic-reported');
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
	});


	$( document.body ).on( 'click', '.fmwp-clear-reports-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_clear_reports_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.parents('.fmwp-topic-main-wrapper').removeClass('fmwp-topic-reported');
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
	});


	$( document.body ).on( 'click', '.fmwp-mark-spam-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_mark_spam_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('spam', true);
				topic_row.parents('.fmwp-topic-main-wrapper').addClass('fmwp-topic-spam');

				if ( topic_row.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-no-replies').length ) {
					if ( topic_row.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-locked') || topic_row.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-pending') || topic_row.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-spam') ) {
						topic_row.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-wrapper').html( fmwp_no_replies_locked_template );
					} else {
						topic_row.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-wrapper').html( fmwp_no_replies_template );
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
	});


	$( document.body ).on( 'click', '.fmwp-restore-spam-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_restore_spam_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('spam', false);
				topic_row.parents('.fmwp-topic-main-wrapper').removeClass('fmwp-topic-spam');

				if ( topic_row.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-no-replies').length ) {
					if ( topic_row.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-locked') || topic_row.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-pending') || topic_row.parents('.fmwp-topic-main-wrapper').hasClass('fmwp-topic-spam') ) {
						topic_row.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-wrapper').html( fmwp_no_replies_locked_template );
					} else {
						topic_row.parents('.fmwp-topic-main-wrapper').find('.fmwp-topic-wrapper').html( fmwp_no_replies_template );
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
	});


	$( document.body ).on( 'click', '.fmwp-lock-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_lock_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('locked', true);
				topic_row.parents('.fmwp-topic-main-wrapper').addClass('fmwp-topic-locked');

				fmwp_set_busy( 'individual_topic', false );

				window.location.reload( true );
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
	});


	$( document.body ).on( 'click', '.fmwp-unlock-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_unlock_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('locked', false);
				topic_row.parents('.fmwp-topic-main-wrapper').removeClass('fmwp-topic-locked');

				fmwp_set_busy( 'individual_topic', false );

				window.location.reload( true );
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
	});


	$( document.body ).on( 'click', '.fmwp-pin-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_pin_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('pinned', true);
				topic_row.parents('.fmwp-topic-main-wrapper').addClass('fmwp-topic-pinned');

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
	});


	$( document.body ).on( 'click', '.fmwp-unpin-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_unpin_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('pinned', false);
				topic_row.parents('.fmwp-topic-main-wrapper').removeClass('fmwp-topic-pinned');
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
	});


	$( document.body ).on( 'click', '.fmwp-trash-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_trash_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );

				topic_row.data('trashed', true);
				topic_row.parents('.fmwp-topic-main-wrapper').addClass('fmwp-topic-trashed');
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
	});


	$( document.body ).on( 'click', '.fmwp-restore-topic', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_restore_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				topic_row.data('trashed', false);
				topic_row.parents('.fmwp-topic-main-wrapper').removeClass('fmwp-topic-trashed');
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
	});


	$( document.body ).on( 'click', '.fmwp-remove-topic', function() {
		if ( fmwp_is_busy( 'individual_topic' ) ) {
			return;
		}

		if ( ! confirm( wp.i18n.__( 'Are you sure to delete permanently this topic. This operation can not be canceled.', 'forumwp' ) ) ) {
			return;
		}

		var obj = $(this);
		var topic_row = $(this).closest('.fmwp-topic-base');
		var topic_id = topic_row.data('topic_id');

		fmwp_set_busy( 'individual_topic', true );
		wp.ajax.send( 'fmwp_delete_topic', {
			data: {
				topic_id: topic_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				$(this).fmwp_notice({
					message: data.message,
					type: 'update'
				});

				setTimeout(function () {
					window.location = data.redirect;
				}, 3000 );

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
	});
});
