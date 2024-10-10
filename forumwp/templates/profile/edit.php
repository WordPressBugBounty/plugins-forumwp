<?php
/**
 * Template for the topics list
 *
 * This template can be overridden by copying it to your-theme/forumwp/profile/edit.php
 *
 * @version 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_slug = get_query_var( 'fmwp_user' );

$user_id = FMWP()->user()->get_user_by_permalink( urldecode( $user_slug ) );
if ( empty( $user_id ) ) {

	esc_html_e( 'Wrong user in query', 'forumwp' );

} else {
	$user = get_userdata( $user_id );

	if ( empty( $user ) || is_wp_error( $user ) ) {

		esc_html_e( 'Wrong user in query', 'forumwp' );

	} else {
		// phpcs:disable WordPress.Security.NonceVerification
		$login      = ! empty( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : $user->user_login;
		$email      = ! empty( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : $user->user_email;
		$first_name = ! empty( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : $user->first_name;
		$last_name  = ! empty( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : $user->last_name;

		$url = ! empty( $_POST['user_url'] ) ? filter_var( wp_unslash( $_POST['user_url'] ), FILTER_VALIDATE_URL ) : $user->user_url;
		$url = false === $url ? $user->user_url : $url;

		$user_description = ! empty( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : $user->description;
		// phpcs:enable WordPress.Security.NonceVerification

		FMWP()->get_template_part(
			'profile/edit-form',
			array(
				'user_id'     => $user->ID,
				'user_login'  => $login,
				'user_email'  => $email,
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'user_url'    => $url,
				'description' => $user_description,
			)
		);
	}
}
