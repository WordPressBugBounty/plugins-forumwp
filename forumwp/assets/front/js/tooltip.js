jQuery( document ).ready( function() {
	var offsetfromcursorY = 10;
	var body = jQuery('body');

	var hidetimeout;
	var hidetimeout2;
	var tooltip;

	body.append( '<div id="fmwp-user-card-tooltip" class="fmwp-tooltip"></div>' );

	jQuery( document.body ).on('mousemove', "[data-fmwp_tooltip]", function( eventObject ) {
		var tipobj = jQuery( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) );

		$data_tooltip = jQuery(this).attr( "data-fmwp_tooltip" );

		var winheight = jQuery(window).innerHeight() - 20;

		var coord = this.getBoundingClientRect();

		if ( ! tipobj.is(':visible') ) {
			tooltip = jQuery('<div>').append( tipobj.clone() );
			tipobj.replaceWith('<div id="fmwp_tooltip_placeholder"></div>');

			jQuery('body').append( tooltip.html() )
				.find( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) ).html( $data_tooltip );

			var css = {};

			css.left = parseInt( coord.left );

			var tooltip_height = jQuery( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) ).outerHeight()*1;

			if ( coord.y + tooltip_height + offsetfromcursorY < winheight ) {
				css.top = parseInt( coord.height ) + offsetfromcursorY + parseInt( coord.top );
				css.bottom = 'auto';
			} else {
				css.top = 'auto';
				css.bottom = parseInt( coord.height ) + offsetfromcursorY + winheight - parseInt( coord.top ) - 20;
			}

			jQuery( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) ).css( css ).show();
		}

		clearTimeout( hidetimeout );
		clearTimeout( hidetimeout2 );
	}).on('mouseout', "[data-fmwp_tooltip]", function() {
		var tipobj = jQuery( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) );

		hidetimeout = setTimeout( function() {
			tipobj.remove();
			jQuery('#fmwp_tooltip_placeholder').replaceWith( jQuery( tooltip ).html() );
		}, 100 );
	});


	jQuery( document.body ).on('mousemove', "#fmwp-user-card-tooltip", function() {
		clearTimeout( hidetimeout );
		clearTimeout( hidetimeout2 );
	}).on('mouseout', "#fmwp-user-card-tooltip", function() {
		var tipobj = jQuery( "#fmwp-user-card-tooltip" );

		hidetimeout2 = setTimeout( function() {
			tipobj.remove();
			jQuery('#fmwp_tooltip_placeholder').replaceWith( jQuery( tooltip ).html() );
		}, 100 );
	});




	/*jQuery( document.body ).on('click', "[data-fmwp_tooltip]", function( eventObject ) {
		eventObject.preventDefault();

		var tipobj = jQuery( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) );

		$data_tooltip = jQuery(this).attr( "data-fmwp_tooltip" );

		var winheight = jQuery(window).innerHeight() - 20;

		var coord = this.getBoundingClientRect();

		if ( ! tipobj.is(':visible') ) {
			tooltip = jQuery('<div>').append( tipobj.clone() );
			tipobj.replaceWith('<div id="fmwp_tooltip_placeholder"></div>');

			jQuery('body').append( tooltip.html() )
				.find( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) ).html( $data_tooltip );

			var css = {};

			css.left = parseInt( coord.left );

			var tooltip_height = jQuery( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) ).outerHeight()*1;

			if ( coord.y + tooltip_height + offsetfromcursorY < winheight ) {
				css.top = parseInt( coord.height ) + offsetfromcursorY + parseInt( coord.top );
				css.bottom = 'auto';
			} else {
				css.top = 'auto';
				css.bottom = parseInt( coord.height ) + offsetfromcursorY + winheight - parseInt( coord.top ) - 20;
			}

			jQuery( "#" + jQuery(this).data( 'fmwp_tooltip_id' ) ).css( css ).show();
		}

		clearTimeout( hidetimeout );
		clearTimeout( hidetimeout2 );
	});*/
});