<?php
namespace fmwp\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\ajax\Notices' ) ) {

	/**
	 * Class Notices
	 *
	 * @package fmwp\ajax
	 */
	class Notices {

		/**
		 * AJAX dismiss notices
		 */
		public function dismiss_notice() {
			check_ajax_referer( 'fmwp-backend-nonce', 'nonce' );

			if ( empty( $_POST['key'] ) ) {
				wp_send_json_error( __( 'Wrong Data', 'forumwp' ) );
			}

			FMWP()->admin()->notices()->dismiss( sanitize_key( $_POST['key'] ) );
			wp_send_json_success();
		}
	}
}
