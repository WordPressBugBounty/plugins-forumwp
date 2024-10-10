<?php
namespace fmwp\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\ajax\Forms' ) ) {

	/**
	 * Class Forms
	 *
	 * @package fmwp\ajax
	 */
	class Forms {

		/**
		 *
		 */
		public function get_icons() {
			check_ajax_referer( 'fmwp-backend-nonce', 'nonce' );

			$response = wp_remote_get( FMWP_URL . 'assets/common/libs/fontawesome/metadata/icons.json' );

			if ( is_array( $response ) ) {
				$result = json_decode( $response['body'] );
				wp_send_json_success( $result );
			}

			wp_send_json_error();
		}
	}
}
