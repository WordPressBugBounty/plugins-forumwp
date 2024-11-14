<?php
/**
 * Plugin Name: ForumWP
 * Plugin URI: https://forumwpplugin.com/
 * Description: A full-featured, powerful forum plugin for WordPress
 * Version: 2.1.2
 * Author: ForumWP
 * Text Domain: forumwp
 * Domain Path: /languages
 * Requires at least: 5.5
 * Requires PHP: 7.0
 *
 * @package FMWP
 */
defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugin_data = get_plugin_data( __FILE__, true, false );

// phpcs:disable Generic.NamingConventions.UpperCaseConstantName
define( 'fmwp_url', plugin_dir_url( __FILE__ ) );
define( 'fmwp_path', plugin_dir_path( __FILE__ ) );
define( 'fmwp_plugin', plugin_basename( __FILE__ ) );
define( 'fmwp_author', $plugin_data['AuthorName'] );
define( 'fmwp_version', $plugin_data['Version'] );
define( 'fmwp_plugin_name', $plugin_data['Name'] );
// phpcs:enable Generic.NamingConventions.UpperCaseConstantName

define( 'FMWP_URL', plugin_dir_url( __FILE__ ) );
define( 'FMWP_PATH', plugin_dir_path( __FILE__ ) );
define( 'FMWP_PLUGIN', plugin_basename( __FILE__ ) );
define( 'FMWP_AUTHOR', $plugin_data['AuthorName'] );
define( 'FMWP_VERSION', $plugin_data['Version'] );
define( 'FMWP_PLUGIN_NAME', $plugin_data['Name'] );

if ( ! function_exists( 'fmwp_check_dependencies' ) ) {
	/**
	 * @return void
	 */
	function fmwp_check_dependencies() {
		require_once FMWP_PATH . 'includes/class-dependencies.php';

		$check_folder_result = fmwp\Dependencies::check_folder();
		if ( true !== $check_folder_result ) {
			add_action(
				'admin_notices',
				static function () use ( $check_folder_result ) {
					$allowed_html = array(
						'a'      => array(
							'href' => array(),
						),
						'strong' => array(),
					);
					ob_start();
					?>
					<div class="error">
						<p><?php echo wp_kses( $check_folder_result, $allowed_html ); ?></p>
					</div>
					<?php
					ob_get_flush();
				}
			);

		} else {
			$check_plus_result = fmwp\Dependencies::forumwp_plus_active_check();
			if ( false !== $check_plus_result ) {
				add_action(
					'admin_notices',
					static function () use ( $check_plus_result ) {
						$allowed_html = array(
							'a'      => array(
								'href' => array(),
							),
							'strong' => array(),
						);
						ob_start();
						?>
						<div class="error">
							<p><?php echo wp_kses( $check_plus_result, $allowed_html ); ?></p>
						</div>
						<?php
						ob_get_flush();
					}
				);

			} else {
				require_once 'includes/class-fmwp-functions.php';
				require_once 'includes/class-fmwp.php';

				FMWP();
			}
		}
	}
}
add_action( 'plugins_loaded', 'fmwp_check_dependencies', -21 );


if ( ! function_exists( 'fmwp_activation' ) ) {
	/**
	 * @return void
	 */
	function fmwp_activation() {
		require_once 'includes/class-fmwp-functions.php';
		require_once 'includes/class-fmwp.php';

		FMWP()->install()->activation();
	}
}
register_activation_hook( FMWP_PLUGIN, 'fmwp_activation' );

if ( ! function_exists( 'fmwp_maybe_network_activation' ) ) {
	/**
	 * @return void
	 */
	function fmwp_maybe_network_activation() {
		require_once 'includes/class-fmwp-functions.php';
		require_once 'includes/class-fmwp.php';

		FMWP()->install()->maybe_network_activation();
	}
}
if ( ! defined( 'DOING_AJAX' ) && is_multisite() ) {
	add_action( 'wp_loaded', 'fmwp_maybe_network_activation' );
}
