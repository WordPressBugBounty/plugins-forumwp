<?php
namespace fmwp\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\frontend\Enqueue' ) ) {

	/**
	 * Class Enqueue
	 *
	 * @package fmwp\frontend
	 */
	final class Enqueue extends \fmwp\common\Enqueue {

		/**
		 * Enqueue constructor.
		 */
		public function __construct() {
			parent::__construct();

			add_action( 'forumwp_init', array( $this, 'extends_variables' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ) );
		}

		/**
		 *
		 */
		public function extends_variables() {
			$this->url['frontend']     = FMWP_URL . 'assets/front/';
			$this->js_url['frontend']  = FMWP_URL . 'assets/front/js/';
			$this->css_url['frontend'] = FMWP_URL . 'assets/front/css/';
		}

		/**
		 * ForumWP frontend assets registration
		 */
		public function register_scripts() {

			wp_register_script( 'fmwp-tipsy', $this->url['common'] . 'libs/tipsy/js/tipsy' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-tooltip', $this->js_url['frontend'] . 'tooltip' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-dropdown', $this->js_url['frontend'] . 'dropdown' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-notice', $this->js_url['frontend'] . 'notice' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-front-global', $this->js_url['frontend'] . 'global' . $this->scripts_prefix . '.js', array( 'jquery', 'wp-util', 'fmwp-tipsy', 'fmwp-notice', 'fmwp-tooltip', 'wp-i18n', 'fmwp-helptip', 'wp-hooks' ), FMWP_VERSION, true );

			wp_set_script_translations( 'fmwp-front-global', 'forumwp' );

			$localize_data = apply_filters(
				'fmwp_enqueue_localize',
				array(
					'can_reply' => is_user_logged_in() && current_user_can( 'fmwp_post_reply' ),
					'can_topic' => is_user_logged_in() && current_user_can( 'fmwp_post_topic' ),
					'nonce'     => wp_create_nonce( 'fmwp-frontend-nonce' ),
				)
			);
			wp_localize_script( 'fmwp-front-global', 'fmwp_front_data', $localize_data );

			wp_register_script( 'fmwp-popup-general', $this->js_url['frontend'] . 'popup' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'wp-api' ), FMWP_VERSION, true );

			if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
				global $post;

				if ( FMWP()->options()->get( 'ajax_increment_views' ) ) {
					if ( ! wp_is_post_revision( $post ) && is_singular( 'fmwp_topic' ) && ! is_preview() ) {
						$auth = FMWP()->common()->topic()->get_auth( $post->ID );
						wp_enqueue_script( 'fmwp-topic-views-cache', $this->js_url['frontend'] . 'topic-views-cache' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );
						wp_localize_script(
							'fmwp-topic-views-cache',
							'fmwp_topic_views',
							array(
								'post_id' => $post->ID,
								'auth_id' => $auth,
							)
						);
					}
				}
			}

			if ( ! is_user_logged_in() ) {
				wp_register_script( 'fmwp-unlogged-user', $this->js_url['frontend'] . 'not-logged-in-user' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );
			}

			if ( is_user_logged_in() ) {
				wp_register_script( 'fmwp-forums-logged-in', $this->js_url['frontend'] . 'logged-in-forums' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown' ), FMWP_VERSION, true );
			}

			$forums_deps = array( 'fmwp-front-global' );
			if ( is_user_logged_in() ) {
				$forums_deps[] = 'fmwp-forums-logged-in';
			} else {
				$forums_deps[] = 'fmwp-unlogged-user';
			}
			wp_register_script( 'fmwp-forums-list', $this->js_url['frontend'] . 'forums-list' . $this->scripts_prefix . '.js', $forums_deps, FMWP_VERSION, true );

			if ( is_user_logged_in() ) {
				wp_register_script( 'fmwp-topics-logged-in', $this->js_url['frontend'] . 'logged-in-topics' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown' ), FMWP_VERSION, true );
			}

			$topics_deps = array( 'fmwp-front-global' );
			if ( is_user_logged_in() ) {
				$topics_deps[] = 'fmwp-topics-logged-in';
			} else {
				$topics_deps[] = 'fmwp-unlogged-user';
			}
			wp_register_script( 'fmwp-topics-list', $this->js_url['frontend'] . 'topics-list' . $this->scripts_prefix . '.js', $topics_deps, FMWP_VERSION, true );

			wp_register_script( 'fmwp-forum-categories-list', $this->js_url['frontend'] . 'forum-categories-list' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-topic', $this->js_url['frontend'] . 'topic' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown', 'fmwp-popup-general' ), FMWP_VERSION, true );

			if ( is_user_logged_in() ) {
				wp_register_script( 'fmwp-topic-logged', $this->js_url['frontend'] . 'logged-in-topic' . $this->scripts_prefix . '.js', array( 'fmwp-topic' ), FMWP_VERSION, true );
			}

			wp_register_script( 'fmwp-new-forum', $this->js_url['frontend'] . 'new-forum' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'suggest' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-forum', $this->js_url['frontend'] . 'forum' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'suggest', 'fmwp-dropdown' ), FMWP_VERSION, true );

			if ( is_user_logged_in() ) {
				wp_register_script( 'fmwp-forum-logged', $this->js_url['frontend'] . 'logged-in-forum' . $this->scripts_prefix . '.js', array( 'fmwp-forum' ), FMWP_VERSION, true );
			}

			wp_register_script( 'fmwp-reply-popup', $this->js_url['frontend'] . 'reply-popup' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-popup-general', 'jquery-ui-autocomplete' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-topic-popup', $this->js_url['frontend'] . 'topic-popup' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-popup-general', 'suggest', 'jquery-ui-autocomplete' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-profile', $this->js_url['frontend'] . 'profile' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );

			wp_register_script( 'fmwp-user-topics', $this->js_url['frontend'] . 'user-topics' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-user-replies', $this->js_url['frontend'] . 'user-replies' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );

			wp_register_style( 'fmwp-tipsy', $this->url['common'] . 'libs/tipsy/css/tipsy' . $this->scripts_prefix . '.css', array(), FMWP_VERSION );

			$common_frontend_deps = apply_filters( 'fmwp_frontend_common_styles_deps', array( 'fmwp-tipsy', 'fmwp-helptip' ) );
			wp_register_style( 'fmwp-common', $this->css_url['frontend'] . 'common' . $this->scripts_prefix . '.css', $common_frontend_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-forms', $this->css_url['frontend'] . 'forms' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );

			wp_register_style( 'fmwp-breadcrumbs', $this->css_url['frontend'] . 'breadcrumbs' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );

			$breadcrumb_enabled = FMWP()->options()->get( 'breadcrumb_enabled' );
			$forums_css_deps    = array( 'fmwp-common' );
			$topics_css_deps    = array( 'fmwp-common' );
			$forum_css_deps     = array( 'fmwp-common' );
			$topic_css_deps     = array( 'fmwp-common' );
			if ( $breadcrumb_enabled ) {
				$forums_css_deps[] = 'fmwp-breadcrumbs';
				$topics_css_deps[] = 'fmwp-breadcrumbs';
				$forum_css_deps[]  = 'fmwp-breadcrumbs';
				$topic_css_deps[]  = 'fmwp-breadcrumbs';
			}

			wp_register_style( 'fmwp-forum-categories-list', $this->css_url['frontend'] . 'forum-categories-list' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );

			wp_register_style( 'fmwp-forums-list', $this->css_url['frontend'] . 'forums-list' . $this->scripts_prefix . '.css', $forums_css_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-topics-list', $this->css_url['frontend'] . 'topics-list' . $this->scripts_prefix . '.css', $topics_css_deps, FMWP_VERSION );

			wp_register_style( 'fmwp-forum', $this->css_url['frontend'] . 'forum' . $this->scripts_prefix . '.css', $forum_css_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-topic', $this->css_url['frontend'] . 'topic' . $this->scripts_prefix . '.css', $topic_css_deps, FMWP_VERSION );

			wp_register_style( 'fmwp-popup-general', $this->css_url['frontend'] . 'popup' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );
			wp_register_style( 'fmwp-reply-popup', $this->css_url['frontend'] . 'reply-popup' . $this->scripts_prefix . '.css', array( 'fmwp-popup-general' ), FMWP_VERSION );
			wp_register_style( 'fmwp-topic-popup', $this->css_url['frontend'] . 'topic-popup' . $this->scripts_prefix . '.css', array( 'fmwp-popup-general' ), FMWP_VERSION );

			wp_register_style( 'fmwp-profile', $this->css_url['frontend'] . 'profile' . $this->scripts_prefix . '.css', array( 'fmwp-common', 'fmwp-forms' ), FMWP_VERSION );

			wp_register_style( 'fmwp-user-topics', $this->css_url['frontend'] . 'user-topics' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );
			wp_register_style( 'fmwp-user-replies', $this->css_url['frontend'] . 'user-replies' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );

			if ( ! is_user_logged_in() ) {
				wp_register_style( 'fmwp-login-popup', $this->css_url['frontend'] . 'login-popup' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );
			}

			wp_register_style( 'fmwp-login', $this->css_url['frontend'] . 'login' . $this->scripts_prefix . '.css', array( 'fmwp-forms' ), FMWP_VERSION );

			wp_register_style( 'fmwp-new-forum', $this->css_url['frontend'] . 'new-forum' . $this->scripts_prefix . '.css', array( 'fmwp-common', 'fmwp-forms' ), FMWP_VERSION );
		}
	}
}
