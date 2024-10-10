<?php
namespace fmwp\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Rewrite' ) ) {

	/**
	 * Class Rewrite
	 *
	 * @package fmwp\common
	 */
	class Rewrite {

		/**
		 * Rewrite constructor.
		 */
		public function __construct() {
			if ( ! defined( 'DOING_AJAX' ) ) {
				add_filter( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );
			}

			add_filter( 'query_vars', array( &$this, 'query_vars' ) );
			add_filter( 'rewrite_rules_array', array( &$this, 'rewrite_rules' ) );

			//empty profile redirect to current user
			add_action( 'template_redirect', array( &$this, 'locate_user_profile' ), 9999 );
		}

		/**
		 * Update "flush" option for reset rules on wp_loaded hook
		 */
		public function reset_rules() {
			update_option( 'fmwp_flush_rewrite_rules', 1 );
		}

		/**
		 * Reset Rewrite rules if need it.
		 *
		 * @return void
		 */
		public function maybe_flush_rewrite_rules() {
			if ( get_option( 'fmwp_flush_rewrite_rules' ) ) {
				flush_rewrite_rules( false );
				delete_option( 'fmwp_flush_rewrite_rules' );
			}
		}

		/**
		 * Modify global query vars
		 *
		 * @param array $query_vars
		 *
		 * @return array
		 */
		public function query_vars( $query_vars ) {
			$query_vars = array_merge(
				$query_vars,
				array(
					'fmwp_user',
					'fmwp_profiletab',
					'fmwp_profilesubtab',
				)
			);

			return $query_vars;
		}

		/**
		 * Add custom rewrite rules
		 *
		 * @param $rules
		 *
		 * @return array
		 */
		public function rewrite_rules( $rules ) {
			$newrules = array();

			$profile_page_id = FMWP()->common()->get_preset_page_id( 'profile' );
			if ( ! empty( $profile_page_id ) ) {
				$profile_page = get_post( $profile_page_id );
				if ( isset( $profile_page->post_name ) ) {
					$user_slug = $profile_page->post_name;

					$newrules[ $user_slug . '/([^/]+)/?$' ]                 = 'index.php?page_id=' . $profile_page_id . '&fmwp_user=$matches[1]';
					$newrules[ $user_slug . '/([^/]+)/([^/]+)/?$' ]         = 'index.php?page_id=' . $profile_page_id . '&fmwp_user=$matches[1]&fmwp_profiletab=$matches[2]';
					$newrules[ $user_slug . '/([^/]+)/([^/]+)/([^/]+)/?$' ] = 'index.php?page_id=' . $profile_page_id . '&fmwp_user=$matches[1]&fmwp_profiletab=$matches[2]&fmwp_profilesubtab=$matches[3]';
				}
			}

			return $newrules + $rules;
		}

		/**
		 * Redirect to current user if empty query args
		 */
		public function locate_user_profile() {
			// phpcs:ignore WordPress.Security.NonceVerification -- checking Fusion builder here
			if ( isset( $_GET['fl_builder'] ) || ! $this->is_core_page( 'profile' ) ) {
				return;
			}

			$user_login = get_query_var( 'fmwp_user' );

			if ( ! empty( $user_login ) ) {
				$user_id = FMWP()->user()->get_user_by_permalink( $user_login );
				$user    = get_user_by( 'ID', $user_id );
				if ( empty( $user ) || is_wp_error( $user ) ) {
					//set 404 is user isn't logged in and user query var is empty
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					return;
				}

				$menu_items = FMWP()->frontend()->profile()->get_profile_tabs( $user );
				if ( empty( $menu_items ) ) {
					//set 404 is user isn't logged in and user query var is empty
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					return;
				}

				$menus_keys = array_keys( $menu_items );

				$active_tab = get_query_var( 'fmwp_profiletab' );
				$active_tab = empty( $active_tab ) ? $menus_keys[0] : $active_tab;
				if ( ! in_array( $active_tab, $menus_keys, true ) ) {
					//set 404 is user isn't logged in and user query var is empty
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					return;
				}

				return;
			}

			if ( ! is_user_logged_in() ) {
				$login_link = FMWP()->common()->get_preset_page_link( 'login' );
				$login_link = add_query_arg( array( 'redirect_to' => urldecode( FMWP()->get_current_url() ) ), $login_link );

				wp_safe_redirect( $login_link );
				exit;
			}

			$profile_link = FMWP()->user()->get_profile_link( get_current_user_id() );

			wp_safe_redirect( $profile_link );
			exit;
		}

		/**
		 * Check if we are on a FMWP Core Page or not
		 *
		 *'login', 'register', 'profile', 'forums', 'topics'
		 *
		 * @param string $slug Page slug
		 *
		 * @return bool
		 */
		public function is_core_page( $slug ) {
			global $post;

			if ( empty( $post ) ) {
				return false;
			}

			$preset_page_id = FMWP()->common()->get_preset_page_id( $slug );

			return isset( $post->ID ) && ! empty( $preset_page_id ) && $post->ID === $preset_page_id;
		}
	}
}
