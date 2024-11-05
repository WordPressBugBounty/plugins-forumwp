var fmwp_embed_selector = "iframe[src*='//player.vimeo.com'], iframe[src*='//www.youtube.com'], object, embed";
var fmwp_embed_containers = [];

jQuery( document ).ready( function($) {
	$(document.body).on( 'click', '#fmwp-popup-overlay', function() {
		$('.fmwp-popup').hide();
		$(this).hide();
	});

	$(document.body).on( 'click', '.fmwp-popup-close', function() {
		$('#fmwp-popup-overlay').hide();
		$(this).parents('.fmwp-popup').hide();
		$(this).tipsy('hide');
	});
});


function fmwp_init_tipsy() {
	if ( typeof( jQuery.fn.tipsy ) === "function" ) {
		jQuery('.fmwp-tip-n').tipsy({gravity: 'n', opacity: 1, live: 'a.live', offset: 3 });
		jQuery('.fmwp-tip-w').tipsy({gravity: 'w', opacity: 1, live: 'a.live', offset: 3 });
		jQuery('.fmwp-tip-e').tipsy({gravity: 'e', opacity: 1, live: 'a.live', offset: 3 });
		jQuery('.fmwp-tip-s').tipsy({gravity: 's', opacity: 1, live: 'a.live', offset: 3 });
	}
}


function fmwp_stringToHash( string ) {
	var hash = 0;

	if ( string.length == 0 ) {
		return hash;
	}

	for ( i = 0; i < string.length; i++ ) {
		char = string.charCodeAt( i );
		hash = ( ( hash << 5 ) - hash ) + char;
		hash = hash & hash;
	}

	return hash;
}

/**
 *
 */
function fmwp_embed_resize() {
	jQuery.each( fmwp_embed_containers, function( i ) {
		var containers = jQuery( fmwp_embed_containers[ i ] );
		if ( ! containers.is(':visible') ) {
			return;
		}

		containers.each( function() {
			var container = jQuery(this);
			var newWidth = container.width();

			var fmwp_embed_elements = container.find( fmwp_embed_selector );
			fmwp_embed_elements.each( function() {
				var $el = jQuery(this);
				$el.width( newWidth ).height( newWidth * $el.attr( 'data-fmwp-aspectratio' ) );
			});
		});
	});
}


/**
 *
 */
function fmwp_set_embed_size() {
	jQuery.each( fmwp_embed_containers, function( i ) {
		var container = jQuery( fmwp_embed_containers[ i ] );
		if ( ! container.is(':visible') ) {
			return;
		}

		var fmwp_embed_elements = container.find( fmwp_embed_selector );
		fmwp_embed_elements.each( function() {
			// jQuery .data does not work on object/embed elements
			if ( ! this.hasAttribute( 'data-fmwp-aspectratio' ) ) {
				jQuery(this).attr( 'data-fmwp-aspectratio', this.height / this.width ).removeAttr( 'height' ).removeAttr( 'width' );
			}
		});
	});
}


/**
 *
 */
function fmwp_embed_resize_async() {
	fmwp_set_embed_size();
	fmwp_embed_resize();
}


/**
 *
 * @param animation
 */
function fmwp_popup_resize( animation ) {

	var w = window.innerWidth
		|| document.documentElement.clientWidth
		|| document.body.clientWidth;

	var h = window.innerHeight
		|| document.documentElement.clientHeight
		|| document.body.clientHeight;

	var popup = jQuery('.fmwp-popup:visible');

	if ( popup.length ) {
		if ( w - 10 < popup.outerWidth() ) {
			if ( animation ) {
				popup.animate({
					'left': '5px',
					'top': ( h - popup.height() ) / 2 + 'px',
					'width' : 'calc( 100% - 10px )'
				}, 300);
			} else {
				popup.css({
					'left': '5px',
					'top': ( h - popup.height() ) / 2 + 'px',
					'width' : 'calc( 100% - 10px )'
				});
			}
		} else {
			if ( animation ) {
				popup.animate({
					'left': ( w - popup.outerWidth() ) / 2 + 'px',
					'top': ( h - popup.height() ) / 2 + 'px'
				}, 300);
			} else {
				popup.css({
					'left': ( w - popup.outerWidth() ) / 2 + 'px',
					'top': ( h - popup.height() ) / 2 + 'px'
				});
			}
		}
	}

}


