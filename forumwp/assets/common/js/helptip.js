function fmwp_init_helptips() {
	var helptips = jQuery( '.fmwp-helptip' );
	if ( helptips.length > 0 ) {
		helptips.tooltip({
			tooltipClass: "fmwp-helptip",
			content: function () {
				return jQuery( this ).attr( 'title' );
			}
		});
	}
}