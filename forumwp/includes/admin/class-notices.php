<?php
namespace fmwp\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'fmwp\admin\Notices' ) ) {

	/**
	 * Class Notices
	 *
	 * @package fmwp\admin
	 */
	class Notices {

		/**
		 * Notices list
		 *
		 * @var array
		 */
		private $list = array();

		/**
		 * Notices constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( &$this, 'create_list' ) );
			add_action( 'admin_notices', array( &$this, 'render' ), 1 );
		}

		/**
		 *
		 * @since 1.0
		 */
		public function create_list() {
			$this->install_core_page_notice();
			$this->old_customers();
			$this->need_upgrade();
			$this->permalink_conflict();
			$this->show_update_messages();
			$this->template_version();

			do_action( 'fmwp_admin_create_notices' );
		}

		/**
		 * Render all admin notices
		 *
		 * @since 2.0
		 */
		public function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$admin_notices = $this->get_admin_notices();

			$hidden = get_option( 'fmwp_hidden_admin_notices', array() );

			uasort( $admin_notices, array( &$this, 'priority_sort' ) );

			foreach ( $admin_notices as $key => $admin_notice ) {
				if ( empty( $hidden ) || ! in_array( $key, $hidden, true ) ) {
					$this->display( $key );
				}
			}

			do_action( 'fmwp_admin_after_main_notices' );
		}

		/**
		 * @return array
		 */
		public function get_admin_notices() {
			return $this->list;
		}

		/**
		 * @param $admin_notices
		 */
		public function set_admin_notices( $admin_notices ) {
			$this->list = $admin_notices;
		}

		/**
		 * @param array $a
		 * @param array $b
		 *
		 * @return int
		 */
		public function priority_sort( $a, $b ) {
			if ( $a['priority'] === $b['priority'] ) {
				return 0;
			}
			return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
		}

		/**
		 * Add notice to FMWP notices array
		 *
		 * @param string $key
		 * @param array $data
		 * @param int $priority
		 *
		 * @uses Notices::get_admin_notices()
		 * @uses Notices::set_admin_notices()
		 *
		 * @since 2.0
		 */
		public function add( $key, $data, $priority = 10 ) {
			$admin_notices = $this->get_admin_notices();

			if ( empty( $admin_notices[ $key ] ) ) {
				$admin_notices[ $key ] = array_merge( $data, array( 'priority' => $priority ) );
				$this->set_admin_notices( $admin_notices );
			}
		}

		/**
		 * Remove notice from FMWP notices array
		 *
		 * @param string $key
		 *
		 * @uses Notices::get_admin_notices()
		 * @uses Notices::set_admin_notices()
		 *
		 * @since 2.0
		 */
		public function remove( $key ) {
			$admin_notices = $this->get_admin_notices();

			if ( ! empty( $admin_notices[ $key ] ) ) {
				unset( $admin_notices[ $key ] );
				$this->set_admin_notices( $admin_notices );
			}
		}

		/**
		 * Dismiss notices by key
		 *
		 * @param string $key
		 */
		public function dismiss( $key ) {
			$hidden_notices   = get_option( 'fmwp_hidden_admin_notices', array() );
			$hidden_notices[] = $key;
			update_option( 'fmwp_hidden_admin_notices', $hidden_notices );
		}

		/**
		 * Display single admin notice
		 *
		 * @param string $key
		 *
		 * @uses Notices::get_admin_notices()
		 *
		 * @since 2.0
		 */
		private function display( $key ) {
			$admin_notices = $this->get_admin_notices();

			if ( empty( $admin_notices[ $key ] ) ) {
				return;
			}

			$notice_data = $admin_notices[ $key ];

			$class = ! empty( $notice_data['class'] ) ? $notice_data['class'] : 'updated';
			if ( ! empty( $admin_notices[ $key ]['dismissible'] ) ) {
				$class .= ' is-dismissible';
			}

			$message = ! empty( $notice_data['message'] ) ? $notice_data['message'] : '';

			echo wp_kses(
				sprintf(
					'<div class="fmwp-admin-notice notice %s" data-key="%s">%s</div>',
					esc_attr( $class ),
					esc_attr( $key ),
					$message
				),
				FMWP()->get_allowed_html( 'admin_notice' )
			);
		}

