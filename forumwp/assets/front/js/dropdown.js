(function ($) {

	/**
	 * The constructor of the dropdown object
	 * @param   {object}  element  The menu element.
	 * @returns {object}           The dropdown menu object.
	 */
	function dropdownMenu(element) {

		var self = {
			get: function(){
				return self;
			},

			show: function () {
				self.hideAll();

				/* add dropdown into the <body> */
				self.$menu = self.$element.find('.fmwp-dropdown');
				if ( !self.$menu.length ) {
					self.$menu = $('div.fmwp-dropdown[data-element="' + self.data.element + '"]').first();
				}
				self.$dropdown = self.$menu.clone();
				self.$dropdown.on('click', 'li a', self.itemHandler); /* add the handler for menu items */
				$(window).on('resize', self.updatePosition); /* update the position on window resize */
				$(document.body).append(self.$dropdown);

				/* trigger event */
				self.$element.trigger('fmwp_dropdown_render', {
					dropdown_layout: self.$dropdown,
					trigger: self.data.trigger,
					element: self.data.elemen,
					obj: self.$element
				});

				/* set styles and show */
				self.$dropdown.css(self.calculatePosition()).show();
				self.$element.addClass('fmwp-dropdown-shown').data('fmwp-dropdown-show', true);

				return self;
			},

			hide: function () {
				if ( self.$dropdown && self.$dropdown.is(':visible') ) {
					$(window).off('resize', self.updatePosition);
					self.$dropdown.remove();
					self.$element.removeClass('fmwp-dropdown-shown').data('fmwp-dropdown-show', false);
				}

				return self;
			},

			hideAll: function () {
				self.hide();
				$('body > div.fmwp-dropdown').remove();
				$('.fmwp-dropdown-shown').removeClass('fmwp-dropdown-shown').data('fmwp-dropdown-show', false);

				return self;
			},

			calculatePosition: function () {
				var offset = self.$element.offset(),
					rect = self.$element.get(0).getBoundingClientRect(),
					height = self.$dropdown.innerHeight() || 150,
					width = self.data.width || 150,
					place = '';

				var css = {
					position: 'absolute',
					width: width + 'px'
				};

				/* vertical position */
				if ( window.innerHeight - rect.bottom > height ) {
					css.top = offset.top + rect.height + 'px';
					place += 'bottom';
				} else {
					place += 'top';
					css.top = offset.top - height + 'px';
				}

				/* horisontal position */
				if ( offset.left > width || offset.left > window.innerWidth / 2 ) {
					css.left = offset.left + rect.width - width + 'px';
					place += '-left';
				} else {
					css.left = offset.left + 'px';
					place += '-right';
				}

				/* border */
				switch ( place ) {
					case 'bottom-right':
						css.borderRadius = '0px 5px 5px 5px';
						break;
					case 'bottom-left':
						css.borderRadius = '5px 0px 5px 5px';
						break;
					case 'top-right':
						css.borderRadius = '5px 5px 5px 0px';
						break;
					case 'top-left':
						css.borderRadius = '5px 5px 0px 5px';
						break;
				}

				return css;
			},

			updatePosition: function () {
				if ( self.$dropdown && self.$dropdown.is(':visible') ) {
					self.$dropdown.css(self.calculatePosition());
				}

				return self;
			},

			itemHandler: function (e) {
				let attrHref = $(e.currentTarget).attr('href');
				if ( '#' === attrHref ) {
					e.preventDefault();
				}
				e.stopPropagation();

				/* trigger 'click' in the original menu */
				var attrClass = $(e.currentTarget).attr('class');
				self.$menu.find('li a[class="' + attrClass + '"]').trigger('click');

				/* hide dropdown */
				self.hide();
			},

			triggerHandler: function (e) {
				e.stopPropagation();

				self.$element = $(e.currentTarget);

				if ( self.$element.data('fmwp-dropdown-show') ) {
					self.hide();
				} else {
					self.show();
				}
			}
		};

		self.$menu = $(element);

		self.data = self.$menu.data();

		self.$element = self.$menu.closest(self.data.element);
		if ( !self.$element.length ) {
			self.$element = $(self.data.element).first();
		}

		self.$dropdown = $(document.body).children('div[data-element="' + self.data.element + '"]');

		if ( typeof self.data.initted === 'undefined' ) {
			self.$menu.data('initted', true);
			$(document.body).on(self.data.trigger, self.data.element, self.triggerHandler);
		}

		if ( typeof dropdownMenu.globalHandlersInitted === 'undefined' ) {
			dropdownMenu.globalHandlersInitted = true;
			$(document.body).on('click', function (e) {
				if ( !$(e.target).closest('.fmwp-dropdown').length ) {
					self.hideAll();
				}
			});
		}

		return self;
	}

	/* Add the method dropdownMenu() to the jQuery */
	$.fn.dropdownMenu = function (action) {
		if ( typeof action === 'string' && action ) {
			return this.map( function (i, menu) {
				var obj = dropdownMenu( menu );
				return typeof obj[action] === 'function' ? obj[action]() : obj[action];
			} ).toArray();
		} else {
			return this.each( function (i, menu) {
				dropdownMenu( menu );
			} );
		}
	};

})(jQuery);


/* Init all dropdown menus on page load */
jQuery( document ).ready( function() {
	jQuery('.fmwp-dropdown').dropdownMenu();
});
