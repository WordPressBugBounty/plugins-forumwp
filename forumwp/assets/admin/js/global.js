jQuery( document ).ready( function() {

	jQuery(document.body).on( 'click', '.fmwp-admin-notice.is-dismissible .notice-dismiss', function() {
		var notice_key = jQuery(this).parents('.fmwp-admin-notice').data('key');

		wp.ajax.send( 'fmwp_dismiss_notice', {
			data: {
				key: notice_key,
				nonce: fmwp_admin_data.nonce
			},
			success: function( data ) {
				return true;
			},
			error: function( data ) {
				return false;
			}
		});
	});

	jQuery(document).on('click','.fmwp-admin-notice.is-dismissible .button-secondary.fmwp_secondary_dismiss',function(e){
		e.preventDefault();
		var parent = jQuery(this).parents('.is-dismissible');
		parent.find('button.notice-dismiss').trigger('click');
	});

});
