<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @return bool
 */
function fmwp_topic_stop_update_last_date201() {
	return false;
}

/**
 *
 * @return bool
 */
function fmwp_forum_stop_update_last_date201() {
	return false;
}

/**
 *
 */
function fmwp_upgrade_spam201() {
	FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- for silenced set_time_limit
	@set_time_limit( 0 );

	include 'spam.php';

	update_option( 'fmwp_last_version_upgrade', '2.0.1' );

	wp_send_json_success( array( 'message' => __( 'Spam posts were upgraded successfully', 'forumwp' ) ) );
}
