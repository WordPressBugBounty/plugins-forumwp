<?php
/**
 * Template for the notifications form wrapper
 *
 * This template can be overridden by copying it to your-theme/forumwp/profile/notifications.php
 *
 * @version 2.1.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = get_current_user_id();
if ( empty( $user_id ) ) {

	esc_html_e( 'Wrong user in query', 'forumwp' );

} else {
	$globally_enabled_emails = FMWP()->common()->mail()->get_public_emails();

	$template_args = array(
		'user_id'         => $user_id,
		'email_templates' => $globally_enabled_emails,
	);

	foreach ( $globally_enabled_emails as $email_key => $enabled_email ) {
		$field_key = 'enabled_' . $email_key . '_notification';
		// phpcs:ignore WordPress.Security.NonceVerification -- early verifying nonce in AJAX handler
		$template_args[ $field_key ] = isset( $_POST[ $field_key ] ) ? (bool) $_POST[ $field_key ] : (bool) get_user_meta( $user_id, 'fmwp_' . $field_key, true );
	}

	FMWP()->get_template_part( 'profile/notifications-form', $template_args );
}
