<?php
namespace fmwp\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\frontend\Common' ) ) {

	/**
	 * Class Common
	 *
	 * @package fmwp\frontend
	 */
	class Common {

		/**
		 * Common constructor.
		 */
		public function __construct() {
			add_action( 'fmwp_before_forums_list', array( &$this, 'breadcrumbs_ui' ) );
			add_action( 'fmwp_before_topics_list', array( &$this, 'breadcrumbs_ui' ) );
			add_action( 'fmwp_before_individual_forum', array( &$this, 'breadcrumbs_ui' ) );
			add_action( 'fmwp_before_individual_topic', array( &$this, 'breadcrumbs_ui' ) );
		}

		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \FMWP::includes()
		 */
		public function includes() {
			$this->actions_listener();
			$this->enqueue();
			$this->shortcodes();
		}

		/**
		 * @since 1.0
		 *
		 * @return Shortcodes
		 */
		public function shortcodes() {
			if ( empty( FMWP()->classes['fmwp\frontend\shortcodes'] ) ) {
				FMWP()->classes['fmwp\frontend\shortcodes'] = new Shortcodes();
			}

			return FMWP()->classes['fmwp\frontend\shortcodes'];
		}

		/**
		 * @since 1.0
		 *
		 * @return Enqueue
		 */
		public function enqueue() {
			if ( empty( FMWP()->classes['fmwp\frontend\enqueue'] ) ) {
				FMWP()->classes['fmwp\frontend\enqueue'] = new Enqueue();
			}

			return FMWP()->classes['fmwp\frontend\enqueue'];
		}

		/**
		 * @since 1.0
		 *
		 * @param array $data
		 *
		 * @return bool|Forms
		 */
		public function forms( $data ) {
			if ( ! array_key_exists( 'id', $data ) ) {
				return false;
			}

			if ( empty( FMWP()->classes[ 'fmwp\frontend\forms' . $data['id'] ] ) ) {
				FMWP()->classes[ 'fmwp\frontend\forms' . $data['id'] ] = new Forms( $data );
			}

			return FMWP()->classes[ 'fmwp\frontend\forms' . $data['id'] ];
		}

		/**
		 * @since 1.0
		 *
		 * @return Profile
		 */
		public function profile() {
			if ( empty( FMWP()->classes['fmwp\frontend\profile'] ) ) {
				FMWP()->classes['fmwp\frontend\profile'] = new Profile();
			}

			return FMWP()->classes['fmwp\frontend\profile'];
		}

		/**
		 * @since 2.0
		 *
		 * @return Actions_Listener
		 */
		public function actions_listener() {
			if ( empty( FMWP()->classes['fmwp\frontend\actions_listener'] ) ) {
				FMWP()->classes['fmwp\frontend\actions_listener'] = new Actions_Listener();
			}
			return FMWP()->classes['fmwp\frontend\actions_listener'];
		}

		/**
		 *
		 */
		public function breadcrumbs_ui() {
			if ( ! FMWP()->options()->get( 'breadcrumb_enabled' ) ) {
				return;
			}

			$breadcrumbs        = FMWP()->get_breadcrumbs_data();
			$breadcrumbs_length = count( $breadcrumbs );
			ob_start();
			?>
			<div class="fmwp-breadcrumbs">
				<ul>
					<?php
					foreach ( $breadcrumbs as $i => $breadcrumbs_item ) {
						$url       = $breadcrumbs_item['url'];
						$css_class = 'fmwp-breadcrumb-link';
						if ( $i === $breadcrumbs_length - 1 ) {
							$url       = '#';
							$css_class = 'fmwp-breadcrumb-link last-item';
						}
						?>
						<li class="fmwp-breadcrumb-item fmwp-breadcrumbs-<?php echo esc_attr( $i ); ?>">
							<a class="<?php echo esc_attr( $css_class ); ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $breadcrumbs_item['title'] ); ?></a>
						</li>
						<?php
					}
					?>
				</ul>
				<div class="clear"></div>
			</div>
			<?php
			ob_get_flush();
		}
	}
}
