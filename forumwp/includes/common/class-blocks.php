<?php
namespace fmwp\common;

use WP_Block_Type_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Blocks' ) ) {


	/**
	 * Class Blocks
	 *
	 * @package fmwp\common
	 */
	class Blocks {

		/**
		 * Blocks constructor.
		 */
		public function __construct() {
			add_action( 'init', array( &$this, 'wp_register_block_metadata_collection' ), 11 );
		}

		public function wp_register_block_metadata_collection() {
			if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
				// Added since WordPress 6.7
				wp_register_block_metadata_collection( FMWP_PATH . 'includes/blocks', FMWP_PATH . 'includes/blocks/blocks-manifest.php' );
			}

			$blocks = array(
				'fmwp-block/fmwp-login-form'        => array(
					'render_callback' => array( $this, 'fmwp_login_form_render' ),
					'attributes'      => array(
						'redirect' => array(
							'type' => 'string',
						),
						'is_popup' => array(
							'type'    => 'boolean',
							'default' => false,
						),
					),
				),
				'fmwp-block/fmwp-registration-form' => array(
					'render_callback' => array( $this, 'fmwp_registration_form_render' ),
					'attributes'      => array(
						'redirect'   => array(
							'type' => 'string',
						),
						'first_name' => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'last_name'  => array(
							'type'    => 'boolean',
							'default' => true,
						),
					),
				),
				'fmwp-block/fmwp-forums-list'       => array(
					'render_callback' => array( $this, 'fmwp_forums_list_render' ),
					'attributes'      => array(
						'search'   => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'category' => array(
							'type'    => 'string',
							'default' => '',
						),
						'with_sub' => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'order'    => array(
							'type'    => 'string',
							'default' => FMWP()->options()->get( 'default_forums_order' ),
						),
					),
				),
				'fmwp-block/fmwp-topics-list'       => array(
					'render_callback' => array( $this, 'fmwp_topics_list_render' ),
					'attributes'      => array(
						'search'     => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'new_topic'  => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'show_forum' => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'tag'        => array(
							'type'    => 'string',
							'default' => '',
						),
						'status'     => array(
							'type'    => 'string',
							'default' => '',
						),
						'type'       => array(
							'type'    => 'string',
							'default' => '',
						),
						'order'      => array(
							'type'    => 'string',
							'default' => FMWP()->options()->get( 'default_topics_order' ),
						),
					),
				),
				'fmwp-block/fmwp-forum-categories'  => array(
					'render_callback' => array( $this, 'fmwp_forum_categories_render' ),
					'attributes'      => array(
						'search' => array(
							'type'    => 'boolean',
							'default' => true,
						),
					),
				),
				'fmwp-block/fmwp-forum'             => array(
					'render_callback' => array( $this, 'fmwp_forum_render' ),
					'attributes'      => array(
						'show_header' => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'order'       => array(
							'type'    => 'string',
							'default' => FMWP()->options()->get( 'default_topics_order' ),
						),
						'id'          => array(
							'type'    => 'string',
							'default' => '',
						),
					),
				),
				'fmwp-block/fmwp-topic'             => array(
					'render_callback' => array( $this, 'fmwp_topic_render' ),
					'attributes'      => array(
						'show_forum'  => array(
							'type'    => 'boolean',
							'default' => (bool) FMWP()->options()->get( 'show_forum' ),
						),
						'show_header' => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'order'       => array(
							'type'    => 'string',
							'default' => 'date_asc',
						),
						'id'          => array(
							'type'    => 'string',
							'default' => '',
						),
					),
				),
				'fmwp-block/fmwp-user-topics'       => array(
					'render_callback' => array( $this, 'fmwp_user_topics_render' ),
					'attributes'      => array(
						'user_id' => array(
							'type'    => 'string',
							'default' => '',
						),
					),
				),
				'fmwp-block/fmwp-user-replies'      => array(
					'render_callback' => array( $this, 'fmwp_user_replies_render' ),
					'attributes'      => array(
						'user_id' => array(
							'type'    => 'string',
							'default' => '',
						),
					),
				),
				'fmwp-block/fmwp-user-edit'         => array(
					'render_callback' => array( $this, 'fmwp_user_edit_render' ),
				),
				'fmwp-block/fmwp-new-forum'         => array(
					'render_callback' => array( $this, 'fmwp_new_forum_render' ),
				),
				'fmwp-block/fmwp-user-profile'      => array(
					'render_callback' => array( $this, 'fmwp_user_profile_render' ),
				),
			);

			foreach ( $blocks as $k => $block_data ) {
				$block_type = str_replace( 'fmwp-block/', '', $k );
				register_block_type_from_metadata( FMWP_PATH . 'includes/blocks/' . $block_type, $block_data );
			}
		}

		public function fmwp_login_form_render( $atts ) {
			$shortcode = '[fmwp_login_form';

			if ( isset( $atts['redirect'] ) && '' !== $atts['redirect'] ) {
				$shortcode .= ' redirect="' . $atts['redirect'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_registration_form_render( $atts ) {
			$shortcode = '[fmwp_registration_form';

			if ( isset( $atts['redirect'] ) && '' !== $atts['redirect'] ) {
				$shortcode .= ' redirect="' . $atts['redirect'] . '"';
			}

			if ( isset( $atts['category'] ) && '' !== $atts['category'] ) {
				$shortcode .= ' category="' . $atts['category'] . '"';
			}

			if ( isset( $atts['first_name'] ) && true === $atts['first_name'] ) {
				$shortcode .= ' first_name="hide"';
			} else {
				$shortcode .= ' first_name="show"';
			}

			if ( isset( $atts['last_name'] ) && true === $atts['last_name'] ) {
				$shortcode .= ' last_name="hide"';
			} else {
				$shortcode .= ' last_name="show"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_forums_list_render( $atts ) {
			$shortcode = '[fmwp_forums ';

			if ( isset( $atts['order'] ) && '' !== $atts['order'] ) {
				$shortcode .= ' order="' . $atts['order'] . '"';
			}

			if ( isset( $atts['search'] ) && true === $atts['search'] ) {
				$shortcode .= ' search="yes"';
			} else {
				$shortcode .= ' search="no"';
			}

			if ( isset( $atts['with_sub'] ) && true === $atts['with_sub'] ) {
				$shortcode .= ' with_sub="1"';
			} else {
				$shortcode .= ' with_sub="0"';
			}

			if ( isset( $atts['category'] ) && '' !== $atts['search'] ) {
				$shortcode .= ' category="' . $atts['category'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_topics_list_render( $atts ) {
			$shortcode = '[fmwp_topics is_block="1"';

			if ( isset( $atts['order'] ) && '' !== $atts['order'] ) {
				$shortcode .= ' order="' . $atts['order'] . '"';
			}

			if ( isset( $atts['search'] ) && true === $atts['search'] ) {
				$shortcode .= ' search="yes"';
			} else {
				$shortcode .= ' search="no"';
			}

			if ( isset( $atts['new_topic'] ) && true === $atts['new_topic'] ) {
				$shortcode .= ' new_topic="yes"';
			} else {
				$shortcode .= ' new_topic="no"';
			}

			if ( isset( $atts['show_forum'] ) && true === $atts['show_forum'] ) {
				$shortcode .= ' show_forum="yes"';
			} else {
				$shortcode .= ' show_forum="no"';
			}

			if ( isset( $atts['tag'] ) && '' !== $atts['tag'] ) {
				$shortcode .= ' tag="' . $atts['tag'] . '"';
			}

			if ( isset( $atts['status'] ) && '' !== $atts['status'] ) {
				$shortcode .= ' status="' . $atts['status'] . '"';
			}

			if ( isset( $atts['type'] ) && '' !== $atts['type'] ) {
				$shortcode .= ' type="' . $atts['type'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_forum_categories_render( $atts ) {
			$shortcode = '[fmwp_forum_categories ';

			if ( isset( $atts['search'] ) && true === $atts['search'] ) {
				$shortcode .= ' search="yes"';
			} else {
				$shortcode .= ' search="no"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_forum_render( $atts ) {
			$shortcode = '[fmwp_forum ';

			if ( isset( $atts['show_header'] ) && true === $atts['show_header'] ) {
				$shortcode .= ' show_header="yes"';
			} else {
				$shortcode .= ' show_header="no"';
			}

			if ( isset( $atts['order'] ) && '' !== $atts['order'] ) {
				$shortcode .= ' order="' . $atts['order'] . '"';
			}

			if ( isset( $atts['id'] ) && '' !== $atts['id'] ) {
				$shortcode .= ' id="' . $atts['id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_topic_render( $atts ) {
			$shortcode = '[fmwp_topic ';

			if ( isset( $atts['show_header'] ) && true === $atts['show_header'] ) {
				$shortcode .= ' show_header="yes"';
			} else {
				$shortcode .= ' show_header="no"';
			}

			if ( isset( $atts['show_forum'] ) && true === $atts['show_forum'] ) {
				$shortcode .= ' show_forum="yes"';
			} else {
				$shortcode .= ' show_forum="no"';
			}

			if ( isset( $atts['order'] ) && '' !== $atts['order'] ) {
				$shortcode .= ' order="' . $atts['order'] . '"';
			}

			if ( isset( $atts['id'] ) && '' !== $atts['id'] ) {
				$shortcode .= ' id="' . $atts['id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_user_topics_render( $atts ) {
			$shortcode = '[fmwp_user_topics ';

			if ( isset( $atts['user_id'] ) && '' !== $atts['user_id'] ) {
				$shortcode .= ' user_id="' . $atts['user_id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_user_replies_render( $atts ) {
			$shortcode = '[fmwp_user_replies ';

			if ( isset( $atts['user_id'] ) && '' !== $atts['user_id'] ) {
				$shortcode .= ' user_id="' . $atts['user_id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_user_edit_render() {
			$shortcode = '[fmwp_user_edit]';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_new_forum_render() {
			$shortcode = '[fmwp_new_forum]';

			return apply_shortcodes( $shortcode );
		}

		public function fmwp_user_profile_render() {
			$shortcode = '[fmwp_user_profile]';

			return apply_shortcodes( $shortcode );
		}
	}
}
