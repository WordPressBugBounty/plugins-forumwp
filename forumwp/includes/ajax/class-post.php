<?php
namespace fmwp\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\ajax\Post' ) ) {

	/**
	 * Class Post
	 *
	 * @package fmwp\ajax
	 */
	class Post {

		/**
		 * Build preview via AJAX request
		 */
		public function build_preview() {
			check_ajax_referer( 'fmwp-frontend-nonce', 'nonce' );

			if ( empty( $_REQUEST['content'] ) ) {
				wp_send_json_success( '' );
			}

			if ( isset( $_REQUEST['action'] ) ) {
				$content = '';

				// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- need to prepare variable after WP_Editor
				$_REQUEST['content'] = html_entity_decode( $_REQUEST['content'] ); // required because WP_Editor send encoded content.
				// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- need to prepare variable after WP_Editor

				if ( FMWP()->options()->get( 'raw_html_enabled' ) ) {
					$request_content = wp_kses_post( wp_unslash( $_REQUEST['content'] ) );
				} else {
					$request_content = sanitize_textarea_field( wp_unslash( $_REQUEST['content'] ) );
				}

				switch ( $_REQUEST['action'] ) {
					case 'fmwp_topic_build_preview':
						list( $origin_content, $content ) = FMWP()->common()->post()->prepare_content( $request_content, 'fmwp_topic' );
						break;
					case 'fmwp_reply_build_preview':
						list( $origin_content, $content ) = FMWP()->common()->post()->prepare_content( $request_content, 'fmwp_reply' );
						break;
				}

				wp_send_json_success( nl2br( $content ) );
			}

			wp_send_json_error( __( 'Wrong request', 'forumwp' ) );
		}
	}
}
