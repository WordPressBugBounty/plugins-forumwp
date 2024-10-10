<?php
namespace fmwp\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\ajax\User' ) ) {

	/**
	 * Class User
	 *
	 * @package fmwp\ajax
	 */
	class User {

		/**
		 * @return void
		 */
		public function get_suggestions() {
			check_ajax_referer( 'wp_rest' );

			$data = array();

			if ( empty( $_GET['term'] ) ) {
				wp_send_json( $data );
			}

			$term = sanitize_text_field( wp_unslash( $_GET['term'] ) );
			$term = str_replace( '@', '', $term );

			if ( empty( $term ) ) {
				wp_send_json( $data );
			}

			if ( current_user_can( 'manage_options' ) ) {
				// Possible to mention all or everyone from Administrator.
				$lower_term = strtolower( $term );

				if ( 'everyone' === $lower_term || similar_text( $lower_term, 'everyone' ) === strlen( $term ) ) {
					$data[0]['list_item']    = '<strong>' . __( 'Everyone', 'forumwp' ) . '</strong> (@everyone)';
					$data[0]['replace_item'] = '@everyone';
				}

				if ( 'all' === $lower_term || similar_text( $lower_term, 'all' ) === strlen( $term ) ) {
					$data[0]['list_item']    = '<strong>' . __( 'All', 'forumwp' ) . '</strong> (@all)';
					$data[0]['replace_item'] = '@all';
				}
			}

			// Getting only users who have published posts. Related to the security.
			$response = wp_remote_get(
				add_query_arg(
					array(
						'search' => $term,
					),
					get_site_url( get_current_blog_id(), '/wp-json/wp/v2/users' )
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$response = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if ( empty( $response ) ) {
				wp_send_json( $data );
			}

			foreach ( $response as $user ) {
				$user_slug                         = get_user_meta( $user->id, 'fmwp_permalink', true );
				$data[ $user->id ]['list_item']    = FMWP()->user()->get_avatar( $user->id, 'inline', '24' ) . '<strong>' . $user->name . '</strong> (@' . $user_slug . ')';
				$data[ $user->id ]['replace_item'] = '@' . $user_slug;
			}

			$data = array_unique( $data, SORT_REGULAR );

			wp_send_json( $data );
		}
	}
}
