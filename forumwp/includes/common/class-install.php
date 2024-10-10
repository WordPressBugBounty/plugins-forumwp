<?php
namespace fmwp\common;

use WP_Post;
use WP_Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Install' ) ) {

	/**
	 * Class Install
	 *
	 * @package fmwp\common
	 */
	class Install {

		/**
		 * @var bool
		 */
		private $install_process = false;

		/**
		 * Plugin Activation
		 *
		 * @since 1.0
		 */
		public function activation() {
			$this->install_process = true;

			$this->single_site_activation();
			if ( is_multisite() ) {
				update_network_option( get_current_network_id(), 'fmwp_maybe_network_wide_activation', 1 );
			}

			$this->install_process = false;
		}

		/**
		 * Single site plugin activation handler
		 */
		public function single_site_activation() {
			//first install
			$version = get_option( 'fmwp_version' );
			if ( ! $version ) {
				update_option( 'fmwp_last_version_upgrade', FMWP_VERSION );
				add_option( 'fmwp_first_activation_date', time() );
			}

			if ( FMWP_VERSION !== $version ) {
				update_option( 'fmwp_version', FMWP_VERSION );
			}

			//create custom roles + upgrade capabilities
			FMWP()->options()->set_defaults( FMWP()->config()->get( 'defaults' ) );

			$this->create_roles();

			$this->generate_permalinks();

			$this->db_create();

			FMWP()->common()->rewrite()->reset_rules();
		}

		/**
		 * Check if plugin is network activated make the first installation on all blogs
		 *
		 * @since 1.0
		 */
		public function maybe_network_activation() {
			$maybe_activation = get_network_option( get_current_network_id(), 'fmwp_maybe_network_wide_activation' );

			if ( $maybe_activation ) {

				delete_network_option( get_current_network_id(), 'fmwp_maybe_network_wide_activation' );

				if ( is_plugin_active_for_network( FMWP_PLUGIN ) ) {
					// get all blogs
					$blogs = get_sites();
					if ( ! empty( $blogs ) ) {
						foreach ( $blogs as $blog ) {
							switch_to_blog( $blog->blog_id );
							//make activation script for each sites blog
							$this->single_site_activation();
							restore_current_blog();
						}
					}
				}
			}
		}

		/**
		 * Create ForumWP roles and update capabilities
		 *
		 * @since 2.0
		 */
		public function create_roles() {
			global $wp_roles;

			if ( ! class_exists( 'WP_Roles' ) ) {
				return;
			}

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- only if ! isset
			}

			$all_caps         = FMWP()->config()->get( 'all_caps' );
			$custom_roles     = FMWP()->config()->get( 'custom_roles' );
			$capabilities_map = FMWP()->config()->get( 'capabilities_map' );

			foreach ( $custom_roles as $role_id => $role_title ) {
				$wp_roles->remove_role( $role_id );

				if ( empty( $capabilities_map[ $role_id ] ) ) {
					$capabilities_map[ $role_id ] = array();
				}

				add_role( $role_id, $role_title, $capabilities_map[ $role_id ] );
			}

			foreach ( $capabilities_map as $role_id => $caps ) {
				foreach ( array_diff( $caps, $all_caps ) as $cap ) {
					$wp_roles->remove_cap( $role_id, $cap );
				}

				foreach ( $caps as $cap ) {
					$wp_roles->add_cap( $role_id, $cap );
				}
			}
		}

		/**
		 * Generate user profile slugs for ForumWP Profile page
		 *
		 * @since 2.0
		 */
		public function generate_permalinks() {
			global $wpdb;

			$exist_meta = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'fmwp_permalink'" );
			$exist_meta = ! empty( $exist_meta ) ? $exist_meta : array();

			$display_names = $wpdb->get_results(
				"SELECT u.ID, u.display_name
				FROM {$wpdb->users} u
				LEFT JOIN {$wpdb->usermeta} um ON um.user_id = u.ID AND um.meta_key = 'fmwp_permalink'
				WHERE um.meta_value IS NULL"
			);

			if ( ! empty( $display_names ) ) {
				foreach ( $display_names as $user ) {
					$i         = 1;
					$permalink = urldecode( sanitize_title( $user->display_name ) );
					if ( ! empty( $exist_meta ) ) {
						while ( in_array( $permalink, $exist_meta, true ) ) {
							$permalink = urldecode( sanitize_title( $user->display_name . ' ' . $i ) );
							++$i;
						}
					}

					if ( ! in_array( $permalink, $exist_meta, true ) ) {
						$exist_meta[] = $permalink;
					}

					update_user_meta( $user->ID, 'fmwp_permalink', $permalink );
				}
			}
		}

		/**
		 * Install Core Pages
		 *
		 * @since 1.0
		 * @version 2.0 Added $content_map, added flushing rewrite rules
		 */
		public function core_pages() {
			$flush_rewrite = false;

			$content_map = array(
				'login'            => '[fmwp_login_form /]',
				'register'         => '[fmwp_registration_form /]',
				'profile'          => '[fmwp_user_profile /]',
				'forums'           => '[fmwp_forums /]',
				'topics'           => '[fmwp_topics /]',
				'forum_categories' => '[fmwp_forum_categories /]',
			);

			$content_map = apply_filters( 'fmwp_core_pages_content_map', $content_map );

			foreach ( FMWP()->config()->get( 'core_pages' ) as $slug => $array ) {

				$page_id = FMWP()->options()->get( $slug . '_page' );
				if ( ! empty( $page_id ) ) {
					$page = get_post( $page_id );

					if ( $page instanceof WP_Post ) {
						continue;
					}
				}

				//If page does not exist - create it
				$content = '';
				if ( array_key_exists( $slug, $content_map ) ) {
					$content = $content_map[ $slug ];
				}

				$user_page = array(
					'post_title'     => $array['title'],
					'post_content'   => $content,
					'post_name'      => $slug,
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $user_page );
				if ( empty( $post_id ) || is_wp_error( $post_id ) ) {
					continue;
				}

				FMWP()->options()->update( $slug . '_page', $post_id );

				$flush_rewrite = true;
			}

			if ( $flush_rewrite ) {
				FMWP()->common()->rewrite()->reset_rules();
			}
		}

		/**
		 * @since 1.0
		 */
		public function db_create() {
			global $wpdb;

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$charset_collate = $wpdb->get_charset_collate();

			// specific tables.
			$tables = "CREATE TABLE {$wpdb->prefix}fmwp_reports (
id int(11) NOT NULL auto_increment,
post_id int(11) NOT NULL,
user_id int(11) NOT NULL,
creation_date varchar(25) NULL,
PRIMARY KEY  (id)
) $charset_collate;";

			dbDelta( $tables );
		}
	}
}
