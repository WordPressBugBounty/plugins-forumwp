jQuery( document ).ready( function($) {

	$( document.body ).on( 'click', '.fmwp-login-to-action', function(e) {
		e.preventDefault();
		$(this).tipsy('hide');
		$('#fmwp-popup-overlay').show();
		var header = $('#fmwp-login-popup-wrapper .fmwp-popup-header');

		var text;
		if ( $(this).data('fmwp_popup_title') ) {
			text = $(this).data('fmwp_popup_title')
		} else {
			text = header.data('default');
		}
		header.html( text );

		$('#fmwp-login-popup-wrapper').show();

		fmwp_popup_resize();
	});

});