		/**
		 * Regarding page setup
		 *
		 * @uses Notices::add()
		 *
		 * @since 1.0
		 */
		private function install_core_page_notice() {
			if ( ! current_user_can( 'manage_options' ) || FMWP()->options()->are_pages_installed() ) {
				return;
			}

			ob_start();
			?>
			<p>
				<?php
				// translators: %s is a plugin name.
				echo esc_html( printf( __( 'To add forum functionality to your website %s needs to create several front-end pages (Forums, Topics, Profile, Registration & Login).', 'forumwp' ), FMWP_PLUGIN_NAME ) );
				?>
			</p>
			<p>
				<a href="<?php echo esc_attr( add_query_arg( 'fmwp_adm_action', 'install_core_pages' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Create Pages', 'forumwp' ); ?>
				</a>
				&nbsp;
				<a href="#" class="button-secondary fmwp_secondary_dismiss">
					<?php esc_html_e( 'No thanks', 'forumwp' ); ?>
				</a>
			</p>
			<?php
			$message = ob_get_clean();

			$this->add(
				'wrong_pages',
				array(
					'class'       => 'updated',
					'message'     => $message,
					'dismissible' => true,
				),
				20
			);
		}

		/**
		 * Regarding old customers upgrade
		 *
		 * @uses Notices::add()
		 *
		 * @since 1.0
		 */
		private function old_customers() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$first_activation = get_option( 'fmwp_first_activation_date', false );

			// {first activation date}
			if ( ! $first_activation || $first_activation <= 1614681468 ) {
				return;
			}

			ob_start();
			?>
			<p>
				<?php echo wp_kses( __( 'ForumWP - We have made some changes to ForumWP and launched a free version of the plugin. If you are were using ForumWP prior to this update you will need to install <a href="https://forumwpplugin.com/addons/forumwp-pro/">ForumWP - Pro</a> as the basic modules are not available in the free version. In order to continue using the modules that extend the functionality of the free plugin, please install ForumWP - Pro. <a href="https://forumwpplugin.com/topic/changes-to-forumwp/">details</a>.', 'forumwp' ), FMWP()->get_allowed_html( 'admin_notice' ) ); ?>
			</p>
			<?php
			$message = ob_get_clean();

