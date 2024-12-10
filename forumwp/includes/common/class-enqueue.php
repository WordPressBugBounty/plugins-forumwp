<?php
namespace fmwp\common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Enqueue' ) ) {

	/**
	 * Class Enqueue
	 *
	 * @package fmwp\common
	 */
	class Enqueue {

		/**
		 * @var string scripts' Standard or Minified versions
		 *
		 * @since 2.0
		 */
		public $scripts_prefix;

		/**
		 * @var array JS URLs
		 *
		 * @since 2.0
		 */
		public $js_url = array();

		/**
		 * @var array CSS URLs
		 *
		 * @since 2.0
		 */
		public $css_url = array();

		/**
		 * @var array assets URLs
		 *
		 * @since 2.0
		 */
		public $url = array();

		/**
		 * @var string FontAwesome version
		 *
		 * @since 2.0
		 */
		public $fa_version = '5.13.0';

		/**
		 * Enqueue constructor.
		 *
		 * @since 2.0
		 */
		public function __construct() {
			add_action( 'forumwp_init', array( $this, 'init_variables' ) );

			add_action( 'admin_enqueue_scripts', array( &$this, 'common_libs' ), 9 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'common_libs' ), 9 );

			add_filter( 'fmwp_frontend_common_styles_deps', array( &$this, 'extends_frontend_styles' ) );
			add_filter( 'fmwp_admin_common_styles_deps', array( &$this, 'extends_admin_styles' ) );

			add_action( 'enqueue_block_assets', array( &$this, 'block_editor' ), 11 );

			global $wp_version;
			if ( version_compare( $wp_version, '5.8', '>=' ) ) {
				add_filter( 'block_categories_all', array( &$this, 'blocks_category' ), 10, 1 );
			} else {
				add_filter( 'block_categories', array( &$this, 'blocks_category' ), 10, 1 );
			}
		}

		/**
		 * Init variables for enqueue scripts
		 *
		 * @since 2.0
		 */
		public function init_variables() {
			$this->scripts_prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			$this->url['common']     = FMWP_URL . 'assets/common/';
			$this->js_url['common']  = FMWP_URL . 'assets/common/js/';
			$this->css_url['common'] = FMWP_URL . 'assets/common/css/';
		}

		/**
		 * Register common JS/CSS libraries
		 *
		 * @since 2.0
		 */
		public function common_libs() {
			wp_register_script( 'fmwp-helptip', $this->js_url['common'] . 'helptip' . $this->scripts_prefix . '.js', array( 'jquery', 'jquery-ui-tooltip' ), FMWP_VERSION, true );

			wp_register_style( 'fmwp-jquery-ui', $this->url['common'] . 'libs/jquery-ui/jquery-ui' . $this->scripts_prefix . '.css', array(), '1.12.1' );

			wp_register_style( 'fmwp-helptip', $this->css_url['common'] . 'helptip' . $this->scripts_prefix . '.css', array( 'dashicons', 'fmwp-jquery-ui' ), FMWP_VERSION );

			if ( ! FMWP()->options()->get( 'disable-fa-styles' ) ) {
				wp_register_style( 'fmwp-far', $this->url['common'] . 'libs/fontawesome/css/regular' . $this->scripts_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'fmwp-fas', $this->url['common'] . 'libs/fontawesome/css/solid' . $this->scripts_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'fmwp-fab', $this->url['common'] . 'libs/fontawesome/css/brands' . $this->scripts_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'fmwp-fa', $this->url['common'] . 'libs/fontawesome/css/v4-shims' . $this->scripts_prefix . '.css', array(), $this->fa_version );
				wp_register_style( 'fmwp-font-awesome', $this->url['common'] . 'libs/fontawesome/css/fontawesome' . $this->scripts_prefix . '.css', array( 'fmwp-fa', 'fmwp-far', 'fmwp-fas', 'fmwp-fab' ), $this->fa_version );
			}
		}

		/**
		 * Add FontAwesome styles to dependencies if it's not disabled frontend
		 *
		 * @param array $styles
		 *
		 * @return array
		 *
		 * @since 2.0
		 */
		public function extends_frontend_styles( $styles ) {
			if ( FMWP()->options()->get( 'disable-fa-styles' ) ) {
				return $styles;
			}

			$styles[] = 'fmwp-font-awesome';
			return $styles;
		}

		/**
		 * Add FontAwesome styles to dependencies if it's not disabled wp-admin
		 *
		 * @param array $styles
		 *
		 * @return array
		 *
		 * @since 2.0
		 */
		public function extends_admin_styles( $styles ) {
			if ( FMWP()->options()->get( 'disable-fa-styles' ) ) {
				return $styles;
			}

			$styles[] = 'fmwp-font-awesome';
			return $styles;
		}

		/**
		 * Add Gutenberg category for ForumWP shortcodes
		 *
		 * @param array $categories
		 *
		 * @return array
		 */
		public function blocks_category( $categories ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug'  => 'fmwp-blocks',
						'title' => __( 'ForumWP', 'forumwp' ),
					),
				)
			);
		}

		/**
		 * Enqueue Gutenberg Block Editor assets
		 */
		public function block_editor() {
			// @todo add condition to check that we are in block editor, not a frontend load
			if ( ! is_user_logged_in() || ! is_admin() ) {
				return;
			}

			$js_url  = FMWP_URL . 'assets/front/js/';
			$css_url = FMWP_URL . 'assets/front/css/';

			// Enqueue block editor styles
			wp_register_style( 'fmwp-helptip', $this->css_url['common'] . 'helptip' . $this->scripts_prefix . '.js', array( 'jquery', 'jquery-ui-tooltip' ), FMWP_VERSION, true );
			wp_register_style( 'fmwp-tipsy', $this->url['common'] . 'libs/tipsy/css/tipsy' . $this->scripts_prefix . '.css', array(), FMWP_VERSION );
			$common_frontend_deps = apply_filters( 'fmwp_frontend_common_styles_deps', array( 'fmwp-tipsy', 'fmwp-helptip' ) );
			wp_register_style( 'fmwp-common', $css_url . 'common' . $this->scripts_prefix . '.css', $common_frontend_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-breadcrumbs', $css_url . 'breadcrumbs' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );
			wp_register_style( 'fmwp-forms', $css_url . 'forms' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );
			wp_register_style( 'fmwp-profile', $css_url . 'profile' . $this->scripts_prefix . '.css', array( 'fmwp-common', 'fmwp-forms' ), FMWP_VERSION );
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
			wp_register_style( 'fmwp-forums-list', $css_url . 'forums-list' . $this->scripts_prefix . '.css', $forums_css_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-topics-list', $css_url . 'topics-list' . $this->scripts_prefix . '.css', $topics_css_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-forum-categories-list', $css_url . 'forum-categories-list' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );
			wp_register_style( 'fmwp-forum', $css_url . 'forum' . $this->scripts_prefix . '.css', $forum_css_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-topic', $css_url . 'topic' . $this->scripts_prefix . '.css', $topic_css_deps, FMWP_VERSION );
			wp_register_style( 'fmwp-user-topics', $css_url . 'user-topics' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );
			wp_register_style( 'fmwp-user-replies', $css_url . 'user-replies' . $this->scripts_prefix . '.css', array( 'fmwp-common' ), FMWP_VERSION );

			// Enqueue block editor scripts
			wp_register_script( 'fmwp-popup-general', $js_url . 'popup' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'wp-api' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-tipsy', $this->url['common'] . 'libs/tipsy/js/tipsy' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-tooltip', $js_url . 'tooltip' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-notice', $js_url . 'notice' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-helptip', $this->js_url['common'] . 'helptip' . $this->scripts_prefix . '.js', array( 'jquery', 'jquery-ui-tooltip' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-dropdown', $js_url . 'dropdown' . $this->scripts_prefix . '.js', array( 'jquery' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-front-global', $js_url . 'global' . $this->scripts_prefix . '.js', array( 'jquery', 'wp-util', 'fmwp-tipsy', 'fmwp-notice', 'fmwp-tooltip', 'wp-i18n', 'fmwp-helptip', 'wp-hooks' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-forums-logged-in', $js_url . 'logged-in-forums' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-topics-logged-in', $js_url . 'logged-in-topics' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-profile', $js_url . 'profile' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-forum-categories-list', $js_url . 'forum-categories-list' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-forum', $js_url . 'forum' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'suggest', 'fmwp-dropdown' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-topic', $js_url . 'topic' . $this->scripts_prefix . '.js', array( 'fmwp-front-global', 'fmwp-dropdown', 'fmwp-popup-general' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-user-topics', $js_url . 'user-topics' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );
			wp_register_script( 'fmwp-user-replies', $js_url . 'user-replies' . $this->scripts_prefix . '.js', array( 'fmwp-front-global' ), FMWP_VERSION, true );

			$forums_deps = array( 'fmwp-front-global', 'fmwp-forums-logged-in' );
			wp_register_script( 'fmwp-forums-list', $js_url . 'forums-list' . $this->scripts_prefix . '.js', $forums_deps, FMWP_VERSION, true );

			$topics_deps = array( 'fmwp-front-global', 'fmwp-topics-logged-in' );
			wp_register_script( 'fmwp-topics-list', $js_url . 'topics-list' . $this->scripts_prefix . '.js', $topics_deps, FMWP_VERSION, true );

			$localize_data = apply_filters(
				'fmwp_enqueue_localize',
				array(
					'can_reply' => is_user_logged_in() && current_user_can( 'fmwp_post_reply' ),
					'can_topic' => is_user_logged_in() && current_user_can( 'fmwp_post_topic' ),
					'nonce'     => wp_create_nonce( 'fmwp-frontend-nonce' ),
				)
			);
			wp_localize_script( 'fmwp-front-global', 'fmwp_front_data', $localize_data );

			wp_enqueue_style( 'fmwp-helptip' );
			wp_enqueue_style( 'fmwp-tipsy' );
			wp_enqueue_style( 'fmwp-common' );
			wp_enqueue_style( 'fmwp-forms' );
			wp_enqueue_style( 'fmwp-profile' );
			wp_enqueue_style( 'fmwp-breadcrumbs' );
			wp_enqueue_style( 'fmwp-front-global' );
			wp_enqueue_style( 'fmwp-forums-list' );
			wp_enqueue_style( 'fmwp-topics-list' );
			wp_enqueue_style( 'fmwp-forum-categories-list' );
			wp_enqueue_style( 'fmwp-forum' );
			wp_enqueue_style( 'fmwp-topic' );
			wp_enqueue_style( 'fmwp-user-topics' );
			wp_enqueue_style( 'fmwp-user-replies' );

			wp_enqueue_script( 'fmwp-profile' );
			wp_enqueue_script( 'fmwp-forums-logged-in' );
			wp_enqueue_script( 'fmwp-forums-list' );
			wp_enqueue_script( 'fmwp-topics-list' );
			wp_enqueue_script( 'fmwp-forum-categories-list' );
			wp_enqueue_script( 'fmwp-forum' );
			wp_enqueue_script( 'fmwp-topic' );
			wp_enqueue_script( 'fmwp-user-topics' );
			wp_enqueue_script( 'fmwp-user-replies' );
		}
	}
}
