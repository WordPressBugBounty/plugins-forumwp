<?php
namespace fmwp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\Dependencies' ) ) {

	/**
	 * Class Dependencies
	 *
	 * @package fmwp
	 */
	class Dependencies {

		/**
		 * @var array
		 */
		private static $active_plugins;

		/**
		 * Set active plugins
		 *
		 * @return void
		 */
		private static function init() {
			self::$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}
		}

		/**
		 * Get active plugins class variable
		 *
		 * @return array
		 */
		private static function get_active_plugins() {
			if ( ! self::$active_plugins ) {
				self::init();
			}

			return self::$active_plugins;
		}

		/**
		 * Check if ForumWP - Plus Modules plugin is active.
		 *
		 * @return bool|string
		 */
		public static function forumwp_plus_active_check() {
			$slug           = 'forumwp-plus/forumwp-plus.php';
			$active_plugins = self::get_active_plugins();

			if ( array_key_exists( $slug, $active_plugins ) || in_array( $slug, $active_plugins, true ) ) {
				return __( 'ForumWP 2.0 is not compatible with the "ForumWP - Plus Modules" plugin. Please download the "ForumWP - Pro" plugin from your account page <a href="https://forumwpplugin.com/account" target="_blank">here</a> and install/activate this plugin on your site to replace the Plus Modules plugin.', 'forumwp' );
			}

			return false;
		}

		/**
		 * Check correct folder name for extensions.
		 *
		 * @return bool|string
		 */
		public static function check_folder() {
			$slug           = 'forumwp/forumwp.php';
			$active_plugins = self::get_active_plugins();

			if ( ! array_key_exists( $slug, $active_plugins ) && ! in_array( $slug, $active_plugins, true ) ) {
				// translators: %s is a plugin name.
				return sprintf( __( 'Please check <strong>"%s"</strong> plugin\'s folder name. Correct folder name is <strong>"forumwp"</strong>', 'forumwp' ), FMWP_PLUGIN_NAME );
			}

			return true;
		}
	}
}
