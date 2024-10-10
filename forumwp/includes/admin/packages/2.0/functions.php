<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @return bool
 */
function fmwp_topic_stop_update_last_date() {
	return false;
}

/**
 *
 * @return bool
 */
function fmwp_forum_stop_update_last_date() {
	return false;
}

/**
 *
 */
function fmwp_upgrade_solved20() {
	FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- for silenced set_time_limit
	@set_time_limit( 0 );

	include 'solved.php';

	wp_send_json_success( array( 'message' => __( 'Solved posts were upgraded successfully', 'forumwp' ) ) );
}

/**
 *
 */
function fmwp_upgrade_locked20() {
	FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- for silenced set_time_limit
	@set_time_limit( 0 );

	include 'locked.php';

	wp_send_json_success( array( 'message' => __( 'Locked forums were upgraded successfully', 'forumwp' ) ) );
}

/**
 *
 */
function fmwp_upgrade_subscriptions20() {
	FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- for silenced set_time_limit
	@set_time_limit( 0 );

	include 'subscriptions.php';

	update_option( 'fmwp_last_version_upgrade', '2.0' );

	wp_send_json_success( array( 'message' => __( 'Subscriptions were upgraded successfully', 'forumwp' ) ) );
}
