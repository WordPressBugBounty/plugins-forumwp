<?php
namespace fmwp\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\admin\Actions_Listener' ) ) {

	/**
	 * Class Actions_Listener
	 *
	 * @package fmwp\admin
	 */
	class Actions_Listener {

		/**
		 * Actions_Listener constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'save_settings' ) );

			//save handlers
			add_filter( 'fmwp_change_settings_before_save', array( $this, 'save_email_templates' ) );
			add_filter( 'fmwp_change_settings_before_save', array( $this, 'multi_checkbox_formatting' ) );

			add_action( 'fmwp_settings_save', array( FMWP()->modules(), 'install_modules' ) );

			add_action( 'admin_init', array( $this, 'core_pages' ) );
			add_action( 'admin_init', array( $this, 'check_templates_version' ) );
		}

		/**
		 * Handler for settings forms
		 * when "Save Settings" button click
		 *
		 */
		public function save_settings() {
			if ( ! isset( $_POST['fmwp-settings-action'] ) || 'save' !== $_POST['fmwp-settings-action'] ) {
				return;
			}

			if ( empty( $_POST['fmwp_options'] ) ) {
				return;
			}

			// Check if nonce is not valid.
			check_admin_referer( 'fmwp-settings-nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				// No capabilities to operate with the settings.
				wp_die( esc_html__( 'Security Check', 'forumwp' ), 'forumwp' );
			}

			do_action( 'fmwp_settings_before_save' );
			$settings = apply_filters( 'fmwp_change_settings_before_save', $_POST['fmwp_options'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below in sanitize.

			$settings = FMWP()->options()->sanitize( $settings );

			foreach ( $settings as $key => $value ) {
				FMWP()->options()->update( $key, $value );
			}

			do_action( 'fmwp_settings_save' );

			//redirect after save settings
			$arg = array(
				'page'   => 'forumwp-settings',
				'update' => 'fmwp_settings_updated',
			);
			if ( ! empty( $_GET['tab'] ) ) {
				$arg['tab'] = sanitize_key( $_GET['tab'] );
			}
			if ( ! empty( $_GET['section'] ) ) {
				$arg['section'] = sanitize_key( $_GET['section'] );
			}

			wp_safe_redirect( add_query_arg( $arg, admin_url( 'admin.php' ) ) );
			exit;
		}

		/**
		 * @param array $settings
		 *
		 * @return array
		 */
		public function save_email_templates( $settings ) {
			if ( empty( $settings['fmwp_email_template'] ) ) {
				return $settings;
			}

			$template = $settings['fmwp_email_template'];
			$content  = wp_unslash( $settings[ $template ] );

			$theme_template_path = FMWP()->common()->mail()->get_template_file( 'theme', $template );

			if ( ! file_exists( $theme_template_path ) ) {
				FMWP()->common()->mail()->copy_template( $template );
			}

			// phpcs:disable WordPress.WP.AlternativeFunctions -- for directly fopen, fwrite, fread, fclose functions using
			// @todo make this place with WP_Filesystem
			if ( file_exists( $theme_template_path ) ) {
				$fp     = fopen( $theme_template_path, 'wb' );
				$result = fputs( $fp, $content );
				fclose( $fp );

				if ( false !== $result ) {
					unset( $settings['fmwp_email_template'], $settings[ $template ] );
				}
			}
			// phpcs:enable WordPress.WP.AlternativeFunctions -- for directly fopen, fwrite, fread, fclose functions using

			return $settings;
		}

		/**
		 * @param array $settings
		 *
		 * @return array
		 */
		public function multi_checkbox_formatting( $settings ) {
			$current_tab    = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( sanitize_key( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			$fields = FMWP()->admin()->settings()->get_settings( $current_tab, $current_subtab, true );

			if ( ! $fields ) {
				return $settings;
			}

			foreach ( $settings as $key => &$value ) {
				if ( ! isset( $fields[ $key ]['type'] ) || 'multi_checkbox' !== $fields[ $key ]['type'] ) {
					continue;
				}

				if ( empty( $value ) ) {
					continue;
				}

				$value = array_keys( $value );
			}

			return $settings;
		}

		/**
		 * Core pages installation process
		 */
		public function core_pages() {
			if ( ! empty( $_REQUEST['fmwp_adm_action'] ) && current_user_can( 'manage_options' ) ) {
				switch ( $_REQUEST['fmwp_adm_action'] ) {
					case 'install_core_pages':
						FMWP()->install()->core_pages();

						if ( FMWP()->options()->are_pages_installed() ) {
							FMWP()->admin()->notices()->dismiss( 'wrong_pages' );
						}

						$url = add_query_arg( array( 'page' => 'forumwp-settings' ), admin_url( 'admin.php' ) );
						wp_safe_redirect( $url );
						exit;
				}
			}

			if ( ! empty( $_REQUEST['fmwp_adm_action'] ) ) {
				switch ( $_REQUEST['fmwp_adm_action'] ) {
					case 'clear_reports':
						if ( ! empty( $_GET['post_id'] ) ) {
							$post_id = absint( $_GET['post_id'] );
							check_admin_referer( 'fmwp_clear_reports' . $post_id );

							FMWP()->reports()->clear( $post_id );
							wp_safe_redirect( remove_query_arg( array( '_wpnonce', 'post_id', 'fmwp_adm_action' ) ) );
							exit;
						}

						break;
				}
			}
		}

		/**
		 * Check template version
		 */
		public function check_templates_version() {
			if ( ! empty( $_REQUEST['fmwp_adm_action'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				switch ( $_REQUEST['fmwp_adm_action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
					case 'check_templates_version':
						$templates = FMWP()->admin()->settings()->get_override_templates( true );
						$out_date  = false;
						foreach ( $templates as $template ) {
							if ( 0 === $template['status_code'] ) {
								$out_date = true;
								break;
							}
						}

						if ( false === $out_date ) {
							delete_option( 'fmwp_override_templates_outdated' );
						}

						$url = add_query_arg(
							array(
								'page' => 'forumwp-settings',
								'tab'  => 'override_templates',
							),
							admin_url( 'admin.php' )
						);
						wp_safe_redirect( $url );
						exit;
				}
			}
		}
	}
}
