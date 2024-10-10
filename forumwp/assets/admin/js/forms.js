jQuery( document ).ready( function() {
	jQuery( document.body ).on( 'click', '.editor-post-trash', function(e) {
        jQuery('#fmwp_metadata_status_changed').val('');
	});

    jQuery( document.body ).on( 'click', '.editor-post-visibility__dialog-radio[value="private"]', function(e) {
        jQuery('#fmwp_metadata_status_changed').val('');
    });

	jQuery( document.body ).on( 'change', '#fmwp_metadata_post_status', function(e) {
		if ( jQuery('#fmwp_metadata_status_changed').data('post-status') != jQuery(this).val() ) {
			jQuery('#fmwp_metadata_status_changed').val('1');
		} else {
			jQuery('#fmwp_metadata_status_changed').val('');
		}
	});

	jQuery('#fmwp_metadata_post_status').trigger('change');


	fmwp_init_helptips();

	/**
	 * Media uploader
	 */
	jQuery( '.fmwp-media-upload' ).each( function() {
		var field = jQuery(this).find( '.fmwp-forms-field' );
		var default_value = field.data('default');

		if ( field.val() != '' && field.val() != default_value ) {
			field.siblings('.fmwp-set-image').hide();
			field.siblings('.fmwp-clear-image').show();
			field.siblings('.icon_preview').show();
		} else {
			if ( field.val() == default_value ) {
				field.siblings('.icon_preview').show();
			}
			field.siblings('.fmwp-set-image').show();
			field.siblings('.fmwp-clear-image').hide();
		}
	});


	if ( typeof wp !== 'undefined' && wp.media && wp.media.editor ) {
		var frame;

		jQuery( document.body ).on( 'click', '.fmwp-set-image', function(e) {
			var button = jQuery(this);

			e.preventDefault();

			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.remove();
				/*frame.open();
				 return;*/
			}

			// Create a new media frame
			frame = wp.media({
				title: button.data('upload_frame'),
				button: {
					text: php_data.texts.select
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected in the media frame...
			frame.on( 'select', function() {
				// Get media attachment details from the frame state
				var attachment = frame.state().get('selection').first().toJSON();

				// Send the attachment URL to our custom image input field.
				button.siblings('.icon_preview').attr( 'src', attachment.url ).show();

				button.siblings('.fmwp-forms-field').val( attachment.url );
				button.siblings('.fmwp-media-upload-data-id').val(attachment.id);
				button.siblings('.fmwp-media-upload-data-width').val(attachment.width);
				button.siblings('.fmwp-media-upload-data-height').val(attachment.height);
				button.siblings('.fmwp-media-upload-data-thumbnail').val(attachment.thumbnail);
				button.siblings('.fmwp-media-upload-data-url').trigger('change');
				button.siblings('.fmwp-media-upload-url').val(attachment.url);

				button.siblings('.fmwp-clear-image').show();
				button.hide();

				jQuery( document ).trigger( 'fmwp_media_upload_select', [button, attachment] );
			});

			frame.open();
		});

		jQuery( document.body ).on( 'click', '.icon_preview', function(e) {
			jQuery(this).siblings('.fmwp-set-image').trigger('click');
		});

		jQuery( document.body ).on( 'click', '.fmwp-clear-image', function() {
			var clear_button = jQuery(this);
			var default_image_url = clear_button.siblings('.fmwp-forms-field').data('default');
			clear_button.siblings('.fmwp-set-image').show();
			clear_button.hide();
			clear_button.siblings('.icon_preview').attr( 'src', default_image_url );
			clear_button.siblings('.fmwp-media-upload-data-id').val('');
			clear_button.siblings('.fmwp-media-upload-data-width').val('');
			clear_button.siblings('.fmwp-media-upload-data-height').val('');
			clear_button.siblings('.fmwp-media-upload-data-thumbnail').val('');
			clear_button.siblings('.fmwp-forms-field').val( default_image_url );
			clear_button.siblings('.fmwp-media-upload-data-url').trigger('change');
			clear_button.siblings('.fmwp-media-upload-url').val( default_image_url );

			jQuery( document ).trigger( 'fmwp_media_upload_clear', clear_button );
		});
	}


	/**
	 * On option fields change
	 */
	jQuery( document.body ).on( 'change', '.fmwp-forms-field', function() {
		if ( jQuery('.fmwp-forms-line[data-conditional*=\'"' + jQuery(this).data('field_id') + '",\']').length > 0 ) {
			run_check_conditions();
		}
	});


	//first load hide unconditional fields
	run_check_conditions();


	/**
	 * Run conditional logic
	 */
	function run_check_conditions() {
		jQuery( '.fmwp-forms-line' ).removeClass('fmwp-forms-line-conditioned').each( function() {
			if ( typeof jQuery(this).data('conditional') === 'undefined' || jQuery(this).hasClass('fmwp-forms-line-conditioned') )
				return;

			if ( check_condition( jQuery(this) ) ) {
				jQuery(this).show();
			} else {
				jQuery(this).hide();
			}
		});
	}


	/**
	 * Conditional logic
	 *
	 * true - show field
	 * false - hide field
	 *
	 * @returns {boolean}
	 */
	function check_condition( form_line ) {

		form_line.addClass( 'fmwp-forms-line-conditioned' );

		var conditional = form_line.data('conditional');
		var condition = conditional[1];
		var value = conditional[2];

		var prefix = form_line.data( 'prefix' );

		var condition_field = jQuery( '#' + prefix + '_' + conditional[0] );
		var parent_condition = true;
		if ( typeof condition_field.parents('.fmwp-forms-line').data('conditional') !== 'undefined' ) {
			parent_condition = check_condition( condition_field.parents('.fmwp-forms-line') );
		}

		var own_condition = false;
		if ( condition == '=' ) {
			var tagName = condition_field.prop("tagName").toLowerCase();

			if ( tagName == 'input' ) {
				var input_type = condition_field.attr('type');
				if ( input_type == 'checkbox' ) {
					own_condition = ( value == '1' ) ? condition_field.is(':checked') : ! condition_field.is(':checked');
				} else {
					own_condition = ( condition_field.val() == value );
				}
			} else if ( tagName == 'select' ) {
				own_condition = ( condition_field.val() == value );
			}
		} else if ( condition == '!=' ) {
			var tagName = condition_field.prop("tagName").toLowerCase();

			if ( tagName == 'input' ) {
				var input_type = condition_field.attr('type');
				if ( input_type == 'checkbox' ) {
					own_condition = ( value == '1' ) ? ! condition_field.is(':checked') : condition_field.is(':checked');
				} else {
					own_condition = ( condition_field.val() != value );
				}
			} else if ( tagName == 'select' ) {
				own_condition = ( condition_field.val() != value );
			}
		}

		return ( own_condition && parent_condition );
	}

	if ( jQuery('.fmwp-icon-select-field').length ) {

		function iformat( icon ) {
			var originalOption = icon.element;
			return jQuery('<span><i class="' + jQuery(originalOption).data('icon') + '"></i> ' + icon.text + '</span>');
		}

		wp.ajax.send( 'fmwp_get_icons', {
			data: {
				nonce: fmwp_admin_data.nonce
			},
			success: function( data ) {
				var options = '';
				jQuery.each( data, function( i ) {
					jQuery.each( data[ i ].styles, function( is ) {
						var style_class;
						if ( data[ i ].styles[ is ] === 'solid' ) {
							style_class = 'fas fa-';
						} else if ( data[ i ].styles[ is ] === 'regular' ) {
							style_class = 'far fa-';
						} else if ( data[ i ].styles[ is ] === 'brands' ) {
							style_class = 'fab fa-';
						}
						options += '<option data-icon="' +  style_class + i + '" value="' + style_class + i + '">' + data[ i ].label + '</option>';
					});
				});

				jQuery('.fmwp-icon-select-field').each( function() {
					var selected = jQuery(this).data('value');
					jQuery(this).html( options ).val( selected );

					jQuery('.fmwp-icon-select-field').select2({
						width: "100%",
						theme: "classic",
						allowHtml: true,
						templateSelection: iformat,
						templateResult: iformat,
						dropdownCssClass: 'fmwp',
					});
				});
			},
			error: function( data ) {

			}
		});
	}
});
