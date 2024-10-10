(function( $, undefined ) {
	var options;
	var $count = -1;
	var $step_size = 10;

	var default_options = {
		'message'   : '',
		'type'      : 'update'
	};

	var methods = {
		init : function( settings ) {
			//merge default & current options
			options = $.extend( {}, default_options, settings );

			$( this ).data( 'options', options );

			var obj = $( '<div class="fmwp-notice-message"></div>').appendTo( 'body' ).html( '<span class="fmwp-notice-message-text"></span>' +
				'<span class="fmwp-notice-message-progress"></span>' );

			obj.find( '.fmwp-notice-message-text' ).html( options.message );

			var $type = 'update';
			if ( options.type ) {
				$type = options.type;
			}

			obj.addClass( 'fmwp-notice-message-' + $type );

			obj.show();

			$count++;

			var $notice_size = obj.height() + 25;

			if ( 0 < $count ) {
				$step_size = $step_size + $notice_size;
			}


			obj.animate({
				bottom: $step_size
			}, 1000);

			setTimeout( function () {
				obj.fadeOut( 1000, function () {
					obj.remove();

					if ( 0 < $count ) {
						$step_size = $step_size - $notice_size;
					}

					$count--;
				});
			}, 3000 );

			//methods.show.apply();


		},
		show : function() {

		},
		close : function() {

		}
	};

	$.fn.fmwp_notice = function( method ) {
		if ( methods[ method ] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist for jQuery.fmwp_notice plugin' );
		}
	};

})( jQuery );