//important order by ASC
var fmwp_resolutions = {
	xs: 320,
	s:  576,
	m:  768,
	l:  992,
	xl: 1024
};


/**
 *
 * @param number
 * @returns {*}
 */
function fmwp_get_size( number ) {
	for ( var key in fmwp_resolutions ) {
		if ( fmwp_resolutions.hasOwnProperty( key ) && fmwp_resolutions[ key ] === number ) {
			return key;
		}
	}

	return false;
}


/**
 *
 */
function fmwp_responsive() {

	var $resolutions = Object.values( fmwp_resolutions );
	$resolutions.sort( function(a, b){ return b-a; });

	jQuery('.fmwp').each( function() {
		var obj = jQuery(this);
		var element_width = obj.outerWidth();

		jQuery.each( $resolutions, function( index ) {
			var $class = fmwp_get_size( $resolutions[ index ] );
			obj.removeClass('fmwp-ui-' + $class );
		});

		jQuery.each( $resolutions, function( index ) {
			var $class = fmwp_get_size( $resolutions[ index ] );

			if ( element_width >= $resolutions[ index ] ) {
				obj.addClass('fmwp-ui-' + $class );
				return false;
			} else if ( $class === 'xs' && element_width <= $resolutions[ index ] ) {
				obj.addClass('fmwp-ui-' + $class );
				return false;
			}
		});

        obj.css('visibility','visible');
	});
}


/**
 * Init tags suggestions
 * @param obj
 */
function fmwp_init_tags_suggest( obj ) {
	if ( ! obj.length ) {
		return;
	}

	obj.suggest( wp.ajax.settings.url + "?action=ajax-tag-search&tax=fmwp_topic_tag", {
		multiple: true,
		multipleSep: ",",
		resultsClass: 'fmwp-ac-results',
		selectClass: 'fmwp-ac-over',
		matchClass: 'fmwp-ac-match'
	});
}


/**
 * Init tags suggestions
 * @param obj
 */
function fmwp_init_categories_suggest( obj ) {
	if ( ! obj.length ) {
		return;
	}
	obj.suggest( wp.ajax.settings.url + "?action=ajax-tag-search&tax=fmwp_forum_category", {
		multiple: true,
		multipleSep: ",",
		resultsClass: 'fmwp-ac-results',
		selectClass: 'fmwp-ac-over',
		matchClass: 'fmwp-ac-match'
	});
}


/**
 * Rebuild dropdown actions for post row in templates
 *
 * @param data
 * @param obj
 */
function fmwp_rebuild_dropdown( data, obj ) {
	var dropdown_html = '';
	jQuery.each( data.dropdown_actions, function( key ) {
		// dropdown_html += '<li><span class="' + key + '">' + data.dropdown_actions[ key ] + '</span></li>';
		dropdown_html += '<li><a href="#" class="' + key + '" data-entity_id="' + data.dropdown_actions[ key ].entity_id + '" data-nonce="' + data.dropdown_actions[ key ].nonce + '">' + data.dropdown_actions[ key ].title + '</a></li>';
	});
	obj.parents('.fmwp-dropdown ul').html( dropdown_html );
}


var fmwp_ajax_busy = {};


function fmwp_set_busy( key, value ) {
	fmwp_ajax_busy[ key ] = value;
}


function fmwp_is_busy( key ) {
	// the same as "return fmwp_ajax_busy[ key ] ? true : false;"
	return !!fmwp_ajax_busy[ key ];
}

function fmwp_change_topic_sorting_visibility( wrapper ) {
	if ( wrapper.find( '.fmwp-reply-row' ).length > 1 ) {
		wrapper.siblings('.fmwp-topic-base').find('.fmwp-topic-sort-wrapper').removeClass('fmwp-topic-hidden-sort');
	} else {
		wrapper.siblings('.fmwp-topic-base').find('.fmwp-topic-sort-wrapper').addClass('fmwp-topic-hidden-sort');
	}
}

jQuery( window ).on( 'resize', function() {
    fmwp_popup_resize( false );
    fmwp_responsive();
    fmwp_embed_resize();
});

jQuery( window ).on( 'load', function() {
    fmwp_responsive();
    fmwp_embed_resize_async();
    fmwp_init_helptips();
    fmwp_init_tipsy();
});
