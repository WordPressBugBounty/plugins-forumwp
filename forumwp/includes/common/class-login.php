<?php
namespace fmwp\common;

use WP_Error;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Login' ) ) {

	/**
	 * Class Login
	 *
	 * @package fmwp\common
	 */
	class Login {

		/**
		 * Login constructor.
		 *
		 * @since 2.0
		 */
		public function __construct() {
			//filter login/logout URLs
			add_action( 'wp_logout', array( &$this, 'logout' ) );
			add_filter( 'logout_url', array( &$this, 'logout_url' ), 10, 2 );
			add_action( 'wp_login_failed', array( &$this, 'login_failed' ) );

			add_filter( 'authenticate', array( &$this, 'verify_username_password' ), 1, 3 );
			add_action( 'template_redirect', array( &$this, 'custom_logout_handler' ), 1 );

			add_filter( 'login_redirect', array( &$this, 'add_fallback_url' ) );
		}

		public function add_fallback_url( $redirect_to ) {
			$ref = wp_get_raw_referer();
			if ( ! $ref ) {
				return $redirect_to;
			}

			$postid = url_to_postid( $ref );

			if ( empty( $postid ) || FMWP()->common()->get_preset_page_id( 'login' ) !== $postid ) {
				return $redirect_to;
			}

			add_filter(
				'wp_safe_redirect_fallback',
				static function () {
					return FMWP()->common()->get_preset_page_link( 'profile' );
				}
			);

			return $redirect_to;
		}

		/**
		 * On logout action
		 */
		public function logout() {
			$login_url       = FMWP()->common()->get_preset_page_link( 'login' );
			$logout_redirect = FMWP()->options()->get( 'logout_redirect' );

			// if empty 'logout_redirect' option then redirect to login page
			$baseurl = ! empty( $logout_redirect ) ? $logout_redirect : $login_url;

			$redirect_url = add_query_arg( array( 'logout' => 'success' ), $baseurl );

			$redirect_url = apply_filters( 'fmwp_logout_redirect_url', $redirect_url, $baseurl );

			add_filter(
				'wp_safe_redirect_fallback',
				static function () {
					return FMWP()->common()->get_preset_page_link( 'login' );
				}
			);

			wp_safe_redirect( $redirect_url );
			exit;
		}

		/**
		 * Change logout URL
		 *
		 * @param $logout_url
		 * @param $redirect
		 *
		 * @return string
		 */
		public function logout_url( $logout_url, $redirect ) {
			$args = array( 'action' => 'logout' );
			if ( ! empty( $redirect ) ) {
				$args['redirect_to'] = rawurlencode( $redirect );
			}

			return wp_nonce_url( add_query_arg( $args, FMWP()->common()->get_preset_page_link( 'login' ) ), 'log-out' );
		}

		/**
		 * Redirects visitor to the login page with login
		 * failed status.
		 *
		 * @return void
		 */
		public function login_failed() {
			$ref = wp_get_raw_referer();
			if ( ! $ref ) {
				return;
			}

			$postid = url_to_postid( $ref );

			if ( empty( $postid ) || FMWP()->common()->get_preset_page_id( 'login' ) !== $postid ) {
				return;
			}

			$logout_link = add_query_arg( array( 'login' => 'failed' ), FMWP()->common()->get_preset_page_link( 'login' ) );
			wp_safe_redirect( $logout_link );
			exit;
		}

		/**
		 * Verifies username and password. Redirects visitor
		 * to the login page with login empty status if
		 * eather username or password is empty.
		 *
		 * @param mixed $user
		 * @param string $username
		 * @param string $password
		 *
		 * @return WP_Error
		 */
		public function verify_username_password( $user, $username, $password ) {
			$ref = wp_get_raw_referer();
			if ( ! $ref ) {
				return $user;
			}

			$postid = url_to_postid( $ref );

			if ( empty( $postid ) || FMWP()->common()->get_preset_page_id( 'login' ) !== $postid ) {
				return $user;
			}

			if ( null === $user && ( '' === $username || '' === $password ) ) {
				return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid username, email address or incorrect password.' ) );
			}

			return $user;
		}

		/**
		 *
		 */
		public function custom_logout_handler() {
			if ( ! FMWP()->is_core_page( 'login' ) ) {
				return;
			}

			if ( isset( $_GET['action'] ) && 'logout' === sanitize_key( $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- checking if logout here
				if ( is_user_logged_in() ) {
					check_admin_referer( 'log-out' );

					$user = wp_get_current_user();

					wp_logout();

					if ( ! empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to           = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) );
						$requested_redirect_to = $redirect_to;
					} else {
						$redirect_to           = 'wp-login.php?loggedout=true';
						$requested_redirect_to = '';
					}

					/**
					 * Filters the log-out redirect URL.
					 *
					 * @since 4.2.0
					 *
					 * @param string  $redirect_to           The redirect destination URL.
					 * @param string  $requested_redirect_to The requested redirect destination URL passed as a parameter.
					 * @param WP_User $user                  The WP_User object for the user that's logging out.
					 */
					$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $user );
					wp_safe_redirect( $redirect_to );
					exit;
				}

				wp_safe_redirect( FMWP()->common()->get_preset_page_link( 'login' ) );
				exit;
			}
		}
	}
}
