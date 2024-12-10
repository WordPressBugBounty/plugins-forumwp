var fmwp_profile = {
	topics:{
		page: 2,
		loading: false
	},
	replies:{
		page: 2,
		loading: false
	}
};

jQuery( document ).ready( function($) {
	fmwp_profile = wp.hooks.applyFilters( 'fmwp_profile_tabs', fmwp_profile );

	fmwp_embed_containers.push( ".fmwp-reply-content" );

	$( window ).on( 'load', function() {
		//set mobile indicator position
		$( '.fmwp-profile-wrapper' ).each( function() {
			var self = $(this).find( '.fmwp-profile-mobile .fmwp-profile-menu > .fmwp-active-tab' );
			var active_tab = self.find('a').data('tab');

			self.siblings('.fmwp-profile-menu-indicator').animate({
				left: self.position().left,
				width: self.outerWidth() + 'px'
			});

			if ( self.outerWidth() + self.offset().left >= self.parents('ul').outerWidth() ) {
				var leftOffset = self.outerWidth() - ( self.parents('nav').outerWidth() - self.offset().left - self.parents( 'nav' ).offset().left ) + self.parents( 'nav' ).scrollLeft();
				self.parents( 'nav' ).animate({ scrollLeft: leftOffset });
			}

			$(this).find( '.fmwp-profile-scroll-content .fmwp-profile-tab-content[data-tab="' + active_tab + '"]' );

			var position = $(this).find( '.fmwp-profile-scroll-content .fmwp-profile-tab-content[data-tab="' + active_tab + '"]' ).offset().left - $(this).find( '.fmwp-profile-scroll-content' ).offset().left + $(this).find( '.fmwp-profile-scroll-content' ).scrollLeft();
			$( '.fmwp-profile-scroll-content' ).scrollLeft( position );
		});
	});

	var first_tab = $('.fmwp-profile-menu').find('li.fmwp-active-tab > a').data('tab');
	var sub_tab = $('.fmwp-profile-tab-content[data-tab="' + first_tab + '"]').find('li.fmwp-active-tab > a').data('tab');
	var user_id = $('.fmwp-profile-wrapper').data('user_id');

	//first page load
	if ( first_tab === 'topics' ) {
		fmwp_profile_topics( $('.fmwp-profile-' + first_tab + '-content'), {
			page: 1,
			user_id: user_id
		});
	} else if ( first_tab === 'replies' ) {
		fmwp_profile_replies( $('.fmwp-profile-' + first_tab + '-content'), {
			page: 1,
			user_id: user_id
		});
	} else {
		wp.hooks.doAction( 'fmwp_user_profile_tab_loading', first_tab, sub_tab, user_id );
	}

	$( window ).on( 'scroll', function() {

		var scrollHandling = {
			allow: true,
			reallow: function() {
				scrollHandling.allow = true;
			},
			delay: 400 //(milliseconds) adjust to the highest acceptable value
		};

		if ( $('.fmwp-profile-topics-content:visible').length ) {
			if( ! fmwp_profile.topics.loading && scrollHandling.allow ) {
				scrollHandling.allow = false;

				var load_block = $('.fmwp-profile-topics-content .fmwp-load-more');
				if ( load_block.length ) {
					setTimeout( scrollHandling.reallow, scrollHandling.delay );

					var offset = load_block.offset().top - $( window ).scrollTop();
					if ( 1000 > offset ) {
						fmwp_profile_topics( $('.fmwp-profile-topics-content'), {
							page: fmwp_profile.topics.page,
							user_id: load_block.parents('.fmwp-profile-wrapper').data('user_id')
						});
					}
				}
			}
		} else if ( $('.fmwp-profile-replies-content:visible').length ) {
			if( ! fmwp_profile.replies.loading && scrollHandling.allow ) {
				scrollHandling.allow = false;

				var load_block = $('.fmwp-profile-replies-content .fmwp-load-more');
				if ( load_block.length ) {
					setTimeout( scrollHandling.reallow, scrollHandling.delay );

					var offset = load_block.offset().top - $( window ).scrollTop();
					if ( 1000 > offset ) {
						fmwp_profile_replies( $('.fmwp-profile-replies-content'), {
							page: fmwp_profile.replies.page,
							user_id: load_block.parents('.fmwp-profile-wrapper').data('user_id')
						});
					}
				}
			}
		} else {
			wp.hooks.doAction( 'fmwp_user_profile_tab_scroll', scrollHandling );
		}
	});


	$( document.body ).on( 'click', '.fmwp-profile-load-content-link', function(e) {
		e.preventDefault();

		if ( $(this).parents( 'li' ).hasClass( 'fmwp-active-tab' ) ) {
			return;
		}

		window.history.pushState("string", "fmwp-profile-tab",  $(this).attr('href') );

		var profile_wrapper = $(this).parents('.fmwp-profile-wrapper');
		var user_id = profile_wrapper.data('user_id');
		var active_tab;

		var is_mobile = false;
		if ( $(this).parents( '.fmwp.fmwp-ui-xs' ).length ) {
			is_mobile = true;
		}

		var tabs_list = $(this).parents('ul');

		tabs_list.find( 'li' ).removeClass('fmwp-active-tab');
		$(this).parents( 'li' ).addClass('fmwp-active-tab');

		var is_tab = ! $(this).parents( '.fmwp-profile-submenu' ).length;

		if ( is_tab ) {
			profile_wrapper.find( '.fmwp-profile-content' ).attr( 'data-active_tab', $(this).data('tab') );
		} else {
			$(this).parents( '.fmwp-profile-tab-content' ).attr( 'data-active_subtab', $(this).data('tab') );
		}


		if ( is_tab ) {

			var is_ajax = $(this).data( 'ajax' );

			if ( ! is_ajax ) {
				if ( $(this).data('tab') === 'topics' ) {
					fmwp_profile_topics( $('.fmwp-profile-' + $(this).data('tab') + '-content'), {
						page: 1,
						user_id: user_id
					});
				} else if ( $(this).data('tab') === 'replies' ) {
					fmwp_profile_replies( $('.fmwp-profile-' + $(this).data('tab') + '-content'), {
						page: 1,
						user_id: user_id
					});
				} else {
					wp.hooks.doAction( 'fmwp_user_profile_not_ajax_tab_loaded', $(this).data('tab') );
				}

			} else {
				active_tab = $(this).data('tab');
				insert_to = profile_wrapper.find( '.fmwp-profile-content' );

				if ( ! insert_to.find( '.fmwp-profile-' + active_tab + '-content' ).length ) {

					fmwp_profile_show_loader( insert_to );

					wp.ajax.send( 'fmwp_profile_get_content', {
						data: {
							tab: active_tab,
							user_id: user_id,
							nonce: fmwp_front_data.nonce
						},
						success: function( data ) {
							var template = wp.template( 'fmwp-profile-' + active_tab );
							var template_content = template( data );

							insert_to.append(
								'<div class="fmwp-profile-tab-content fmwp-profile-' + active_tab + '-content">' +
									template_content +
								'</div>'
							);

							insert_to.find('.fmwp-profile-' + active_tab + '-content').show();

							insert_to.find('[data-checked="checked"]').prop('checked', true).attr('checked', 'checked');

                            fmwp_responsive();

							fmwp_profile_hide_loader( insert_to );

							insert_to.trigger( 'fmwp_profile_tab_loaded', {tab:active_tab, user_id: user_id} );
						},
						error: function( data ) {
							console.log( data );
							jQuery(this).fmwp_notice({
								message: data,
								type: 'error'
							});

							fmwp_profile_hide_loader( insert_to );
						}
					});
				}
			}

		} else {
			active_tab = profile_wrapper.find('.fmwp-profile-menu:visible .fmwp-active-tab a').data('tab');

			wp.hooks.doAction( 'fmwp_user_profile_subtab_loaded', active_tab, $(this).data('tab'), user_id );
		}
	});


	/**
	 * Mobile
	 *
	 */
	$( document.body ).on( 'click', '.fmwp-profile-mobile-tab-link', function(e) {
		e.preventDefault();

		if ( $(this).parents( 'li' ).hasClass( 'fmwp-active-tab' ) ) {
			return;
		}

		window.history.pushState("string", "fmwp-profile-tab",  $(this).attr('href') );

		var profile_wrapper = $(this).parents('.fmwp-profile-wrapper');
		var user_id = profile_wrapper.data('user_id');
		var active_tab = $(this).data('tab');

		var tabs_list = $(this).parents('ul');


		tabs_list.find( 'li' ).removeClass('fmwp-active-tab');
		$(this).parents( 'li' ).addClass('fmwp-active-tab');

		profile_wrapper.find( '.fmwp-profile-content' ).attr( 'data-active_tab', $(this).data('tab') );

		var self = $(this).parents( 'li' );
		var leftOffset;
		self.siblings('.fmwp-profile-menu-indicator').animate({ left: self.position().left, width: self.outerWidth() + 'px' });
		if ( self.outerWidth() + self.offset().left >= self.parents('ul').outerWidth() ) {
			leftOffset = self.outerWidth() - ( self.parents('nav').outerWidth() - self.offset().left - $(this).parents( 'nav' ).offset().left ) + $(this).parents( 'nav' ).scrollLeft();
			$(this).parents( 'nav' ).animate({ scrollLeft: leftOffset });
		} else if ( self.offset().left < 0 ) {
			leftOffset = ( self.offset().left - $(this).parents( 'nav' ).offset().left ) + $(this).parents( 'nav' ).scrollLeft() - ( self.parents( 'nav' ).outerWidth() - self.parents( 'nav' ).width() );
			$(this).parents( 'nav' ).animate({ scrollLeft: leftOffset });
		}

		//scroll data tabs
		/*var position = $('.fmwp-profile-tab-content[data-tab="' + active_tab + '"]').offset().left - $( '.fmwp-profile-scroll-content' ).offset().left + $( '.fmwp-profile-scroll-content' ).scrollLeft();
		$( '.fmwp-profile-scroll-content' ).animate({ scrollLeft: position } );
*/
		var is_ajax = $(this).data( 'ajax' );

		if ( ! is_ajax ) {

			//scroll data tabs
			var position = $('.fmwp-profile-tab-content[data-tab="' + active_tab + '"]').offset().left - $( '.fmwp-profile-scroll-content' ).offset().left + $( '.fmwp-profile-scroll-content' ).scrollLeft();
			$( '.fmwp-profile-scroll-content' ).animate({ scrollLeft: position } );


			if ( $(this).data('tab') === 'topics' ) {
				fmwp_profile_topics( $('.fmwp-profile-' + $(this).data('tab') + '-content'), {
					page: 1,
					user_id: user_id
				});
			} else if ( $(this).data('tab') === 'replies' ) {
				fmwp_profile_replies( $('.fmwp-profile-' + $(this).data('tab') + '-content'), {
					page: 1,
					user_id: user_id
				});
			} else {
				wp.hooks.doAction( 'fmwp_user_profile_not_ajax_tab_loaded_mobile', $(this).data('tab') );
			}

		} else {
			var insert_to = profile_wrapper.find( '.fmwp-profile-scroll-content' );

			var position = insert_to.find('.fmwp-profile-tab-content[data-tab="' + active_tab + '"]').offset().left - $( '.fmwp-profile-scroll-content' ).offset().left + $( '.fmwp-profile-scroll-content' ).scrollLeft();
			$( '.fmwp-profile-scroll-content' ).animate({ scrollLeft: position } );

			if ( insert_to.find( '.fmwp-profile-' + active_tab + '-content' ).hasClass('fmwp-profile-blank-content') ) {
				wp.ajax.send( 'fmwp_profile_get_content', {
					data: {
						tab: active_tab,
						user_id: user_id,
						nonce: fmwp_front_data.nonce
					},
					success: function( data ) {
						var template = wp.template( 'fmwp-profile-' + active_tab );
						var template_content = template( data );

						insert_to.find( '.fmwp-profile-' + active_tab + '-content' ).removeClass( 'fmwp-profile-blank-content' ).html( template_content );

						/*insert_to.append(
							'<div class="fmwp-profile-tab-content fmwp-profile-' + active_tab + '-content" data-ajax="1" data-tab="' + active_tab + '">' +
							template_content +
							'</div>'
						);*/

						//scroll data tabs

						insert_to.find('[data-checked="checked"]').prop('checked', true).attr('checked', 'checked');

                        fmwp_responsive();

						fmwp_profile_hide_loader( insert_to );
					},
					error: function( data ) {
						console.log( data );
						jQuery(this).fmwp_notice({
							message: data,
							type: 'error'
						});
					}
				});
			}
		}
	});
});