			$this->add(
				'replace_plus',
				array(
					'class'       => 'notice-warning',
					'message'     => $message,
					'dismissible' => true,
				),
				20
			);
		}

		private function need_upgrade() {
			if ( ! empty( FMWP()->admin()->upgrade()->necessary_packages ) ) {
				$url = add_query_arg( array( 'page' => 'fmwp_upgrade' ), admin_url( 'admin.php' ) );

				ob_start();
				?>
				<p>
					<?php
					// translators: %1$s,%3$s is a plugin name; %2$s,%4$s is a plugin version; %5$s is a upgrade page URL.
					echo wp_kses( sprintf( __( '<strong>%1$s version %2$s</strong> needs to be updated to work correctly.<br />It is necessary to update the structure of the database and options that are associated with <strong>%3$s %4$s</strong>.<br />Please visit "<a href="%5$s">Upgrade</a>" page and run the upgrade process.', 'forumwp' ), FMWP_PLUGIN_NAME, FMWP_VERSION, FMWP_PLUGIN_NAME, FMWP_VERSION, $url ), FMWP()->get_allowed_html( 'admin_notice' ) );
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( $url ); ?>" class="button button-primary"><?php esc_html_e( 'Upgrade Now', 'forumwp' ); ?></a>
					&nbsp;
				</p>
				<?php
				$message = ob_get_clean();

				$this->add(
					'upgrade',
					array(
						'class'   => 'error',
						'message' => $message,
					),
					4
				);
			} elseif ( isset( $_GET['fmwp-msg'] ) && 'updated' === sanitize_key( $_GET['fmwp-msg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['page'] ) && 'forumwp-settings' === sanitize_key( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$this->add(
						'settings_upgrade',
						array(
							'class'   => 'updated',
							// translators: %1$s is a plugin name; %2$s is a plugin version.
							'message' => '<p>' . sprintf( __( '<strong>%1$s %2$s</strong> Successfully Upgraded', 'forumwp' ), FMWP_PLUGIN_NAME, FMWP_VERSION ) . '</p>',
						),
						4
					);
				}
			}
		}

		/**
		 * Render message for page slug conflict with post type slug.
		 * @since 2.0
		 *
		 * @return void
		 */
		private function permalink_conflict() {
			$permalinks = array(
				FMWP()->options()->get( 'forum_slug' ),
				FMWP()->options()->get( 'topic_slug' ),
			);

			if ( FMWP()->options()->get( 'forum_categories' ) ) {
				$permalinks[] = FMWP()->options()->get( 'forum_category_slug' );
			}

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$permalinks[] = FMWP()->options()->get( 'topic_tag_slug' );
			}

			$post_types   = get_post_types(
				array(
					'public'             => true,
					'publicly_queryable' => true,
				)
			);
			$post_types[] = 'page';

			foreach ( $permalinks as $permalink ) {
				if ( empty( $permalink ) ) {
					continue;
				}

				$page = get_page_by_path( $permalink, OBJECT, $post_types );
				if ( ! $page ) {
					continue;
				}
				ob_start();
				?>
				<p>
					<?php
					// translators: %1$s is a post title; %2$s is a post name (slug).
					echo wp_kses( sprintf( __( 'Permalink Conflict! Please change permalink for the <strong>"%1$s"</strong> post, the <strong>"%2$s"</strong> slug is reserved.', 'forumwp' ), $page->post_title, $page->post_name ), FMWP()->get_allowed_html( 'admin_notice' ) );
					?>
				</p>
				<p>
					<a href="<?php echo esc_attr( get_edit_post_link( $page->ID ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Edit page', 'forumwp' ); ?>
					</a>
				</p>
				<?php
				$message = ob_get_clean();

				$this->add(
					'permalink_conflict' . $permalink,
					array(
						'class'       => 'notice-error',
						'message'     => $message,
						'dismissible' => false,
					),
					20
				);
			}
		}

		/**
		 * Update notices
		 *
		 * @since 2.0.3
		 */
		private function show_update_messages() {
			if ( ! isset( $_REQUEST['update'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			$update = sanitize_key( $_REQUEST['update'] ); // phpcs:ignore WordPress.Security.NonceVerification
			switch ( $update ) {
				case 'fmwp_settings_updated':
					$messages[0]['content'] = __( 'Settings updated successfully.', 'forumwp' );
					break;
			}

			if ( ! empty( $messages ) ) {
				foreach ( $messages as $message ) {
					$this->add(
						'actions',
						array(
							'class'   => 'updated',
							'message' => '<p>' . $message['content'] . '</p>',
						),
						20
					);
				}
			}
		}

		/**
		 * Check Templates Versions notice
		 */
		public function template_version() {
			if ( true === (bool) get_option( 'fmwp_override_templates_outdated' ) ) {
				$link = admin_url( 'admin.php?page=forumwp-settings&tab=override_templates' );
				ob_start();
				?>
				<p>
					<?php
					// translators: %s override templates page link.
					echo wp_kses_post( sprintf( __( 'Your templates are out of date. Please visit <a href="%s">override templates status page</a> and update templates.', 'forumwp' ), $link ) );
					?>
				</p>
				<?php
				$message = ob_get_clean();
				$this->add(
					'fmwp_override_templates_notice',
					array(
						'class'       => 'error',
						'message'     => $message,
						'dismissible' => false,
					)
				);
			}
		}
	}
}
