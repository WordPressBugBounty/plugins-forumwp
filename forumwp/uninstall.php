<?php
/**
 * Uninstall ForumWP
 *
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'FMWP_PATH' ) ) {
	define( 'FMWP_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'FMWP_URL' ) ) {
	define( 'FMWP_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'FMWP_PLUGIN' ) ) {
	define( 'FMWP_PLUGIN', plugin_basename( __FILE__ ) );
}

require_once FMWP_PATH . 'includes/class-fmwp-functions.php';
require_once FMWP_PATH . 'includes/class-fmwp.php';

$delete_options = FMWP()->options()->get( 'uninstall-delete-settings' );

if ( ! empty( $delete_options ) ) {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- single time uninstall
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- single time uninstall
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- single time uninstall
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}fmwp_reports" );

	delete_option( 'fmwp_options' );
	delete_option( 'fmwp_last_version_upgrade' );
	delete_option( 'fmwp_first_activation_date' );
	delete_option( 'fmwp_version' );
	delete_option( 'fmwp_flush_rewrite_rules' );
	delete_option( 'fmwp_hidden_admin_notices' );

	$wpdb->query(
		"DELETE *
		FROM {$wpdb->usermeta}
		WHERE meta_key LIKE 'fmwp_%'"
	);

	$wpdb->query(
		"DELETE *
		FROM {$wpdb->options}
		WHERE option_name LIKE 'fmwp_%'"
	);
}