/**
 *
 * @param wrapper
 */
function fmwp_profile_show_loader( wrapper ) {
	wrapper.find( '> .fmwp-ajax-loading' ).show();
}


/**
 *
 * @param wrapper
 */
function fmwp_profile_hide_loader( wrapper ) {
	wrapper.find( ' > .fmwp-ajax-loading' ).hide();
}


/**
 * Function for loading replies
 *
 * @param obj
 * @param args
 */
function fmwp_profile_replies( obj, args ) {
	obj.find( '.fmwp-ajax-loading' ).show();
	fmwp_profile.replies.loading = true;

	if ( args.page === 1 ) {
		obj.find('.fmwp-replies-wrapper').html('');
	}

	wp.ajax.send( 'fmwp_profile_replies', {
		data: {
			user_id: args.user_id,
			page: args.page,
			nonce: fmwp_front_data.nonce
		},
		success: function( data ) {
			obj.find( '.fmwp-ajax-loading' ).hide();
			obj.find( '.fmwp-load-more' ).remove();

			if ( ! data.length ) {
				if ( args.page === 1 ) {
					obj.html( '<span class="fmwp-profile-no-replies">' + wp.i18n.__( 'No replies', 'forumwp' ) + '</span>' );
				}
			} else {
				var template = wp.template( 'fmwp-replies-list' );
				var template_content = template({
					replies: data
				});

				if ( args.page === 1 ) {
					obj.find('.fmwp-replies-wrapper').html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.find('.fmwp-replies-wrapper').append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_embed_resize_async();

				fmwp_profile.replies.page = parseInt( args.page ) + 1;
			}
			fmwp_profile.replies.loading = false;

			fmwp_responsive();
			wp.hooks.doAction( 'fmwp_profile_load_finish' );
		},
		error: function( data ) {
			console.log( data );
			obj.find( '.fmwp-ajax-loading' ).hide();
			jQuery(this).fmwp_notice({
				message: data,
				type: 'error'
			});
			fmwp_profile.replies.loading = false;
		}
	});
}


/**
 * Function for loading topics
 *
 * @param obj
 * @param args
 */
function fmwp_profile_topics( obj, args ) {
	fmwp_profile.topics.loading = true;
	obj.find( '.fmwp-ajax-loading' ).show();

	if ( args.page === 1 ) {
		obj.find('.fmwp-topics-wrapper').html('');
	}

	wp.ajax.send( 'fmwp_profile_topics', {
		data: {
			user_id: args.user_id,
			page: args.page,
			nonce: fmwp_front_data.nonce
		},
		success: function( data ) {
			obj.find( '.fmwp-ajax-loading' ).hide();
			obj.find( '.fmwp-load-more' ).remove();

			if ( ! data.length ) {
				if ( args.page === 1 ) {
					obj.find('.fmwp-topics-wrapper').html( '<span class="fmwp-profile-no-topics">' + wp.i18n.__( 'No topics', 'forumwp' ) + '</span>' );
				}
			} else {
				var template = wp.template( 'fmwp-topics-list' );
				var template_content = template({
					topics: data
				});

				if ( args.page === 1 ) {
					obj.find('.fmwp-topics-wrapper').html( template_content + '<span class="fmwp-load-more"></span>' );
				} else {
					obj.find('.fmwp-topics-wrapper').append( template_content + '<span class="fmwp-load-more"></span>' );
				}

				fmwp_embed_resize_async();

				fmwp_profile.topics.page = parseInt( args.page ) + 1;
			}

			fmwp_profile.topics.loading = false;

			fmwp_responsive();
			wp.hooks.doAction( 'fmwp_profile_load_finish' );
		},
		error: function( data ) {
			console.log( data );
			obj.find( '.fmwp-ajax-loading' ).hide();
			jQuery(this).fmwp_notice({
				message: data,
				type: 'error'
			});
			fmwp_profile.topics.loading = false;
		}
	});
}
