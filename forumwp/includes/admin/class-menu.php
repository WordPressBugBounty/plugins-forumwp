<?php
namespace fmwp\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\admin\Menu' ) ) {

	/**
	 * Class Menu
	 *
	 * @package fmwp\common
	 */
	class Menu {

		/**
		 * Menu constructor.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'menu' ) );

			add_filter( 'admin_body_class', array( &$this, 'selected_menu' ) );
			add_filter( 'submenu_file', array( &$this, 'wp_admin_submenu_filter' ) );

			add_action( 'admin_init', array( &$this, 'wrong_settings' ), 9999 );
		}

		/**
		 * Creates the plugin's menu
		 *
		 * @since 1.0
		 */
		public function menu() {
			$capability = current_user_can( 'fmwp_see_admin_menu' ) ? 'fmwp_see_admin_menu' : 'manage_options';
			add_menu_page( __( 'Forums', 'forumwp' ), __( 'Forums', 'forumwp' ), $capability, 'forumwp', '', 'dashicons-format-chat', 40 );

			$manage_forums_cap  = current_user_can( 'manage_fmwp_forums' ) ? 'manage_fmwp_forums' : 'manage_options';
			$manage_topics_cap  = current_user_can( 'manage_fmwp_topics' ) ? 'manage_fmwp_topics' : 'manage_options';
			$manage_replies_cap = current_user_can( 'manage_fmwp_replies' ) ? 'manage_fmwp_replies' : 'manage_options';

			$category_capability = current_user_can( 'manage_fmwp_forum_categories' ) ? 'manage_fmwp_forum_categories' : 'manage_options';
			$tags_capability     = current_user_can( 'manage_fmwp_topic_tags' ) ? 'manage_fmwp_topic_tags' : 'manage_options';

			add_submenu_page( 'forumwp', __( 'Dashboard', 'forumwp' ), __( 'Dashboard', 'forumwp' ), $capability, 'forumwp' );

			add_submenu_page( 'forumwp', __( 'Forums', 'forumwp' ), __( 'Forums', 'forumwp' ), $manage_forums_cap, 'edit.php?post_type=fmwp_forum' );
			if ( FMWP()->options()->get( 'forum_categories' ) ) {
				add_submenu_page( 'forumwp', __( 'Forum Categories', 'forumwp' ), __( 'Forum Categories', 'forumwp' ), $category_capability, 'edit-tags.php?taxonomy=fmwp_forum_category&post_type=fmwp_forum' );
			}

			add_submenu_page( 'forumwp', __( 'Topics', 'forumwp' ), __( 'Topics', 'forumwp' ), $manage_topics_cap, 'edit.php?post_type=fmwp_topic' );
			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				add_submenu_page( 'forumwp', __( 'Topic Tags', 'forumwp' ), __( 'Topic Tags', 'forumwp' ), $tags_capability, 'edit-tags.php?taxonomy=fmwp_topic_tag&post_type=fmwp_topic' );
			}

			add_submenu_page( 'forumwp', __( 'Replies', 'forumwp' ), __( 'Replies', 'forumwp' ), $manage_replies_cap, 'edit.php?post_type=fmwp_reply' );

			add_submenu_page( 'forumwp', __( 'Settings', 'forumwp' ), __( 'Settings', 'forumwp' ), 'manage_options', 'forumwp-settings', array( &$this, 'settings' ) );
		}

		/**
		 * Hide first submenu and replace to Forums
		 *
		 * @param string $submenu_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function wp_admin_submenu_filter( $submenu_file ) {
			global $plugin_page;

			$hidden_submenus = array(
				'forumwp',
			);

			// Select another submenu item to highlight (optional).
			if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
				$submenu_file = 'edit.php?post_type=fmwp_forum';
			}

			// Hide the submenu.
			foreach ( $hidden_submenus as $submenu ) {
				remove_submenu_page( 'forumwp', $submenu );
			}

			return $submenu_file;
		}

		/**
		 * Made selected ForumWP menu on Add/Edit CPT and Term Taxonomies
		 *
		 * @param string $classes
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function selected_menu( $classes ) {
			global $submenu, $pagenow;
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $submenu['forumwp'] ) ) {
				if ( isset( $_GET['post_type'] ) ) {
					$post_type = sanitize_key( $_GET['post_type'] );
					if ( in_array( $post_type, array( 'fmwp_forum', 'fmwp_topic', 'fmwp_reply' ), true ) ) {
						add_filter( 'parent_file', array( &$this, 'change_parent_file' ), 200 );
					}
				}

				if ( isset( $_GET['post'] ) ) {
					$post      = absint( $_GET['post'] );
					$post_type = get_post_type( $post );
					if ( 'post.php' === $pagenow && in_array( $post_type, array( 'fmwp_forum', 'fmwp_topic', 'fmwp_reply' ), true ) ) {
						add_filter( 'parent_file', array( &$this, 'change_parent_file' ), 200 );
					}
				}

				add_filter( 'submenu_file', array( &$this, 'change_submenu_file' ), 200, 2 );
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return $classes;
		}

		/**
		 * Return admin submenu variable for display pages
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function change_parent_file() {
			global $pagenow;

			if ( 'edit-tags.php' !== $pagenow && 'term.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- need this action to set proper parent menu.
				$pagenow = 'admin.php';
			}

			return 'forumwp';
		}

		/**
		 * Return admin submenu variable for display pages
		 *
		 * @param string $submenu_file
		 * @param string $parent_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function change_submenu_file( $submenu_file, $parent_file ) {
			global $pagenow;
			// phpcs:disable WordPress.Security.NonceVerification
			if ( 'edit-tags.php' === $pagenow || 'term.php' === $pagenow || 'post-new.php' === $pagenow ) {
				if ( 'forumwp' === $parent_file ) {
					if ( isset( $_GET['post_type'], $_GET['taxonomy'] ) && ( 'fmwp_forum' === $_GET['post_type'] || 'fmwp_topic' === $_GET['post_type'] ) && ( 'fmwp_forum_category' === $_GET['taxonomy'] || 'fmwp_topic_tag' === $_GET['taxonomy'] ) ) {
						$submenu_file = 'edit-tags.php?taxonomy=' . sanitize_key( $_GET['taxonomy'] ) . '&post_type=' . sanitize_key( $_GET['post_type'] );
					} elseif ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && ( 'fmwp_forum' === $_GET['post_type'] || 'fmwp_topic' === $_GET['post_type'] || 'fmwp_reply' === $_GET['post_type'] ) ) {
						$submenu_file = 'edit.php?post_type=' . sanitize_key( $_GET['post_type'] );
					}
					// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- need this action to set proper parent menu.
					$pagenow = 'admin.php';
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return $submenu_file;
		}

		/**
		 * Settings page callback
		 *
		 * @since 1.0
		 */
		public function settings() {
			include_once FMWP()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'settings.php';
		}

		/**
		 * Handle redirect if wrong settings tab is open
		 *
		 * @since 2.0
		 */
		public function wrong_settings() {
			global $pagenow;
			// phpcs:disable WordPress.Security.NonceVerification
			if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'forumwp-settings' === $_GET['page'] ) {
				$current_tab    = empty( $_GET['tab'] ) ? '' : sanitize_key( wp_unslash( $_GET['tab'] ) );
				$current_subtab = empty( $_GET['section'] ) ? '' : sanitize_key( wp_unslash( $_GET['section'] ) );

				$settings_struct = FMWP()->admin()->settings()->get_settings( $current_tab, $current_subtab );
				$custom_section  = FMWP()->admin()->settings()->section_is_custom( $current_tab, $current_subtab );

				if ( ! $custom_section && empty( $settings_struct ) ) {
					wp_safe_redirect( add_query_arg( array( 'page' => 'forumwp-settings' ), admin_url( 'admin.php' ) ) );
					exit;
				}

				// remove extra query arg for Email list table
				$email_key           = empty( $_GET['email'] ) ? '' : sanitize_key( wp_unslash( $_GET['email'] ) );
				$email_notifications = FMWP()->config()->get( 'email_notifications' );

				if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
					if ( ! empty( $_GET['_wp_http_referer'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
						wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI ok
						exit;
					}
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}
	}
}
