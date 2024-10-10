<?php
namespace fmwp\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\admin\Enqueue' ) ) {

	/**
	 * Class Enqueue
	 *
	 * @package fmwp\admin
	 */
	final class Enqueue extends \fmwp\common\Enqueue {

		/**
		 * Enqueue constructor.
		 */
		public function __construct() {
			parent::__construct();

			add_action( 'forumwp_init', array( $this, 'extends_variables' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );
		}

		/**
		 *
		 */
		public function extends_variables() {
			$this->url['admin']     = FMWP_URL . 'assets/admin/';
			$this->js_url['admin']  = FMWP_URL . 'assets/admin/js/';
			$this->css_url['admin'] = FMWP_URL . 'assets/admin/css/';
		}

		/**
		 * ForumWP wp-admin assets registration
		 */
		public function admin_scripts() {
			wp_register_script( 'select2', $this->url['common'] . 'libs/select2/js/select2.full' . $this->scripts_prefix . '.js', array(), FMWP_VERSION, true );
			wp_register_script( 'fmwp-global', $this->js_url['admin'] . 'global' . $this->scripts_prefix . '.js', array( 'jquery', 'wp-util' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-common', $this->js_url['admin'] . 'common' . $this->scripts_prefix . '.js', array( 'jquery', 'wp-color-picker' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-forms', $this->js_url['admin'] . 'forms' . $this->scripts_prefix . '.js', array( 'jquery', 'wp-util', 'fmwp-helptip', 'select2' ), FMWP_VERSION, true );

			$localize_data = apply_filters(
				'fmwp_admin_enqueue_localize',
				array(
					'nonce' => wp_create_nonce( 'fmwp-backend-nonce' ),
				)
			);

			wp_localize_script( 'fmwp-global', 'fmwp_admin_data', $localize_data );
			wp_enqueue_script( 'fmwp-global' );

			wp_register_style( 'select2', $this->url['common'] . 'libs/select2/css/select2' . $this->scripts_prefix . '.css', array(), FMWP_VERSION );

			$common_admin_deps = apply_filters( 'fmwp_admin_common_styles_deps', array( 'wp-color-picker', 'fmwp-helptip', 'select2' ) );

			wp_register_style( 'fmwp-common', $this->css_url['admin'] . 'common' . $this->scripts_prefix . '.css', $common_admin_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-forms', $this->css_url['admin'] . 'forms' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );

			if ( FMWP()->admin()->is_own_screen() ) {
				wp_enqueue_script( 'fmwp-common' );
				wp_enqueue_script( 'fmwp-forms' );

				wp_enqueue_style( 'fmwp-common' );
				wp_enqueue_style( 'fmwp-forms' );
			}
		}
	}
}
