jQuery( document ).ready( function($) {
	$( document.body ).on( 'click', '.fmwp-lock-forum', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'forums_list' ) ) {
			return;
		}

		var obj = $(this);
		var forum_row = $(this).closest('.fmwp-forum-row');
		var forum_id = forum_row.data('forum_id');

		fmwp_set_busy( 'forums_list', true );
		wp.ajax.send( 'fmwp_lock_forum', {
			data: {
				forum_id: forum_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				forum_row.data('locked', true).addClass('fmwp-forum-locked');
				fmwp_set_busy( 'forums_list', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'forums_list', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-unlock-forum', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'forums_list' ) ) {
			return;
		}

		var obj = $(this);
		var forum_row = $(this).closest('.fmwp-forum-row');
		var forum_id = forum_row.data('forum_id');

		fmwp_set_busy( 'forums_list', true );
		wp.ajax.send( 'fmwp_unlock_forum', {
			data: {
				forum_id: forum_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );
				forum_row.data('locked', false).removeClass('fmwp-forum-locked');
				fmwp_set_busy( 'forums_list', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'forums_list', false );
			}
		});
	});


	$( document.body ).on( 'click', '.fmwp-trash-forum', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'forums_list' ) ) {
			return;
		}

		var obj = $(this);
		var forum_row = $(this).closest('.fmwp-forum-row');
		var forum_id = forum_row.data('forum_id');

		fmwp_set_busy( 'forums_list', true );
		wp.ajax.send( 'fmwp_trash_forum', {
			data: {
				forum_id: forum_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				//forum_row.remove();
				fmwp_rebuild_dropdown( data, obj );

				forum_row.addClass('fmwp-forum-trashed').data('trashed', true);
				fmwp_set_busy( 'forums_list', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'forums_list', false );
			}
		});
	});

	$( document.body ).on( 'click', '.fmwp-restore-forum', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'forums_list' ) ) {
			return;
		}

		var obj = $(this);
		var forum_row = $(this).closest('.fmwp-forum-row');
		var forum_id = forum_row.data('forum_id');

		fmwp_set_busy( 'forums_list', true );
		wp.ajax.send( 'fmwp_restore_forum', {
			data: {
				forum_id: forum_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				fmwp_rebuild_dropdown( data, obj );

				forum_row.removeClass('fmwp-forum-trashed').data('trashed', false);
				fmwp_set_busy( 'forums_list', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'forums_list', false );
			}
		});
	});

	$( document.body ).on( 'click', '.fmwp-remove-forum', function(e) {
		e.preventDefault();

		if ( fmwp_is_busy( 'forums_list' ) ) {
			return;
		}

		if ( ! confirm( wp.i18n.__( 'Are you sure to delete permanently this forum. This operation can not be canceled.', 'forumwp' ) ) ) {
			return;
		}

		var obj = $(this);
		var forum_row = $(this).closest('.fmwp-forum-row');
		var forum_id = forum_row.data('forum_id');

		fmwp_set_busy( 'forums_list', true );
		wp.ajax.send( 'fmwp_remove_forum', {
			data: {
				forum_id: forum_id,
				nonce: fmwp_front_data.nonce
			},
			success: function( data ) {
				forum_row.remove();
				fmwp_set_busy( 'forums_list', false );
			},
			error: function( data ) {
				console.log( data );
				$(this).fmwp_notice({
					message: data,
					type: 'error'
				});
				fmwp_set_busy( 'forums_list', false );
			}
		});
	});
});
