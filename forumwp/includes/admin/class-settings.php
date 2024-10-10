<?php
namespace fmwp\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\admin\Settings' ) ) {

	/**
	 * Class Settings
	 *
	 * @package fmwp\admin
	 */
	class Settings {

		/**
		 * @var array[]
		 */
		public $config = array();

		/**
		 * Settings constructor.
		 */
		public function __construct() {
			add_action( 'forumwp_init', array( &$this, 'init' ) );

			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
			add_action( 'admin_init', array( $this, 'permalinks_save' ) );

			add_action( 'fmwp_before_settings_email__content', array( $this, 'email_templates_list_table' ) );
			add_filter( 'fmwp_section_fields', array( $this, 'email_template_fields' ), 10, 2 );

			add_action( 'plugins_loaded', array( $this, 'fmwp_check_template_version' ) );
			add_filter( 'fmwp_settings_custom_tabs', array( $this, 'add_custom_content_tab' ) );
			add_filter( 'fmwp_settings_section_override_templates__content', array( $this, 'override_templates_list_table' ) );
		}

		public function add_custom_content_tab( $custom_array ) {
			$custom_array[] = 'override_templates';
			return $custom_array;
		}

		/**
		 * Include admin files conditionally.
		 *
		 * @since 1.0
		 */
		public function conditional_includes() {
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}
			if ( 'options-permalink' === $screen->id ) {
				add_settings_field(
					FMWP()->options()->get_key( 'forum_slug' ),
					__( 'Forum base', 'forumwp' ),
					array( $this, 'forum_slug_input' ),
					'permalink',
					'optional'
				);
				add_settings_field(
					FMWP()->options()->get_key( 'topic_slug' ),
					__( 'Topic base', 'forumwp' ),
					array( $this, 'topic_slug_input' ),
					'permalink',
					'optional'
				);

				if ( FMWP()->options()->get( 'forum_categories' ) ) {
					add_settings_field(
						FMWP()->options()->get_key( 'forum_category_slug' ),
						__( 'Forum Category base', 'forumwp' ),
						array( $this, 'forum_category_slug_input' ),
						'permalink',
						'optional'
					);
				}

				if ( FMWP()->options()->get( 'topic_tags' ) ) {
					add_settings_field(
						FMWP()->options()->get_key( 'topic_tag_slug' ),
						__( 'Topic Tag base', 'forumwp' ),
						array( $this, 'topic_tag_slug_input' ),
						'permalink',
						'optional'
					);
				}
			}
		}

		/**
		 * Show a slug input box for CPT Forum slug.
		 *
		 * @since 1.0
		 */
		public function forum_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' );
			?>
			<input type="text" class="regular-text code"
					name="<?php echo esc_attr( FMWP()->options()->get_key( 'forum_slug' ) ); ?>"
					value="<?php echo esc_attr( FMWP()->options()->get( 'forum_slug' ) ); ?>"
					placeholder="<?php echo esc_attr( $defaults['forum_slug'] ); ?>" />
			<?php
		}

		/**
		 * Show a slug input box for Topic CPT slug.
		 *
		 * @since 1.0
		 */
		public function topic_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' );
			?>
			<input type="text" class="regular-text code"
					name="<?php echo esc_attr( FMWP()->options()->get_key( 'topic_slug' ) ); ?>"
					value="<?php echo esc_attr( FMWP()->options()->get( 'topic_slug' ) ); ?>"
					placeholder="<?php echo esc_attr( $defaults['topic_slug'] ); ?>" />
			<?php
		}

		/**
		 * Show a slug input box for Forum Category slug.
		 *
		 * @since 1.0
		 */
		public function forum_category_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' );
			?>
			<input type="text" class="regular-text code"
					name="<?php echo esc_attr( FMWP()->options()->get_key( 'forum_category_slug' ) ); ?>"
					value="<?php echo esc_attr( FMWP()->options()->get( 'forum_category_slug' ) ); ?>"
					placeholder="<?php echo esc_attr( $defaults['forum_category_slug'] ); ?>" />
			<?php
		}

		/**
		 * Show a slug input box for Topic Tag slug.
		 *
		 * @since 1.0
		 */
		public function topic_tag_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' );
			?>
			<input type="text" class="regular-text code"
					name="<?php echo esc_attr( FMWP()->options()->get_key( 'topic_tag_slug' ) ); ?>"
					value="<?php echo esc_attr( FMWP()->options()->get( 'topic_tag_slug' ) ); ?>"
					placeholder="<?php echo esc_attr( $defaults['topic_tag_slug'] ); ?>" />
			<?php
		}

		/**
		 * Save permalinks handler
		 *
		 * @since 1.0
		 */
		public function permalinks_save() {
			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! isset( $_POST['permalink_structure'] ) ) {
				// We must not be saving permalinks.
				return;
			}

			check_admin_referer( 'update-permalink' );

			$forum_base_key = FMWP()->options()->get_key( 'forum_slug' );
			$topic_base_key = FMWP()->options()->get_key( 'topic_slug' );

			$forum_base = isset( $_POST[ $forum_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $forum_base_key ] ) ) : '';
			$topic_base = isset( $_POST[ $topic_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $topic_base_key ] ) ) : '';

			FMWP()->options()->update( 'forum_slug', $forum_base );
			FMWP()->options()->update( 'topic_slug', $topic_base );

			if ( FMWP()->options()->get( 'forum_categories' ) ) {
				$forum_category_base_key = FMWP()->options()->get_key( 'forum_category_slug' );
				$forum_category_base     = isset( $_POST[ $forum_category_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $forum_category_base_key ] ) ) : '';
				FMWP()->options()->update( 'forum_category_slug', $forum_category_base );
			}

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$topic_tag_base_key = FMWP()->options()->get_key( 'topic_tag_slug' );
				$topic_tag_base     = isset( $_POST[ $topic_tag_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $topic_tag_base_key ] ) ) : '';
				FMWP()->options()->update( 'topic_tag_slug', $topic_tag_base );
			}
		}

		/**
		 * Set FMWP Settings
		 */
		public function init() {
			$pages = get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => array( 'ID', 'post_title' ),
				)
			);

			$page_options = array( '' => __( '(None)', 'forumwp' ) );
			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page ) {
					$page_options[ $page->ID ] = $page->post_title;
				}
			}

			$general_pages_fields = array();
			foreach ( FMWP()->config()->get( 'core_pages' ) as $page_id => $page ) {
				$page_title = ! empty( $page['title'] ) ? $page['title'] : '';

				$general_pages_fields[] = array(
					'id'          => $page_id . '_page',
					'type'        => 'select',
					// translators: %s is a page title.
					'label'       => sprintf( __( '%s page', 'forumwp' ), $page_title ),
					'options'     => $page_options,
					'placeholder' => __( 'Choose a page...', 'forumwp' ),
					'size'        => 'small',
				);
			}

			$topic_fields = array(
				array(
					'id'      => 'topic_tags',
					'type'    => 'checkbox',
					'label'   => __( 'Topic Tags', 'forumwp' ),
					'helptip' => __( 'Enable tags for topics', 'forumwp' ),
				),
				array(
					'id'    => 'topic_throttle',
					'type'  => 'number',
					'size'  => 'small',
					'label' => __( 'Time between new topics (seconds)', 'forumwp' ),
				),
				array(
					'id'      => 'show_forum',
					'type'    => 'checkbox',
					'size'    => 'small',
					'label'   => __( 'Show forum title', 'forumwp' ),
					'helptip' => __( 'Show forum title at individual topic page and at topics lists', 'forumwp' ),
				),
				array(
					'id'      => 'default_topics_order',
					'type'    => 'select',
					'size'    => 'small',
					'options' => FMWP()->common()->topic()->sort_by,
					'label'   => __( 'Default topics order', 'forumwp' ),
					'helptip' => __( 'Default topics order on latest topics list', 'forumwp' ),
				),
			);

			$custom_templates = FMWP()->common()->topic()->get_templates( 'fmwp_topic' );
			if ( count( $custom_templates ) ) {
				$topic_fields[] = array(
					'id'      => 'default_topic_template',
					'type'    => 'select',
					'label'   => __( 'Default topics template', 'forumwp' ),
					'options' => array_merge(
						array(
							'' => __( 'Default Template', 'forumwp' ),
						),
						$custom_templates
					),
					'helptip' => __( 'Default template for all topics at your site. You may set different for each topic in the topic\'s styling section', 'forumwp' ),
					'size'    => 'small',
				);
			} else {
				$topic_fields[] = array(
					'id'    => 'default_topic_template',
					'type'  => 'hidden',
					'value' => '',
				);
			}

			if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
				$topic_fields[] = array(
					'id'      => 'ajax_increment_views',
					'type'    => 'checkbox',
					'label'   => __( 'Use AJAX To Update Views', 'forumwp' ),
					'helptip' => __( 'There is an ability to count views via AJAX in some cases when WP cache is active', 'forumwp' ),
				);
			} else {
				$topic_fields[] = array(
					'id'    => 'ajax_increment_views',
					'type'  => 'hidden',
					'value' => 0,
				);
			}

			$forums = get_posts(
				array(
					'post_type'      => 'fmwp_forum',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'     => 'fmwp_visibility',
							'value'   => 'public',
							'compare' => '=',
						),
					),
					'fields'         => array( 'ID', 'post_title' ),
				)
			);

			$forum_options = array( '' => __( 'None', 'forumwp' ) );
			if ( ! empty( $forums ) && ! is_wp_error( $forums ) ) {
				foreach ( $forums as $forum ) {
					$forum_options[ $forum->ID ] = $forum->post_title;
				}
			}

			$forums_fields = array(
				array(
					'id'      => 'forum_categories',
					'type'    => 'checkbox',
					'label'   => __( 'Forum categories', 'forumwp' ),
					'helptip' => __( 'Enable categories for forums', 'forumwp' ),
				),
				array(
					'id'      => 'default_forum',
					'type'    => 'select',
					'size'    => 'medium',
					'options' => $forum_options,
					'label'   => __( 'Default forum', 'forumwp' ),
					'helptip' => __( 'When you create a topic from topics list it will be added to the default forum', 'forumwp' ),
				),
				array(
					'id'      => 'default_forums_order',
					'type'    => 'select',
					'size'    => 'small',
					'options' => array(
						'date_desc'  => __( 'Newest to Oldest', 'forumwp' ),
						'date_asc'   => __( 'Oldest to Newest', 'forumwp' ),
						'order_desc' => __( 'Most Priority', 'forumwp' ),
						'order_asc'  => __( 'Lower Priority', 'forumwp' ),
					),
					'label'   => __( 'Default forums order', 'forumwp' ),
					'helptip' => __( 'Default forums order on latest forums list', 'forumwp' ),
				),
			);

			$custom_templates = FMWP()->common()->forum()->get_templates( 'fmwp_forum' );
			if ( count( $custom_templates ) ) {
				$forums_fields[] = array(
					'id'      => 'default_forums_template',
					'type'    => 'select',
					'label'   => __( 'Default forums template', 'forumwp' ),
					'options' => array_merge(
						array(
							'' => __( 'Default Template', 'forumwp' ),
						),
						$custom_templates
					),
					'helptip' => __( 'Default template for all forums at your site. You may set different for each forum in the forum\'s styling section', 'forumwp' ),
					'size'    => 'small',
				);
			} else {
				$forums_fields[] = array(
					'id'    => 'default_forums_template',
					'type'  => 'hidden',
					'value' => '',
				);
			}

			$this->config = apply_filters(
				'fmwp_settings',
				array(
					'general' => array(
						'title'    => __( 'General', 'forumwp' ),
						'sections' => array(
							'pages'           => array(
								'title'  => __( 'Pages', 'forumwp' ),
								'fields' => $general_pages_fields,
							),
							'general_options' => array(
								'title'  => __( 'General Options', 'forumwp' ),
								'fields' => array(
									array(
										'id'      => 'default_role',
										'type'    => 'select',
										'size'    => 'small',
										'label'   => __( 'Default Role', 'forumwp' ),
										'helptip' => __( 'New members will get this forum role automatically', 'forumwp' ),
										'options' => FMWP()->config()->get( 'custom_roles' ),
									),
									array(
										'id'      => 'raw_html_enabled',
										'type'    => 'checkbox',
										'label'   => __( 'Enable raw HTML in topic/reply content', 'forumwp' ),
										'helptip' => __( 'If enabled can be less secure. Please enable only if you plan to get an ability for users create topics and replies with HTML tags', 'forumwp' ),
									),
									array(
										'id'      => 'breadcrumb_enabled',
										'type'    => 'checkbox',
										'label'   => __( 'Enable Breadcrumbs', 'forumwp' ),
										'helptip' => __( 'If enabled, breadcrumbs will be displayed on ForumWP templates', 'forumwp' ),
									),
									array(
										'id'      => 'login_redirect',
										'type'    => 'text',
										'size'    => 'small',
										'label'   => __( 'Login Redirect', 'forumwp' ),
										'helptip' => __( 'If empty user will be redirected to the same page. This option can be rewritten via login shortcode "redirect" attribute', 'forumwp' ),
									),
									array(
										'id'      => 'register_redirect',
										'type'    => 'text',
										'size'    => 'small',
										'label'   => __( 'Registration Redirect', 'forumwp' ),
										'helptip' => __( 'If empty user will be redirected to the Profile page. This option can be rewritten via register shortcode "redirect" attribute', 'forumwp' ),
									),
									array(
										'id'      => 'logout_redirect',
										'type'    => 'text',
										'size'    => 'small',
										'label'   => __( 'Logout Redirect', 'forumwp' ),
										'helptip' => __( 'If empty user will be redirected to the Login page.', 'forumwp' ),
									),
								),
							),
							'forums'          => array(
								'title'  => __( 'Forums', 'forumwp' ),
								'fields' => $forums_fields,
							),
							'topics'          => array(
								'title'  => __( 'Topics', 'forumwp' ),
								'fields' => $topic_fields,
							),
							'replies'         => array(
								'title'  => __( 'Replies', 'forumwp' ),
								'fields' => array(
									array(
										'id'    => 'reply_throttle',
										'type'  => 'number',
										'size'  => 'small',
										'label' => __( 'Time between new replies', 'forumwp' ),
									),
									array(
										'id'      => 'reply_delete',
										'type'    => 'select',
										'size'    => 'small',
										'label'   => __( 'Reply deletion: sub-reply action to take', 'forumwp' ),
										'options' => array(
											'sub_delete'   => __( 'Delete all sub replies', 'forumwp' ),
											'change_level' => __( 'Change sub replies\' level', 'forumwp' ),
										),
										'helptip' => __( 'When a reply to a topic is removed/deleted, what would you like to happen to replies to that reply (sub replies)?', 'forumwp' ),
									),
									array(
										'id'      => 'reply_user_role',
										'type'    => 'checkbox',
										'label'   => __( 'Show user role tag on replies', 'forumwp' ),
										'helptip' => __( 'If turned on the role of the user will appear to the right of their name on topic page', 'forumwp' ),
									),
								),
							),
						),
					),
					'email'   => array(
						'title'  => __( 'Email', 'forumwp' ),
						'fields' => array(
							array(
								'id'      => 'admin_email',
								'type'    => 'text',
								'label'   => __( 'Admin Email Address', 'forumwp' ),
								'helptip' => __( 'e.g. admin@companyname.com', 'forumwp' ),
							),
							array(
								'id'      => 'mail_from',
								'type'    => 'text',
								'label'   => __( 'Mail appears from', 'forumwp' ),
								'helptip' => __( 'e.g. Site Name', 'forumwp' ),
							),
							array(
								'id'      => 'mail_from_addr',
								'type'    => 'text',
								'label'   => __( 'Mail appears from address', 'forumwp' ),
								'helptip' => __( 'e.g. admin@companyname.com', 'forumwp' ),
							),
						),
					),
				)
			);

			$module_plans = FMWP()->modules()->get_list();
			if ( ! empty( $module_plans ) ) {

				$modules_settings_fields = array();
				foreach ( $module_plans as $plan_key => $plan_data ) {
					if ( empty( $plan_data['modules'] ) ) {
						continue;
					}

					$modules_settings_fields[] = array(
						'id'            => $plan_key . '_label',
						'type'          => 'separator',
						// translators: %s is a plan data.
						'value'         => sprintf( __( '%s modules', 'forumwp' ), $plan_data['title'] ),
						'without_label' => true,
					);

					foreach ( $plan_data['modules'] as $slug => $data ) {
						$slug = FMWP()->undash( $slug );

						$modules_settings_fields[] = array(
							'id'      => 'module_' . $slug . '_on',
							'type'    => 'checkbox',
							'label'   => $data['title'],
							'helptip' => $data['description'],
						);
					}
				}

				$sections = array(
					'modules' => array(
						'title'  => __( 'Enabled Modules', 'forumwp' ),
						'fields' => $modules_settings_fields,
					),
				);
				$sections = apply_filters( 'fmwp_modules_settings_sections', $sections );

				$this->config['modules'] = array(
					'title'    => __( 'Modules', 'forumwp' ),
					'sections' => $sections,
				);
			}

			$this->config['advanced'] = array(
				'title'  => __( 'Advanced', 'forumwp' ),
				'fields' => array(
					array(
						'id'      => 'disable-fa-styles',
						'type'    => 'checkbox',
						'label'   => __( 'Disable FontAwesome styles', 'forumwp' ),
						'helptip' => __( 'To avoid duplicates if you have enqueued FontAwesome styles you could disable it.', 'forumwp' ),
					),
					array(
						'id'      => 'uninstall-delete-settings',
						'type'    => 'checkbox',
						'label'   => __( 'Delete settings on uninstall', 'forumwp' ),
						'helptip' => __( 'Once removed, this data cannot be restored.', 'forumwp' ),
					),
				),
			);

			$this->config['override_templates'] = array(
				'title'  => __( 'Override templates', 'forumwp' ),
				'fields' => array(
					array(
						'type' => 'override_templates',
					),
				),
			);
		}

		/**
		 * @param $current_tab
		 * @param $current_subtab
		 *
		 * @return bool
		 */
		public function section_is_custom( $current_tab, $current_subtab ) {
			$custom_tabs   = apply_filters( 'fmwp_settings_custom_tabs', array() );
			$custom_subtab = apply_filters( 'fmwp_settings_custom_subtabs', array(), $current_tab );

			return in_array( $current_tab, $custom_tabs, true ) || in_array( $current_subtab, $custom_subtab, true );
		}

		/**
		 * Get settings section
		 *
		 * @param string $tab
		 * @param string $section
		 * @param bool   $assoc Return Associated array
		 *
		 * @return bool|array
		 */
		public function get_settings( $tab = '', $section = '', $assoc = false ) {
			if ( empty( $tab ) ) {
				$tabs = array_keys( $this->config );
				$tab  = $tabs[0];
			}

			if ( ! isset( $this->config[ $tab ] ) ) {
				return false;
			}

			if ( ! empty( $section ) && empty( $this->config[ $tab ]['sections'] ) ) {
				return false;
			}

			if ( ! empty( $this->config[ $tab ]['sections'] ) ) {
				if ( empty( $section ) ) {
					$sections = array_keys( $this->config[ $tab ]['sections'] );
					$section  = $sections[0];
				}

				if ( isset( $this->config[ $tab ]['sections'] ) && ! isset( $this->config[ $tab ]['sections'][ $section ] ) ) {
					return false;
				}

				$fields = $this->config[ $tab ]['sections'][ $section ]['fields'];
			} else {
				$fields = $this->config[ $tab ]['fields'];
			}

			$fields = apply_filters( 'fmwp_section_fields', $fields, $tab, $section );

			$assoc_fields = array();
			foreach ( $fields as &$data ) {
				if ( empty( $data['id'] ) ) {
					continue;
				}

				if ( ! isset( $data['value'] ) ) {
					$data['value'] = FMWP()->options()->get( $data['id'] );
				}

				if ( $assoc ) {
					$assoc_fields[ $data['id'] ] = $data;
				}
			}

			return $assoc ? $assoc_fields : $fields;
		}

		/**
		 * Generate pages tabs
		 *
		 * @param string $page
		 *
		 * @return string
		 */
		public function tabs_menu( $page = 'settings' ) {
			switch ( $page ) {
				case 'settings':
					$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
					if ( empty( $current_tab ) ) {
						$all_tabs    = array_keys( $this->config );
						$current_tab = $all_tabs[0];
					}

					$i    = 0;
					$tabs = '';
					foreach ( $this->config as $slug => $tab ) {
						if ( empty( $tab['fields'] ) && empty( $tab['sections'] ) ) {
							continue;
						}

						$link_args = array(
							'page' => 'forumwp-settings',
						);
						if ( ! empty( $i ) ) {
							$link_args['tab'] = $slug;
						}

						$tab_link = add_query_arg(
							$link_args,
							admin_url( 'admin.php' )
						);

						$active = $current_tab === $slug ? 'nav-tab-active' : '';
						$tabs  .= sprintf(
							'<a href="%s" class="nav-tab %s">%s</a>',
							$tab_link,
							$active,
							$tab['title']
						);

						++$i;
					}
					break;

				default:
					$tabs = apply_filters( 'fmwp_generate_tabs_menu_' . $page, '' );
					break;
			}

			return '<h2 class="nav-tab-wrapper fmwp-nav-tab-wrapper">' . $tabs . '</h2>';
		}

		/**
		 * Generate sub-tabs
		 *
		 * @param string $tab
		 *
		 * @return string
		 */
		public function subtabs_menu( $tab = '' ) {
			if ( empty( $tab ) ) {
				$all_tabs = array_keys( $this->config );
				$tab      = $all_tabs[0];
			}

			if ( empty( $this->config[ $tab ]['sections'] ) ) {
				return '';
			}

			$current_tab    = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( sanitize_key( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( empty( $current_subtab ) ) {
				$sections       = array_keys( $this->config[ $tab ]['sections'] );
				$current_subtab = $sections[0];
			}

			$i       = 0;
			$subtabs = '';
			foreach ( $this->config[ $tab ]['sections'] as $slug => $subtab ) {
				$custom_section = FMWP()->admin()->settings()->section_is_custom( $current_tab, $slug );

				if ( ! $custom_section && empty( $subtab['fields'] ) ) {
					continue;
				}

				$link_args = array(
					'page' => 'forumwp-settings',
				);
				if ( ! empty( $current_tab ) ) {
					$link_args['tab'] = $current_tab;
				}
				if ( ! empty( $i ) ) {
					$link_args['section'] = $slug;
				}

				$tab_link = add_query_arg(
					$link_args,
					admin_url( 'admin.php' )
				);

				$active = $current_subtab === $slug ? 'current' : '';

				$subtabs .= sprintf(
					'<a href="%s" class="%s">%s</a> | ',
					$tab_link,
					$active,
					$subtab['title']
				);

				++$i;
			}

			return '<div><ul class="subsubsub">' . substr( $subtabs, 0, -3 ) . '</ul></div>';
		}

		/**
		 * Render settings section
		 *
		 * @param $current_tab
		 * @param $current_subtab
		 *
		 * @return string
		 */
		public function display_section( $current_tab, $current_subtab ) {
			$fields = $this->get_settings( $current_tab, $current_subtab );
			if ( ! $fields ) {
				return '';
			}

			return FMWP()->admin()->forms(
				array(
					'class'     => 'fmwp-options-' . $current_tab . '-' . $current_subtab . ' fmwp-third-column',
					'prefix_id' => 'fmwp_options',
					'fields'    => $fields,
				)
			)->display( false );
		}

		/**
		 * Display Email Notifications Templates List
		 */
		public function email_templates_list_table() {
			$email_key           = empty( $_GET['email'] ) ? '' : urldecode( sanitize_key( $_GET['email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$email_notifications = FMWP()->config()->get( 'email_notifications' );

			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				include_once FMWP()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'emails-list-table.php';
			}
		}

		/**
		 * Edit email template fields
		 *
		 * @param array $fields
		 * @param string $tab
		 *
		 * @return array
		 */
		public function email_template_fields( $fields, $tab ) {
			if ( 'email' !== $tab ) {
				return $fields;
			}

			$email_key           = empty( $_GET['email'] ) ? '' : urldecode( sanitize_key( $_GET['email'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$email_notifications = FMWP()->config()->get( 'email_notifications' );
			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				return $fields;
			}

			$fields = array(
				array(
					'id'    => 'fmwp_email_template',
					'type'  => 'hidden',
					'value' => $email_key,
				),
				array(
					'id'      => $email_key . '_on',
					'type'    => 'checkbox',
					'label'   => $email_notifications[ $email_key ]['title'],
					'helptip' => $email_notifications[ $email_key ]['description'],
				),
				array(
					'id'          => $email_key . '_sub',
					'type'        => 'text',
					'label'       => __( 'Subject Line', 'forumwp' ),
					'helptip'     => __( 'This is the subject line of the email', 'forumwp' ),
					'conditional' => array( $email_key . '_on', '=', 1 ),
				),
				array(
					'id'          => $email_key,
					'type'        => 'email_template',
					'label'       => __( 'Message Body', 'forumwp' ),
					'helptip'     => __( 'This is the content of the email', 'forumwp' ),
					'value'       => FMWP()->common()->mail()->get_template( $email_key ),
					'conditional' => array( $email_key . '_on', '=', 1 ),
				),
			);

			return apply_filters( 'fmwp_settings_email_section_fields', $fields, $email_key );
		}

		public function override_templates_list_table() {
			$fmwp_check_version = get_transient( 'fmwp_check_template_versions' );
			ob_start();
			?>
			<p class="description" style="margin: 20px 0 0 0;">
				<a href="<?php echo esc_url( add_query_arg( 'fmwp_adm_action', 'check_templates_version' ) ); ?>" class="button" style="margin-right: 10px;">
					<?php esc_html_e( 'Re-check templates', 'forumwp' ); ?>
				</a>
				<?php
				if ( false !== $fmwp_check_version ) {
					// translators: %s: Last checking templates time.
					echo esc_html( sprintf( __( 'Last update: %s. You could re-check changes manually.', 'forumwp' ), wp_date( get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i:s' ), $fmwp_check_version ) ) );
				} else {
					esc_html_e( 'Templates haven\'t check yet. You could check changes manually.', 'forumwp' );
				}
				?>
			</p>
			<p class="description" style="margin: 20px 0 0 0;">
				<?php
				// translators: %s: Link to the docs article.
				echo wp_kses_post( sprintf( __( 'You may get more details about overriding templates <a href="%s" target="_blank">here</a>.', 'forumwp' ), 'https://docs.forumwpplugin.com/article/1502-template-structure' ) );
				?>
			</p>
			<?php
			include_once FMWP_PATH . 'includes/admin/templates/settings/version-template-list-table.php';

			return ob_get_clean();
		}

		/**
		 * Periodically checking the versions of templates.
		 *
		 * @since 2.0.3
		 *
		 * @return void
		 */
		public function fmwp_check_template_version() {
			$fmwp_check_version = get_transient( 'fmwp_check_template_versions' );
			if ( false === $fmwp_check_version ) {
				$this->get_override_templates();
			}
		}

		/**
		 * @param $get_list boolean
		 *
		 * @return array|void
		 */
		public function get_override_templates( $get_list = false ) {
			$outdated_files     = array();
			$scan_files['fmwp'] = self::scan_template_files( FMWP_PATH . '/templates/' );

			/**
			 * Filters ForumWP templates files for scan versions and overriding.
			 *
			 * @since 2.0.3
			 * @hook fmwp_override_templates_scan_files
			 *
			 * @param {array} $scan_files The list of template files for scanning.
			 *
			 * @return {array} The list of template files for scanning.
			 */
			$scan_files = apply_filters( 'fmwp_override_templates_scan_files', $scan_files );
			$out_date   = false;

			set_transient( 'fmwp_check_template_versions', time(), 12 * HOUR_IN_SECONDS );

			foreach ( $scan_files as $key => $files ) {
				foreach ( $files as $file ) {
					if ( ! str_contains( $file, 'emails/' ) ) {
						$located = array();
						/**
						 * Filters ForumWP templates locations for override templates table.
						 *
						 * @since 2.0.3
						 * @hook fmwp_override_templates_get_template_path__{$key}
						 *
						 * @param {array} $located Locations for override templates table.
						 * @param {array} $file    Template filename.
						 *
						 * @return {array} The list of template locations.
						 */
						$located = apply_filters( 'fmwp_override_templates_get_template_path__' . $key, $located, $file );

						if ( ! empty( $located ) ) {
							$theme_file = $located['theme'];
						} elseif ( file_exists( get_stylesheet_directory() . '/forumwp/' . $file ) ) {
							$theme_file = get_stylesheet_directory() . '/forumwp/' . $file;
						} else {
							$theme_file = false;
						}

						if ( ! empty( $theme_file ) ) {
							$core_file = $file;

							if ( ! empty( $located ) ) {
								$core_path      = $located['core'];
								$core_file_path = stristr( $core_path, 'wp-content' );
							} else {
								$core_path      = FMWP_PATH . '/templates/' . $core_file;
								$core_file_path = stristr( FMWP_PATH . 'templates/' . $core_file, 'wp-content' );
							}
							$core_version  = self::get_file_version( $core_path );
							$theme_version = self::get_file_version( $theme_file );

							$status      = esc_html__( 'Theme version up to date', 'forumwp' );
							$status_code = 1;
							if ( version_compare( $theme_version, $core_version, '<' ) ) {
								$status      = esc_html__( 'Theme version is out of date', 'forumwp' );
								$status_code = 0;
							}
							if ( '' === $theme_version ) {
								$status      = esc_html__( 'Theme version is empty', 'forumwp' );
								$status_code = 0;
							}
							if ( 0 === $status_code ) {
								$out_date = true;
								update_option( 'fmwp_override_templates_outdated', true );
							}
							$outdated_files[] = array(
								'core_version'  => $core_version,
								'theme_version' => $theme_version,
								'core_file'     => $core_file_path,
								'theme_file'    => stristr( $theme_file, 'wp-content' ),
								'status'        => $status,
								'status_code'   => $status_code,
							);
						}
					}
				}
			}

			if ( false === $out_date ) {
				delete_option( 'fmwp_override_templates_outdated' );
			}
			update_option( 'fmwp_template_statuses', $outdated_files );
			if ( true === $get_list ) {
				return $outdated_files;
			}
		}

		/**
		 * @param $file string
		 *
		 * @return string
		 */
		public static function get_file_version( $file ) {
			// Avoid notices if file does not exist.
			if ( ! file_exists( $file ) ) {
				return '';
			}

			// phpcs:disable WordPress.WP.AlternativeFunctions -- for directly fopen, fwrite, fread, fclose functions using

			// We don't need to write to the file, so just open for reading.
			$fp = fopen( $file, 'rb' );

			// Pull only the first 8kiB of the file in.
			$file_data = fread( $fp, 8192 );

			// PHP will close a file handle, but we are good citizens.
			fclose( $fp );

			// phpcs:enable WordPress.WP.AlternativeFunctions -- for directly fopen, fwrite, fread, fclose functions using

			// Make sure we catch CR-only line endings.
			$file_data = str_replace( "\r", "\n", $file_data );
			$version   = '';

			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
				$version = _cleanup_header_comment( $match[1] );
			}

			return $version;
		}

		/**
		 * Scan the template files.
		 *
		 * @param  string $template_path Path to the template directory.
		 * @return array
		 */
		public static function scan_template_files( $template_path ) {
			// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged -- for silenced scandir functions running
			$files = @scandir( $template_path );
			// phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged -- for silenced scandir functions running

			$result = array();
			if ( ! empty( $files ) ) {

				foreach ( $files as $value ) {

					if ( ! in_array( $value, array( '.', '..' ), true ) ) {

						if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
							$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
							foreach ( $sub_files as $sub_file ) {
								$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
							}
						} else {
							$result[] = $value;
						}
					}
				}
			}
			return $result;
		}
	}
}
