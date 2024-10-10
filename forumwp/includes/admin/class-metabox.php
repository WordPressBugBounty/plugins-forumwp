<?php
namespace fmwp\admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\admin\Metabox' ) ) {

	/**
	 * Class Metabox
	 *
	 * @package fmwp\admin
	 */
	class Metabox {

		/**
		 * @var array
		 */
		private $nonce = array();

		/**
		 * Metabox constructor.
		 */
		public function __construct() {
			add_action( 'load-post.php', array( &$this, 'add_metabox' ), 9 );
			add_action( 'load-post-new.php', array( &$this, 'add_metabox' ), 9 );
		}

		/**
		 *
		 */
		public function add_metabox() {
			global $current_screen;

			if ( 'fmwp_forum' === $current_screen->id && current_user_can( 'manage_fmwp_forums' ) ) {

				add_action( 'add_meta_boxes', array( &$this, 'add_metabox_forum' ) );
				add_action( 'save_post', array( &$this, 'save_metabox_forum' ), 10, 2 );

			} elseif ( 'fmwp_topic' === $current_screen->id && current_user_can( 'manage_fmwp_topics' ) ) {

				add_action( 'add_meta_boxes', array( &$this, 'add_metabox_topic' ) );
				add_action( 'save_post', array( &$this, 'save_metabox_topic' ), 10, 2 );

			} elseif ( 'fmwp_reply' === $current_screen->id && current_user_can( 'manage_fmwp_replies' ) ) {

				add_action( 'add_meta_boxes', array( &$this, 'add_metabox_reply' ) );

			}
		}

		/**
		 * Load a form metabox
		 *
		 * @param $forum
		 * @param $box
		 */
		public function load_metabox_forum( $forum, $box ) {
			$metabox = str_replace( 'fmwp-forum-', '', $box['id'] );

			include_once FMWP()->admin()->templates_path . 'forum' . DIRECTORY_SEPARATOR . $metabox . '.php';

			if ( empty( $this->nonce['forum'] ) ) {
				$this->nonce['forum'] = true;
				wp_nonce_field( basename( __FILE__ ), 'fmwp_forum_save_metabox_nonce' );
			}
		}

		/**
		 * Load a form metabox
		 *
		 * @param $topic
		 * @param $box
		 */
		public function load_metabox_topic( $topic, $box ) {
			$metabox = str_replace( 'fmwp-topic-', '', $box['id'] );

			include_once FMWP()->admin()->templates_path . 'topic' . DIRECTORY_SEPARATOR . $metabox . '.php';

			if ( empty( $this->nonce['topic'] ) ) {
				$this->nonce['topic'] = true;
				wp_nonce_field( basename( __FILE__ ), 'fmwp_topic_save_metabox_nonce' );
			}
		}

		/**
		 * Load a form metabox
		 *
		 * @param $reply
		 * @param $box
		 */
		public function load_metabox_reply( $reply, $box ) {
			$metabox = str_replace( 'fmwp-reply-', '', $box['id'] );

			include_once FMWP()->admin()->templates_path . 'reply' . DIRECTORY_SEPARATOR . $metabox . '.php';

			if ( empty( $this->nonce['reply'] ) ) {
				$this->nonce['reply'] = true;
				wp_nonce_field( basename( __FILE__ ), 'fmwp_reply_save_metabox_nonce' );
			}
		}

		/**
		 * Add form metabox
		 */
		public function add_metabox_forum() {
			add_meta_box( 'fmwp-forum-attributes', __( 'Forum Settings', 'forumwp' ), array( &$this, 'load_metabox_forum' ), 'fmwp_forum', 'side', 'core' );
			add_meta_box( 'fmwp-forum-styling', __( 'Forum Styling', 'forumwp' ), array( &$this, 'load_metabox_forum' ), 'fmwp_forum', 'side', 'core' );
		}

		/**
		 * Add form metabox
		 */
		public function add_metabox_topic() {
			add_meta_box( 'fmwp-topic-attributes', __( 'Topic Settings', 'forumwp' ), array( &$this, 'load_metabox_topic' ), 'fmwp_topic', 'side', 'core' );
			add_meta_box( 'fmwp-topic-styling', __( 'Topic Styling', 'forumwp' ), array( &$this, 'load_metabox_topic' ), 'fmwp_topic', 'side', 'core' );
			add_meta_box( 'fmwp-topic-creator', __( 'Topic Creator', 'forumwp' ), array( &$this, 'load_metabox_topic' ), 'fmwp_topic', 'side', 'core' );
		}

		/**
		 * Add form metabox
		 */
		public function add_metabox_reply() {
			add_meta_box( 'fmwp-reply-creator', __( 'Reply Creator', 'forumwp' ), array( &$this, 'load_metabox_reply' ), 'fmwp_reply', 'side', 'core' );
		}

		/**
		 * Save forum metabox
		 *
		 * @param $post_id
		 * @param $post
		 */
		public function save_metabox_forum( $post_id, $post ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// validate post type
			if ( 'fmwp_forum' !== $post->post_type ) {
				return;
			}

			// validate post type object
			$post_type = get_post_type_object( $post->post_type );
			if ( null === $post_type ) {
				return;
			}

			// validate user
			if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
				return;
			}

			if ( empty( $_POST['fmwp_metadata'] ) ) {
				return;
			}

			check_admin_referer( basename( __FILE__ ), 'fmwp_forum_save_metabox_nonce' );

			//save metadata
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below in sanitize.
			foreach ( $_POST['fmwp_metadata'] as $k => $v ) {
				if ( false !== strpos( $k, 'fmwp_' ) ) {
					switch ( $k ) {
						default:
							// sanitize as text field by default but can use filter for 3rd-party integration
							$v = apply_filters( 'fmwp_sanitize_forum_metadata', sanitize_text_field( $v ), $v, $k );
							break;
						case 'fmwp_locked':
							$v = (bool) $v;
							break;
						case 'fmwp_visibility':
							$v = sanitize_key( $v );
							break;
						case 'fmwp_order':
							$v = (int) $v;
							break;
					}

					update_post_meta( $post_id, sanitize_key( $k ), $v );
				}
			}
		}

		/**
		 * Save topic metabox
		 *
		 * @param $post_id
		 * @param $post
		 */
		public function save_metabox_topic( $post_id, $post ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// validate post type
			if ( 'fmwp_topic' !== $post->post_type ) {
				return;
			}

			// validate post type object
			$post_type = get_post_type_object( $post->post_type );
			if ( null === $post_type ) {
				return;
			}

			// validate user
			if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
				return;
			}

			if ( empty( $_POST['fmwp_metadata'] ) ) {
				return;
			}

			check_admin_referer( basename( __FILE__ ), 'fmwp_topic_save_metabox_nonce' );

			//save metadata
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below in sanitize.
			foreach ( $_POST['fmwp_metadata'] as $k => $v ) {
				if ( false !== strpos( $k, 'fmwp_' ) ) {

					switch ( $k ) {
						default:
							// sanitize as text field by default but can use filter for 3rd-party integration
							$v = apply_filters( 'fmwp_sanitize_topic_metadata', sanitize_text_field( $v ), $v, $k );
							break;
						case 'fmwp_locked':
							$v = (bool) $v;
							break;
						case 'fmwp_type':
							$v = sanitize_key( $v );
							break;
						case 'fmwp_forum':
							$v = absint( $v );
							break;
					}

					$old_forum_id = FMWP()->common()->topic()->get_forum_id( $post_id );

					update_post_meta( $post_id, sanitize_key( $k ), $v );
					if ( 'fmwp_type' === $k ) {
						update_post_meta( $post_id, 'fmwp_type_order', FMWP()->common()->topic()->types[ $v ]['order'] );
					}

					if ( 'fmwp_forum' === $k ) {
						$upgrade_last_update = apply_filters( 'fmwp_topic_upgrade_last_update', true, $post_id );

						if ( $upgrade_last_update ) {
							$forum_id = FMWP()->common()->topic()->get_forum_id( $post_id );
							if ( ! empty( $forum_id ) ) {
								update_post_meta( $forum_id, 'fmwp_last_update', time() );
							}

							if ( ! empty( $old_forum_id ) ) {
								update_post_meta( $old_forum_id, 'fmwp_last_update', time() );
							}
						}
					}
				}
			}
		}
	}
}
