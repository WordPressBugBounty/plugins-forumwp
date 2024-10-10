<?php
namespace fmwp\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\admin\Common' ) ) {

	/**
	 * Class Common
	 *
	 * @package fmwp\admin
	 */
	class Common {

		/**
		 * @var string
		 */
		public $templates_path = '';

		/**
		 * Common constructor.
		 */
		public function __construct() {
			$this->templates_path = FMWP_PATH . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		}

		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \FMWP::includes()
		 */
		public function includes() {
			$this->actions_listener();
			$this->columns();
			$this->enqueue();
			$this->menu();
			$this->metabox();
			$this->notices();
			$this->settings();
			$this->upgrade();
		}

		/**
		 * Check if ForumWP screen is loaded
		 *
		 * @return bool
		 */
		public function is_own_screen() {
			global $current_screen;
			$screen_id = $current_screen->id;

			if ( false !== strpos( $screen_id, 'forumwp' ) || false !== strpos( $screen_id, 'fmwp_' ) ) {
				return true;
			}

			if ( $this->is_own_post_type() ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if current page load ForumWP CPT
		 *
		 * @return bool
		 */
		public function is_own_post_type() {
			$cpt = FMWP()->get_cpt();

			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_REQUEST['post_type'] ) ) {
				$post_type = sanitize_key( $_REQUEST['post_type'] );
				if ( array_key_exists( $post_type, $cpt ) ) {
					return true;
				}
			} elseif ( isset( $_REQUEST['action'] ) && 'edit' === sanitize_key( $_REQUEST['action'] ) ) {
				$post_type = get_post_type();
				if ( array_key_exists( $post_type, $cpt ) ) {
					return true;
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return false;
		}

		/**
		 * @since 2.0
		 *
		 * @return Actions_Listener
		 */
		public function actions_listener() {
			if ( empty( FMWP()->classes['fmwp\admin\actions_listener'] ) ) {
				FMWP()->classes['fmwp\admin\actions_listener'] = new Actions_Listener();
			}
			return FMWP()->classes['fmwp\admin\actions_listener'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Columns
		 */
		public function columns() {
			if ( empty( FMWP()->classes['fmwp\admin\columns'] ) ) {
				FMWP()->classes['fmwp\admin\columns'] = new Columns();
			}

			return FMWP()->classes['fmwp\admin\columns'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Enqueue
		 */
		public function enqueue() {
			if ( empty( FMWP()->classes['fmwp\admin\enqueue'] ) ) {
				FMWP()->classes['fmwp\admin\enqueue'] = new Enqueue();
			}
			return FMWP()->classes['fmwp\admin\enqueue'];
		}

		/**
		 * @since 1.0
		 *
		 * @param array $data
		 *
		 * @return bool|Forms
		 */
		public function forms( $data ) {
			if ( ! array_key_exists( 'class', $data ) ) {
				return false;
			}

			if ( empty( FMWP()->classes[ 'fmwp\admin\forms' . $data['class'] ] ) ) {
				FMWP()->classes[ 'fmwp\admin\forms' . $data['class'] ] = new Forms( $data );
			}
			return FMWP()->classes[ 'fmwp\admin\forms' . $data['class'] ];
		}

		/**
		 * @since 1.0
		 *
		 * @return Menu
		 */
		public function menu() {
			if ( empty( FMWP()->classes['fmwp\admin\menu'] ) ) {
				FMWP()->classes['fmwp\admin\menu'] = new Menu();
			}
			return FMWP()->classes['fmwp\admin\menu'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Metabox
		 */
		public function metabox() {
			if ( empty( FMWP()->classes['fmwp\admin\metabox'] ) ) {
				FMWP()->classes['fmwp\admin\metabox'] = new Metabox();
			}
			return FMWP()->classes['fmwp\admin\metabox'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Notices
		 */
		public function notices() {
			if ( empty( FMWP()->classes['fmwp\admin\notices'] ) ) {
				FMWP()->classes['fmwp\admin\notices'] = new Notices();
			}
			return FMWP()->classes['fmwp\admin\notices'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Settings
		 */
		public function settings() {
			if ( empty( FMWP()->classes['fmwp\admin\settings'] ) ) {
				FMWP()->classes['fmwp\admin\settings'] = new Settings();
			}
			return FMWP()->classes['fmwp\admin\settings'];
		}

		/**
		 * @since 2.0
		 *
		 * @return Upgrade
		 */
		public function upgrade() {
			if ( empty( FMWP()->classes['fmwp\admin\upgrade'] ) ) {
				FMWP()->classes['fmwp\admin\upgrade'] = new Upgrade();
			}

			return FMWP()->classes['fmwp\admin\upgrade'];
		}
	}
}
