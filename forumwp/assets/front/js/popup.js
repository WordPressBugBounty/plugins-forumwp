jQuery( document ).ready( function($) {

	$( document.body ).on( 'click', '.fmwp-post-popup-action-fullsize', function() {
		var popup = $(this).parents( '.fmwp-post-popup-wrapper' );
		if ( popup.hasClass( 'fmwp-fullsize' ) ) {
			popup.removeClass( 'fmwp-fullsize' );
		} else {
			popup.addClass( 'fmwp-fullsize' );
		}

		fmwp_resize_popup();
		fmwp_responsive();
	});


	$(window).on( 'resize', function() {
		fmwp_resize_popup();
	}).on( 'load', fmwp_resize_popup() );

});

function fmwp_resize_popup() {
	var obj = [
		'reply',
		'topic'
	];

	jQuery.each( obj, function( item ) {
		if ( ! jQuery('#fmwp-' + obj[ item ] + '-popup-editor').length ) {
			return;
		}
		var height = jQuery('#fmwp-' + obj[ item ] + '-popup-editor').outerHeight();

		height = height - jQuery('#fmwp-' + obj[ item ] + '-popup-editor').find( '.mce-statusbar' ).outerHeight() - jQuery('#fmwp-' + obj[ item ] + '-popup-editor').find( '.mce-top-part' ).outerHeight() - 1;
		jQuery('#fmwp' + obj[ item ] + 'content_ifr').css( 'height', height + 'px' );
	});
}


function fmwp_extractLast( term ) {
	return term.split(" ").pop();
}

function fmwp_extract_string( term ) {
	return term.split(" ");
}


function fmwp_autocomplete_mentions() {
	var textarea_wrappers = jQuery( '#fmwp-reply-popup-editor, #fmwp-topic-popup-editor' );

	if ( textarea_wrappers.length === 0 ) {
		return;
	}

	textarea_wrappers.each( function() {
		var textarea_id = jQuery(this).data('editor-id');
		var el = jQuery( '#' + textarea_id );

		if ( el.length === 0 ) {
			return;
		}

		if ( typeof jQuery.ui === 'undefined' ) {
			return false;
		}

		var el_autocomplete = el.autocomplete({
			minLength: 1,
			appendTo: '#wp-' + textarea_id + '-editor-container',
			position: { my : "left top", at: "left bottom" },
			source: function( request, response ) {
				var last_word = fmwp_extractLast( request.term ).replace( '<p>', '' ).replace( '</p>', '' );
				if ( last_word.charAt(0) === '@' ) {

					jQuery.getJSON( wp.ajax.settings.url + '?action=fmwp_get_user_suggestions&term=' + last_word.trim() + '&_wpnonce=' + wpApiSettings.nonce, function( data ) {
						response( data );
					});

				}

			},
			select: function( event, ui ) {
				var terms = fmwp_extract_string( this.value );
				terms.pop();
				terms.push( ui.item.replace_item );
				terms.push( "" );

				var editor = tinymce.get( textarea_id );
				if ( editor && editor instanceof tinymce.Editor ) {

					editor.setContent( jQuery.trim( terms.join(" ") ) + '&nbsp;', {format: 'html'} );

					tinymce.activeEditor.focus();
					tinymce.activeEditor.selection.select( tinymce.activeEditor.getBody(), true );
					tinymce.activeEditor.selection.collapse( false );
				}

				event.preventDefault();
				event.stopPropagation();

				return false;
			}
		});

		if ( typeof el_autocomplete.data("ui-autocomplete") !== 'undefined' ) {
			el_autocomplete.data("ui-autocomplete")._renderItem = function( ul, item ) {
				return jQuery("<li />").data("item.autocomplete", item).append( item.list_item ).appendTo( ul );
			}
		}


		if ( typeof el_autocomplete.data("ui-autocomplete") !== 'undefined' ) {
			el_autocomplete.data("ui-autocomplete")._resizeMenu = function() {
				this.menu.element.outerWidth( 400 );
			}
		}

	});
}
