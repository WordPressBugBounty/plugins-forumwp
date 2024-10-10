<?php
namespace fmwp\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\ajax\Profile' ) ) {

	/**
	 * Class Profile
	 *
	 * @package fmwp\ajax
	 */
	class Profile {

		/**
		 *
		 */
		public function get_tab_content() {
			check_ajax_referer( 'fmwp-frontend-nonce', 'nonce' );

			if ( empty( $_POST['tab'] ) ) {
				wp_send_json_error( __( 'Invalid Tab', 'forumwp' ) );
			}
			if ( empty( $_POST['user_id'] ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$tabs = array_keys( FMWP()->frontend()->profile()->tabs_list() );

			$tab = sanitize_key( $_POST['tab'] );
			if ( ! in_array( $tab, $tabs, true ) ) {
				wp_send_json_error( __( 'Invalid Tab', 'forumwp' ) );
			}

			$user = get_userdata( absint( $_POST['user_id'] ) );
			if ( empty( $user ) || is_wp_error( $user ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$tab_data = array();
			if ( method_exists( FMWP()->frontend()->profile(), 'get_' . $tab . '_tab_data' ) ) {
				$tab_data = call_user_func( array( FMWP()->frontend()->profile(), 'get_' . $tab . '_tab_data' ), $user );
			}

			wp_send_json_success( $tab_data );
		}

		/**
		 *
		 */
		public function get_profile_topics() {
			check_ajax_referer( 'fmwp-frontend-nonce', 'nonce' );

			if ( empty( $_POST['user_id'] ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$user = get_userdata( absint( $_POST['user_id'] ) );
			if ( empty( $user ) || is_wp_error( $user ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$topics = FMWP()->common()->topic()->get_topics_by_author(
				$user->ID,
				array(
					'paged'          => ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : '1',
					'posts_per_page' => FMWP()->options()->get_variable( 'topics_per_page' ),
				)
			);

			$data = array();
			foreach ( $topics as $topic ) {
				$data[] = FMWP()->ajax()->topic()->response_data( $topic );
			}

			wp_send_json_success( $data );
		}

		/**
		 *
		 */
		public function get_profile_replies() {
			check_ajax_referer( 'fmwp-frontend-nonce', 'nonce' );

			if ( empty( $_POST['user_id'] ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$user = get_userdata( absint( $_POST['user_id'] ) );
			if ( empty( $user ) || is_wp_error( $user ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$replies = FMWP()->common()->reply()->get_replies_by_author(
				$user->ID,
				array(
					'paged'          => ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : '1',
					'posts_per_page' => FMWP()->options()->get_variable( 'replies_per_page' ),
				)
			);

			$data = array();
			foreach ( $replies as $reply ) {
				$data[] = FMWP()->ajax()->reply()->response_data( $reply );
			}

			wp_send_json_success( $data );
		}
	}
}
