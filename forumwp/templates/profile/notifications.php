<?php
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
		$globally_enabled_emails = FMWP()->common()->mail()->get_public_emails();

		$template_args = array(
			'user_id'         => $user->ID,
			'email_templates' => $globally_enabled_emails,
		);

		foreach ( $globally_enabled_emails as $email_key => $enabled_email ) {
			$field_key = 'enabled_' . $email_key . '_notification';
			// phpcs:ignore WordPress.Security.NonceVerification -- early verifying nonce in AJAX handler
			$template_args[ $field_key ] = isset( $_POST[ $field_key ] ) ? (bool) $_POST[ $field_key ] : (bool) get_user_meta( $user->ID, 'fmwp_' . $field_key, true );
		}

		FMWP()->get_template_part( 'profile/notifications-form', $template_args );
	}
}
