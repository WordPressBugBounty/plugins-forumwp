<?php
namespace fmwp\common;

use WP_Admin_Bar;
use WP_Post;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Common' ) ) {

	/**
	 * Class Common
	 * @package fmwp\common
	 */
	class Common {

		public $origin_content = '';

		public $users_mentioned;

		/**
		 * Common constructor.
		 */
		public function __construct() {
			//run hook for extensions init
			add_action( 'plugins_loaded', array( &$this, 'on_plugins_loaded' ), -19 );

			// loading modules when forumwp is loaded
			add_action( 'forumwp_loaded', array( FMWP()->modules(), 'load_modules' ), 1 );

			add_action( 'init', array( $this, 'init' ), 0 );

			add_action( 'admin_bar_menu', array( &$this, 'toolbar_links' ), 999 );
			add_action( 'admin_bar_menu', array( &$this, 'new_cpt_links' ), 999 );

			add_filter( 'insert_user_meta', array( &$this, 'update_user_permalink' ), 10, 3 );

			add_action( 'delete_post', array( &$this, 'delete_sub_posts' ), 10, 2 );

			add_filter( 'wp_insert_post_data', array( &$this, 'insert_post_data' ), 10, 2 );
			add_action( 'save_post', array( &$this, 'update_origin_content' ), 10, 2 );

			add_action( 'save_post', array( &$this, 'mention_notification' ), 999998, 3 );

			add_filter( 'fmwp_sanitize_option_value', array( &$this, 'sanitize_option_value' ), 10, 3 );

			add_filter( 'pre_get_posts', array( &$this, 'exclude_private_topics_replies' ) );
		}

		/**
		 * Exclude ForumWP CPT from global search to avoid the conflicts with private posts data
		 *
		 * @todo exclude only private posts for the current user
		 *
		 * @param WP_Query $query
		 * @return WP_Query $query
		 */
		public function exclude_private_topics_replies( $query ) {
			if ( ! $query->is_search ) {
				return $query;
			}

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_forums_all' ) ) {

				$not_visible = get_posts(
					array(
						'post_type'   => 'fmwp_forum',
						'post_status' => array( 'publish' ),
						'numberposts' => -1,
						'meta_query'  => array(
							array(
								'key'     => 'fmwp_visibility',
								'value'   => 'public',
								'compare' => '!=',
							),
						),
						'fields'      => 'ids',
					)
				);

				$not_visible = ( empty( $not_visible ) || is_wp_error( $not_visible ) ) ? array() : $not_visible;

				$private_hidden = get_posts(
					array(
						'post_type'   => 'fmwp_forum',
						'post_status' => array( 'private', 'pending' ),
						'numberposts' => -1,
						'fields'      => 'ids',
					)
				);

				$private_hidden = ( empty( $private_hidden ) || is_wp_error( $private_hidden ) ) ? array() : $private_hidden;

				$exclude_forums = array_merge( $private_hidden, $not_visible );

				if ( ! empty( $exclude_forums ) ) {
					$post__not_in = $query->get( 'post__not_in' );
					$query->set( 'post__not_in', array_merge( $post__not_in, $exclude_forums ) );
				}
			}

			$exclude_topics = array();
			if ( ! empty( $exclude_forums ) ) {
				$from_hidden_forums = get_posts(
					array(
						'post_type'   => 'fmwp_topic',
						'post_status' => $this->topic()->post_status,
						'numberposts' => -1,
						'meta_query'  => array(
							array(
								'key'     => 'fmwp_forum',
								'value'   => $exclude_forums,
								'compare' => 'IN',
							),
						),
						'fields'      => 'ids',
					)
				);

				$from_hidden_forums = ( empty( $from_hidden_forums ) || is_wp_error( $from_hidden_forums ) ) ? array() : $from_hidden_forums;

				if ( ! empty( $from_hidden_forums ) ) {
					$exclude_topics = array_merge( $exclude_topics, $from_hidden_forums );

					$post__not_in = $query->get( 'post__not_in' );
					$query->set( 'post__not_in', array_merge( $post__not_in, $from_hidden_forums ) );
				}
			}

			if ( ! current_user_can( 'manage_fmwp_topics_all' ) ) {
				$not_visible_topics = get_posts(
					array(
						'post_type'   => 'fmwp_topic',
						'post_status' => $this->topic()->post_status,
						'numberposts' => -1,
						'meta_query'  => array(
							array(
								'key'     => 'fmwp_spam',
								'value'   => '1',
								'compare' => '=',
							),
						),
						'fields'      => 'ids',
					)
				);

				$not_visible_topics = ( empty( $not_visible_topics ) || is_wp_error( $not_visible_topics ) ) ? array() : $not_visible_topics;

				if ( ! empty( $not_visible_topics ) ) {
					$exclude_topics = array_merge( $exclude_topics, $not_visible_topics );
					$post__not_in   = $query->get( 'post__not_in' );
					$query->set( 'post__not_in', array_merge( $post__not_in, $not_visible_topics ) );
				}
			}

			$exclude_replies = array();
			if ( ! empty( $exclude_topics ) ) {
				$from_hidden_topics = get_posts(
					array(
						'post_type'   => 'fmwp_reply',
						'post_status' => $this->reply()->post_status,
						'numberposts' => -1,
						'meta_query'  => array(
							array(
								'key'     => 'fmwp_topic',
								'value'   => $exclude_topics,
								'compare' => 'IN',
							),
						),
						'fields'      => 'ids',
					)
				);

				$from_hidden_topics = ( empty( $from_hidden_topics ) || is_wp_error( $from_hidden_topics ) ) ? array() : $from_hidden_topics;

				if ( ! empty( $from_hidden_topics ) ) {
					$exclude_replies = array_merge( $exclude_replies, $from_hidden_topics );

					$post__not_in = $query->get( 'post__not_in' );
					$query->set( 'post__not_in', array_merge( $post__not_in, $from_hidden_topics ) );
				}
			}

			$all_replies = get_posts(
				array(
					'post_type'      => 'fmwp_reply',
					'post_status'    => $this->reply()->post_status,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $all_replies ) ) {
				$other_replies = array();
				foreach ( $all_replies as $r_id ) {
					if ( ! FMWP()->user()->can_view_reply( get_current_user_id(), $r_id ) ) {
						$other_replies[] = $r_id;
					}
				}

				if ( ! empty( $other_replies ) ) {
					$exclude_replies = array_merge( $exclude_replies, $other_replies );

					$post__not_in = $query->get( 'post__not_in' );
					$query->set( 'post__not_in', array_merge( $post__not_in, $exclude_replies ) );
				}
			}

			return $query;
		}

		/**
		 * @param mixed $sanitized_value
		 * @param mixed $value
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function sanitize_option_value( $sanitized_value, $value, $key ) {
			$pages_keys = array();
			foreach ( FMWP()->config()->get( 'core_pages' ) as $page_key => $page_value ) {
				$pages_keys[] = $page_key . '_page';
			}

			if ( in_array( $key, $pages_keys, true ) ) {
				return absint( $value );
			}

			$email_notifications = FMWP()->config()->get( 'email_notifications' );
			if ( ! empty( $email_notifications ) ) {
				$emails_on_keys  = array();
				$emails_sub_keys = array();
				foreach ( array_keys( $email_notifications ) as $email_key ) {
					$emails_on_keys[]  = $email_key . '_on';
					$emails_sub_keys[] = $email_key . '_sub';
				}

				if ( in_array( $key, $emails_on_keys, true ) ) {
					return (bool) $value;
				}

				if ( in_array( $key, $emails_sub_keys, true ) ) {
					return sanitize_text_field( $value );
				}
			}

			$module_plans = FMWP()->modules()->get_list();
			if ( ! empty( $module_plans ) ) {
				$modules_keys = array();

				foreach ( $module_plans as $plan_data ) {
					if ( empty( $plan_data['modules'] ) ) {
						continue;
					}

					foreach ( $plan_data['modules'] as $slug => $data ) {
						$modules_keys[] = 'module_' . $slug . '_on';
					}
				}

				if ( in_array( $key, $modules_keys, true ) ) {
					return (bool) $value;
				}
			}

			return $sanitized_value;
		}

		/**
		 * @param $content
		 * @param array $postarr
		 *
		 * @return mixed
		 */
		public function mention_links( $content, $postarr ) {
			$this->users_mentioned = array();

			preg_match_all( '/\@([\S]+)/', $content, $matches );

			if ( isset( $matches[1] ) && is_array( $matches[1] ) ) {
				foreach ( $matches[1] as $match ) {
					//https://www.php.net/manual/ru/function.trim.php#98812 fixed UTF encodes symbols
					$match = trim( trim( wp_strip_all_tags( $match ) ), chr( 0xC2 ) . chr( 0xA0 ) );
					if ( isset( $postarr['post_type'] ) && 'fmwp_reply' === $postarr['post_type'] && current_user_can( 'manage_options' ) ) {
						if ( 'all' === strtolower( $match ) || 'everyone' === strtolower( $match ) ) {
							$this->users_mentioned[] = 0;
						}
					}

					$user_id = FMWP()->user()->get_user_by_permalink( $match );
					if ( empty( $user_id ) ) {
						continue;
					}
					$user = get_userdata( $user_id );
					if ( ! empty( $user ) && ! is_wp_error( $user ) ) {
						$this->users_mentioned[] = $user_id;

						$args     = apply_filters( 'fmwp_mention_link_args', array(), $user );
						$tag_args = array();
						foreach ( $args as $arg => $value ) {
							$value      = esc_attr( $value );
							$tag_args[] = "{$arg}=\"{$value}\"";
						}

						$link    = '<a href="' . esc_url( FMWP()->user()->get_profile_link( $user->ID ) ) . '" class="fmwp-link fmwp-mention-link" ' . implode( ' ', $tag_args ) . '>' . FMWP()->user()->display_name( $user ) . '</a>';
						$content = str_replace( '@' . $match, $link, $content );
					}
				}
			}

			if ( in_array( 0, $this->users_mentioned, true ) ) {
				$all_link = '<a data-mention="all" href="#" class="fmwp-link fmwp-mention-link fmwp-mention-all">' . esc_html__( 'All', 'forumwp' ) . '</a>';
				$content  = str_replace( '@all', $all_link, $content );

				$everyone_link = '<a data-mention="all" href="#" class="fmwp-link fmwp-mention-link fmwp-mention-all">' . esc_html__( 'Everyone', 'forumwp' ) . '</a>';
				$content       = str_replace( '@everyone', $everyone_link, $content );

			}

			return $content;
		}

		/**
		 * @param array $data
		 * @param array $postarr
		 *
		 * @return array
		 */
		public function insert_post_data( $data, $postarr ) {
			if ( ! isset( $postarr['post_type'] ) ||
				! in_array( $postarr['post_type'], array( 'fmwp_forum', 'fmwp_topic', 'fmwp_reply' ), true ) ) {
				return $data;
			}

			if ( isset( $postarr['meta_input']['fmwp_original_content'] ) ) {
				return $data;
			}

			$this->origin_content = $data['post_content'];

			$data['post_content'] = $this->mention_links( $data['post_content'], $postarr );

			return $data;
		}


		/**
		 * @param int $post_ID
		 * @param WP_Post $post
		 */
		public function update_origin_content( $post_ID, $post ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! in_array( $post->post_type, array( 'fmwp_forum', 'fmwp_topic', 'fmwp_reply' ), true ) ) {
				return;
			}

			$original_meta = get_post_meta( $post_ID, 'fmwp_original_content', true );

			if ( ! empty( $post->post_content ) && empty( $original_meta ) ) {
				$original_meta = ! empty( $this->origin_content ) ? $this->origin_content : $post->post_content;
				update_post_meta( $post_ID, 'fmwp_original_content', $original_meta );

				$this->origin_content = '';
			} elseif ( ! empty( $this->origin_content ) ) {
				update_post_meta( $post_ID, 'fmwp_original_content', $this->origin_content );

				$this->origin_content = '';
			}

			if ( ! empty( $this->users_mentioned ) ) {

				/* Include users for @everyone and @all mention by admin */

				if ( 'fmwp_reply' === $post->post_type && in_array( 0, $this->users_mentioned, true ) ) {

					if ( user_can( $post->post_author, 'manage_options' ) ) {
						$t_id  = $this->reply()->get_topic_id( $post_ID );
						$topic = get_post( $t_id );

						if ( ! empty( $topic ) ) {
							if ( $post->post_author !== $topic->post_author ) {
								$this->users_mentioned[] = $topic->post_author;
							}

							$replies = get_posts(
								array(
									'post_type'        => 'fmwp_reply',
									'posts_per_page'   => -1,
									'post_status'      => 'publish',
									'suppress_filters' => false,
									'meta_query'       => array(
										array(
											'key'     => 'fmwp_topic',
											'value'   => $t_id,
											'compare' => '=',
										),
									),
								)
							);

							if ( ! empty( $replies ) ) {
								foreach ( $replies as $reply ) {
									if ( $this->reply()->is_spam( $reply ) ) {
										continue;
									}

									if ( $post->post_author !== $reply->post_author ) {
										$this->users_mentioned[] = $reply->post_author;
									}
								}
							}
						}
					}
				} // end mention all

				$zero_user = array_search( 0, $this->users_mentioned, true );
				if ( false !== $zero_user ) {
					unset( $this->users_mentioned[ $zero_user ] );
				}

				$this->users_mentioned = array_values( array_unique( $this->users_mentioned ) );

				update_post_meta( $post_ID, 'fmwp_original_mentions', $this->users_mentioned );
				$this->users_mentioned = array();
			}
		}

		public function delete_sub_posts( $post_id, $post ) {
			if ( 'fmwp_forum' === $post->post_type ) {
				$topic_ids = get_posts(
					array(
						'post_type'      => 'fmwp_topic',
						'posts_per_page' => -1,
						'post_status'    => array( 'any', 'trash' ),
						'meta_query'     => array(
							array(
								'key'   => 'fmwp_forum',
								'value' => $post_id,
							),
						),
						'fields'         => 'ids',
					)
				);

				if ( ! empty( $topic_ids ) ) {
					foreach ( $topic_ids as $topic_id ) {
						wp_delete_post( $topic_id, true );
					}
				}
			} elseif ( 'fmwp_topic' === $post->post_type ) {
				$forum_id = FMWP()->common()->topic()->get_forum_id( $post->ID );
				update_post_meta( $forum_id, 'fmwp_last_update', time() );

				$replies_ids = get_posts(
					array(
						'post_type'      => 'fmwp_reply',
						'posts_per_page' => -1,
						'post_status'    => array( 'any', 'trash' ),
						'meta_query'     => array(
							'topic' => array(
								'key'   => 'fmwp_topic',
								'value' => $post->ID,
							),
						),
						'fields'         => 'ids',
					)
				);

				if ( ! empty( $replies_ids ) ) {
					foreach ( $replies_ids as $reply_id ) {
						wp_delete_post( $reply_id, true );
					}
				}
			} elseif ( 'fmwp_reply' === $post->post_type ) {
				$topic_id = get_post_meta( $post->ID, 'fmwp_topic', true );

				update_post_meta( $topic_id, 'fmwp_last_update', time() );
				$forum_id = FMWP()->common()->topic()->get_forum_id( $topic_id );
				update_post_meta( $forum_id, 'fmwp_last_update', time() );

				$sub_delete = FMWP()->options()->get( 'reply_delete' );
				if ( 'sub_delete' === $sub_delete ) {
					$child_replies = get_posts(
						array(
							'post_parent'    => $post->ID,
							'post_type'      => 'fmwp_reply',
							'posts_per_page' => -1,
							'post_status'    => array( 'any', 'trash' ),
							'fields'         => 'ids',
						)
					);

					if ( ! empty( $child_replies ) ) {
						foreach ( $child_replies as $sub_id ) {
							wp_delete_post( $sub_id, true );
						}
					}
				} elseif ( 'change_level' === $sub_delete ) {
					$request       = array(
						'post_parent'    => $post->ID,
						'post_type'      => 'fmwp_reply',
						'posts_per_page' => -1,
						'post_status'    => array( 'any', 'trash' ),
						'fields'         => 'ids',
					);
					$child_replies = get_posts( $request );

					if ( ! empty( $child_replies ) ) {
						foreach ( $child_replies as $sub_id ) {
							wp_update_post(
								array(
									'ID'          => $sub_id,
									'post_parent' => $post->post_parent,
								)
							);
						}
					}
				}
			}
		}


		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \FMWP::includes()
		 */
		public function includes() {
			$this->login();
			$this->rewrite();
			$this->forum();
			$this->topic();
			$this->reply();
			$this->blocks();
		}


		/**
		 * Loaded core hook for external integrations
		 */
		public function on_plugins_loaded() {
			do_action( 'forumwp_loaded' );
		}


		/**
		 *
		 * @uses Common::set_variables()
		 * @uses Common::localize()
		 */
		public function init() {
			do_action( 'before_forumwp_init' );

			$this->set_variables();

			$this->create_post_types();
			$this->register_post_statuses();

			do_action( 'forumwp_init' );
		}


		/**
		 * @used-by Common::init()
		 */
		public function set_variables() {
			if ( get_option( 'permalink_structure' ) ) {
				FMWP()->is_permalinks = true;
			}

			$this->mail()->paths = apply_filters( 'fmwp_email_templates_extends', array() );

			FMWP()->templates_path  = FMWP_PATH . 'templates' . DIRECTORY_SEPARATOR;
			FMWP()->theme_templates = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'forumwp' . DIRECTORY_SEPARATOR;
		}

		/**
		 * @since 2.0
		 *
		 * @return Login
		 */
		public function login() {
			if ( empty( FMWP()->classes['fmwp\common\login'] ) ) {
				FMWP()->classes['fmwp\common\login'] = new Login();
			}

			return FMWP()->classes['fmwp\common\login'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Rewrite
		 */
		public function rewrite() {
			if ( empty( FMWP()->classes['fmwp\common\rewrite'] ) ) {
				FMWP()->classes['fmwp\common\rewrite'] = new Rewrite();
			}

			return FMWP()->classes['fmwp\common\rewrite'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Mail
		 */
		public function mail() {
			if ( empty( FMWP()->classes['fmwp\common\mail'] ) ) {
				FMWP()->classes['fmwp\common\mail'] = new Mail();
			}

			return FMWP()->classes['fmwp\common\mail'];
		}


		/**
		 * @since 1.1
		 *
		 * @return Filesystem
		 */
		public function filesystem() {
			if ( empty( FMWP()->classes['fmwp\common\filesystem'] ) ) {
				FMWP()->classes['fmwp\common\filesystem'] = new Filesystem();
			}
			return FMWP()->classes['fmwp\common\filesystem'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Options
		 */
		public function options() {
			if ( empty( FMWP()->classes['fmwp\common\options'] ) ) {
				FMWP()->classes['fmwp\common\options'] = new Options();
			}
			return FMWP()->classes['fmwp\common\options'];
		}


		/**
		 * @param array $meta
		 * @param \WP_User $user
		 * @param bool $update
		 *
		 * @return mixed
		 */
		public function update_user_permalink( $meta, $user, $update ) {
			if ( $update ) {
				$slug = get_user_meta( $user->ID, 'fmwp_permalink', true );
				if ( ! empty( $slug ) ) {
					return $meta;
				}
			}

			$meta['fmwp_permalink'] = FMWP()->user()->get_unique_permalink( $user );
			return $meta;
		}


		/**
		 * @param array $mce_buttons
		 * @param int $editor_id
		 *
		 * @return array
		 */
		public function filter_mce_buttons( $mce_buttons, $editor_id ) {
			$mce_buttons = array_diff( $mce_buttons, array( 'alignright', 'alignleft', 'aligncenter', 'wp_adv', 'wp_more', 'fullscreen', 'formatselect', 'spellchecker' ) );
			$mce_buttons = apply_filters( 'fmwp_rich_text_editor_buttons', $mce_buttons, $editor_id, $this );

			return $mce_buttons;
		}


		/**
		 * @param string $post_type
		 * @param string $content
		 */
		public function render_editor( $post_type, $content = '' ) {
			add_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ), 10, 2 );

			add_action(
				'after_wp_tiny_mce',
				static function ( $settings ) use ( $post_type ) {
					if ( isset( $settings[ 'fmwp' . $post_type . 'content' ]['plugins'] ) && false !== strpos( $settings[ 'fmwp' . $post_type . 'content' ]['plugins'], 'wplink' ) ) {
						echo '<style>
						#link-selector > .howto, #link-selector > #search-panel { display:none; }
					</style>';
					}
				}
			);

			if ( FMWP()->options()->get( 'raw_html_enabled' ) ) {
				$tinymce = array(
					'init_instance_callback' => "function (editor) {
													editor.on( 'keyup paste mouseover', function (e) {
													var content = editor.getContent( { format: 'html' } ).trim();
													var textarea = jQuery( '#' + editor.id );
													textarea.val( content ).trigger( 'keyup' ).trigger( 'keypress' ).trigger( 'keydown' ).trigger( 'change' ).trigger( 'paste' ).trigger( 'mouseover' );
												});}",
				);
			} else {
				$tinymce = false;
			}

			$editor_settings = apply_filters(
				'fmwp_content_editor_options',
				array(
					'textarea_name' => 'fmwp-' . $post_type . '[content]',
					'wpautop'       => true,
					'editor_height' => 145,
					'media_buttons' => false,
					'quicktags'     => false,
					'tinymce'       => $tinymce,
				)
			);

			wp_editor( $content, 'fmwp' . $post_type . 'content', $editor_settings );

			remove_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ) );
		}


		/**
		 * @param int $post_ID
		 * @param WP_Post $post
		 * @param bool $update
		 */
		public function mention_notification( $post_ID, $post, $update ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! in_array( $post->post_type, array( 'fmwp_forum', 'fmwp_topic', 'fmwp_reply' ), true ) ) {
				return;
			}

			if ( 'auto-draft' === $post->post_status ) {
				return;
			}

			if ( 'fmwp_topic' === $post->post_type ) {
				$f_id = $this->topic()->get_forum_id( $post_ID );
				if ( empty( $f_id ) ) {
					return;
				}
			}

			$author = get_userdata( $post->post_author );

			$users = get_post_meta( $post_ID, 'fmwp_original_mentions', true );
			$users = ! empty( $users ) ? $users : array();
			$users = apply_filters( 'fmwp_notify_mentioned_users_list', $users, $post );
			$users = array_unique( $users );

			$need_mention = array();

			if ( ! $update ) {
				if ( 'publish' !== $post->post_status ) {

					if ( empty( $users ) ) {
						return;
					}

					foreach ( $users as $user_id ) {
						if ( absint( $user_id ) === absint( $post->post_author ) ) {
							continue;
						}

						if ( 'fmwp_reply' === $post->post_type ) {
							if ( FMWP()->user()->can_view_reply( $user_id, $post_ID ) ) {
								continue;
							}
						} elseif ( 'fmwp_topic' === $post->post_type ) {
							if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
								if ( 'pending' === $post->post_status || 'private' === $post->post_status ) {
									continue;
								}
							}
						} elseif ( 'fmwp_forum' === $post->post_type ) {
							if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
								if ( 'pending' === $post->post_status || 'private' === $post->post_status ) {
									continue;
								}
							}
						}

						$need_mention[] = $user_id;
					}

					update_post_meta( $post_ID, 'fmwp_need_mention', $need_mention );
				}
			} elseif ( 'publish' === $post->post_status ) {
					$need_mention = get_post_meta( $post_ID, 'fmwp_need_mention', true );
					$need_mention = ! empty( $need_mention ) ? $need_mention : array();

				if ( ! empty( $need_mention ) ) {
					$users = array_unique( array_merge( $users, $need_mention ) );

					delete_post_meta( $post_ID, 'fmwp_need_mention' );

					$need_mention = array();
				}
			} else {
				if ( empty( $users ) ) {
					return;
				}

				foreach ( $users as $user_id ) {
					if ( absint( $user_id ) === absint( $post->post_author ) ) {
						continue;
					}

					if ( 'fmwp_reply' === $post->post_type ) {
						if ( FMWP()->user()->can_view_reply( $user_id, $post_ID ) ) {
							continue;
						}
					} elseif ( 'fmwp_topic' === $post->post_type ) {
						if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
							if ( 'pending' === $post->post_status || 'private' === $post->post_status ) {
								continue;
							}
						}
					} elseif ( 'fmwp_forum' === $post->post_type ) {
						if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
							if ( 'pending' === $post->post_status || 'private' === $post->post_status ) {
								continue;
							}
						}
					}

					$need_mention[] = $user_id;
				}

				update_post_meta( $post_ID, 'fmwp_need_mention', $need_mention );
			}

			if ( empty( $users ) ) {
				return;
			}

			$mentioned_users = get_post_meta( $post_ID, 'fmwp_mentioned', true );
			$mentioned_users = ! empty( $mentioned_users ) ? $mentioned_users : array();

			foreach ( $users as $user_id ) {
				if ( absint( $user_id ) === absint( $post->post_author ) ) {
					continue;
				}

				if ( in_array( $user_id, $mentioned_users, true ) ) {
					continue;
				}

				$userdata = get_userdata( $user_id );

				$email_args = array(
					'post_id'      => $post_ID,
					'post_content' => ! empty( $post->post_password ) ? '' : $post->post_content,
					'login_url'    => $this->get_preset_page_link( 'login' ),
					'site_name'    => get_bloginfo( 'name' ),
					'site_url'     => get_bloginfo( 'url' ),
					'author_name'  => FMWP()->user()->display_name( $author ),
					'author_url'   => FMWP()->user()->get_profile_link( $author->ID ),
					'recipient_id' => $user_id,
				);

				$send = true;

				if ( 'fmwp_reply' === $post->post_type ) {
					$topic_id = FMWP()->common()->reply()->get_topic_id( $post_ID );
					$topic    = get_post( $topic_id );

					$forum_id = FMWP()->common()->topic()->get_forum_id( $topic_id );
					$forum    = get_post( $forum_id );

					$send = FMWP()->user()->can_view_reply( $user_id, $post_ID );

					$email_args['topic_url']   = get_permalink( $topic_id ); // backward compatibility
					$email_args['topic_title'] = $topic->post_title; // backward compatibility
					$email_args['post_url']    = get_permalink( $topic_id );
					$email_args['post_title']  = $topic->post_title;

					if ( ! empty( $topic->post_password ) || ! empty( $forum->post_password ) ) {
						$email_args['post_content'] = '';
					}
				} elseif ( 'fmwp_topic' === $post->post_type ) {
					$topic_id = $post_ID;
					$topic    = $post;

					$forum_id = FMWP()->common()->topic()->get_forum_id( $topic_id );
					$forum    = get_post( $forum_id );

					if ( 'publish' !== $forum->post_status ) {
						$send = false;
						if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
							if ( 'pending' === $forum->post_status || 'private' === $forum->post_status ) {
								$send = true;
							}
						}
					}

					if ( $send ) {
						if ( 'publish' !== $post->post_status ) {
							$send = false;
							if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
								if ( 'pending' === $post->post_status || 'private' === $post->post_status ) {
									$send = true;
								}
							}
						}
					}

					$email_args['topic_url']   = get_permalink( $topic_id ); // backward compatibility
					$email_args['topic_title'] = $topic->post_title; // backward compatibility
					$email_args['post_url']    = get_permalink( $topic_id );
					$email_args['post_title']  = $topic->post_title;

					if ( ! empty( $forum->post_password ) ) {
						$email_args['post_content'] = '';
					}
				} elseif ( 'fmwp_forum' === $post->post_type ) {
					if ( 'publish' !== $post->post_status ) {
						$send = false;
						if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
							if ( 'pending' === $post->post_status || 'private' === $post->post_status ) {
								$send = true;
							}
						}
					}

					$email_args['post_url']   = get_permalink( $post->ID );
					$email_args['post_title'] = $post->post_title;
				}

				$send = apply_filters( 'fmwp_send_mention_email', $send, $post_ID, $post, $update );

				if ( $send ) {
					$email_args = apply_filters( 'fmwp_mentions_email_args', $email_args, $post_ID, $post, $update );
					FMWP()->common()->mail()->send( $userdata->user_email, 'mention', $email_args );
				}
			}

			// update mentioned users, don't remove old mentioned users. Avoid re-send mentioned email notification
			$mentioned_users = array_diff( array_unique( array_merge( $mentioned_users, $users ) ), $need_mention );
			update_post_meta( $post_ID, 'fmwp_mentioned', $mentioned_users );
		}

		/**
		 * @param WP_Admin_Bar $wp_admin_bar
		 */
		public function new_cpt_links( $wp_admin_bar ) {
			if ( ! is_user_logged_in() ) {
				return;
			}

			if ( current_user_can( 'create_fmwp_forums' ) ) {
				$wp_admin_bar->add_menu(
					array(
						'parent' => 'new-content',
						'id'     => 'new-fmwp_forum',
						'title'  => __( 'Forum', 'forumwp' ),
						'href'   => add_query_arg( array( 'post_type' => 'fmwp_forum' ), admin_url( 'post-new.php' ) ),
					)
				);
			}

			if ( current_user_can( 'create_fmwp_topics' ) ) {
				$wp_admin_bar->add_menu(
					array(
						'parent' => 'new-content',
						'id'     => 'new-fmwp_topic',
						'title'  => __( 'Topic', 'forumwp' ),
						'href'   => add_query_arg( array( 'post_type' => 'fmwp_topic' ), admin_url( 'post-new.php' ) ),
					)
				);
			}
		}


		/**
		 * @param WP_Admin_Bar $wp_admin_bar
		 */
		public function toolbar_links( $wp_admin_bar ) {
			global $post;

			if ( ! is_user_logged_in() ) {
				return;
			}

			if ( ! is_singular( array( 'fmwp_forum', 'fmwp_topic' ) ) ) {
				return;
			}

			$args = array();

			if ( is_singular( 'fmwp_forum' ) ) {

				if ( current_user_can( 'edit_post', $post->ID ) ) {

					$args = array(
						'id'    => 'fmwp_edit_forum',
						'title' => '<span class="ab-icon"></span>' . __( 'Edit Forum', 'forumwp' ),
						'href'  => get_edit_post_link(),
						'meta'  => array(
							'class' => 'fmwp-child-toolbar',
						),
					);
				}
			} elseif ( is_singular( 'fmwp_topic' ) ) {

				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$args = array(
						'id'    => 'fmwp_edit_topic',
						'title' => '<span class="ab-icon"></span>' . __( 'Edit Topic', 'forumwp' ),
						'href'  => get_edit_post_link(),
						'meta'  => array(
							'class' => 'fmwp-child-toolbar',
						),
					);
				}
			}

			$wp_admin_bar->add_node( $args );
		}

		/**
		 * Create CPT & Taxonomies
		 */
		public function create_post_types() {
			$cpt = FMWP()->get_cpt();
			foreach ( $cpt as $post_type => $args ) {
				register_post_type( $post_type, $args );
			}

			$taxonomies = FMWP()->get_taxonomies();
			foreach ( $taxonomies as $key => $taxonomy ) {
				register_taxonomy( $key, $taxonomy['post_types'], $taxonomy['tax_args'] );
			}
		}

		public function register_post_statuses() {
			$order_statuses = FMWP()->get_post_statuses();

			foreach ( $order_statuses as $order_status => $values ) {
				register_post_status( $order_status, $values );
			}
		}

		/**
		 * @param string $slug
		 *
		 * @return int
		 */
		public function get_preset_page_id( $slug ) {
			$page_id = FMWP()->options()->get( $slug . '_page' );

			return (int) $page_id;
		}

		/**
		 * @param string $slug
		 *
		 * @return false|string
		 */
		public function get_preset_page_link( $slug ) {
			$page_id = $this->get_preset_page_id( $slug );
			return get_permalink( $page_id );
		}

		/**
		 * @param $type
		 *
		 * @return int
		 */
		public function get_type_order( $type ) {
			if ( ! array_key_exists( $type, FMWP()->common()->topic()->types ) ) {
				return 0;
			}

			return FMWP()->common()->topic()->types[ $type ]['order'];
		}



		/**
		 * @since 1.0
		 *
		 * @return Forum_Category
		 */
		public function forum_category() {
			if ( empty( FMWP()->classes['fmwp\common\forum_category'] ) ) {
				FMWP()->classes['fmwp\common\forum_category'] = new Forum_Category();
			}

			return FMWP()->classes['fmwp\common\forum_category'];
		}


		/**
		 * @since 2.0
		 *
		 * @return Post
		 */
		public function post() {
			if ( empty( FMWP()->classes['fmwp\common\post'] ) ) {
				FMWP()->classes['fmwp\common\post'] = new Post();
			}

			return FMWP()->classes['fmwp\common\post'];
		}


		/**
		 * @since 2.0
		 *
		 * @return Forum
		 */
		public function forum() {
			if ( empty( FMWP()->classes['fmwp\common\forum'] ) ) {
				FMWP()->classes['fmwp\common\forum'] = new Forum();
			}

			return FMWP()->classes['fmwp\common\forum'];
		}


		/**
		 * @since 2.0
		 *
		 * @return Topic
		 */
		public function topic() {
			if ( empty( FMWP()->classes['fmwp\common\topic'] ) ) {
				FMWP()->classes['fmwp\common\topic'] = new Topic();
			}

			return FMWP()->classes['fmwp\common\topic'];
		}


		/**
		 * @since 2.0
		 *
		 * @return Reply
		 */
		public function reply() {
			if ( empty( FMWP()->classes['fmwp\common\reply'] ) ) {
				FMWP()->classes['fmwp\common\reply'] = new Reply();
			}

			return FMWP()->classes['fmwp\common\reply'];
		}

		/**
		 * @since 2.1.1
		 *
		 * @return Blocks
		 */
		public function blocks() {
			if ( empty( FMWP()->classes['fmwp\common\blocks'] ) ) {
				FMWP()->classes['fmwp\common\blocks'] = new Blocks();
			}

			return FMWP()->classes['fmwp\common\blocks'];
		}

		/**
		 * Loading FMWP textdomain, 'forumwp' by default
		 *
		 * @used-by Common::init()
		 *
		 * @deprecated 2.1.3
		 */
		public function localize() {
			_deprecated_function( __METHOD__, '2.1.3' );
		}
	}
}
