<?php
namespace fmwp\common;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Reply' ) ) {

	/**
	 * Class Reply
	 *
	 * @package fmwp\common
	 */
	final class Reply extends Post {

		/**
		 * @var array
		 */
		public $post_status;


		public $sort_by;

		/**
		 * Reply constructor.
		 */
		public function __construct() {
			add_action( 'forumwp_init', array( &$this, 'init_variables' ), 9 );

			add_action( 'save_post_fmwp_reply', array( &$this, 'save_post' ), 999997, 3 );
			add_action( 'init', array( &$this, 'init_statuses' ), 10 );

			add_filter( 'posts_where', array( &$this, 'filter_pending_for_author' ), 10, 2 );
			add_filter( 'post_type_link', array( &$this, 'change_link' ), 10, 2 );

			add_filter( 'the_posts', array( &$this, 'filter_visible_replies' ), 99 );

			add_action( 'transition_post_status', array( &$this, 'trash' ), 10, 3 );
		}

		/**
		 * Make invisible replies from trashed forums
		 *
		 * @param $posts
		 *
		 * @return array
		 */
		public function filter_visible_replies( $posts ) {
			if ( FMWP()->is_request( 'admin' ) && ! FMWP()->is_request( 'ajax' ) ) {
				return $posts;
			}

			$filtered_posts = array();

			//if empty
			if ( empty( $posts ) ) {
				return $posts;
			}

			foreach ( $posts as $post ) {
				if ( 'fmwp_reply' !== $post->post_type ) {
					$filtered_posts[] = $post;
					continue;
				}

				if ( FMWP()->user()->can_view_reply( get_current_user_id(), $post->ID ) ) {
					$filtered_posts[] = $post;
				}
			}
			return $filtered_posts;
		}

		/**
		 * @param string $permalink
		 * @param WP_Post $post
		 *
		 * @return string
		 */
		public function change_link( $permalink, $post ) {
			if ( 'fmwp_reply' !== $post->post_type ) {
				return $permalink;
			}

			return $this->get_link( $post->ID );
		}

		/**
		 * @param $where
		 * @param $wp_query
		 *
		 * @return mixed
		 */
		public function filter_pending_for_author( $where, $wp_query ) {
			if ( isset( $wp_query->query['post_type'] ) && 'fmwp_reply' === $wp_query->query['post_type'] ) {
				if ( isset( $wp_query->query['post_status'] ) && ( 'pending' === $wp_query->query['post_status'] || ( is_array( $wp_query->query['post_status'] ) && in_array( 'pending', $wp_query->query['post_status'], true ) ) ) ) {
					global $wpdb;
					if ( ! current_user_can( 'manage_fmwp_replies_all' ) ) {
						$current_user_id = get_current_user_id();

						$where = str_replace( "{$wpdb->posts}.post_status = 'pending'", "( {$wpdb->posts}.post_status = 'pending' AND {$wpdb->posts}.post_author = '" . $current_user_id . "' )", $where );
						$where = str_replace(
							"{$wpdb->posts}.post_status = 'trash'",
							"( {$wpdb->posts}.post_status = 'trash' AND {$wpdb->posts}.post_author = '$current_user_id' )",
							$where
						);
					}
				}
			}
			return $where;
		}

		public function init_variables() {
			$this->sort_by = apply_filters(
				'fmwp_replies_sorting',
				array(
					'date_asc'  => __( 'Oldest to Newest', 'forumwp' ),
					'date_desc' => __( 'Newest to Oldest', 'forumwp' ),
				)
			);
		}

		/**
		 * @param int $reply_id
		 *
		 * @return string
		 */
		public function get_link( $reply_id ) {
			$reply_link = '';

			$topic_id = $this->get_topic_id( $reply_id );

			$topic_link = get_permalink( $topic_id );

			if ( empty( $topic_link ) ) {
				return $reply_link;
			}

			return $topic_link . '#reply-' . $reply_id;
		}

		/**
		 * Get total count of child replies
		 *
		 * @param int $reply_id Parent reply ID
		 *
		 * @return int
		 */
		public function get_child_replies_count( $reply_id ) {
			$total_child = 0;

			$args = array(
				'post_parent'    => $reply_id,
				'post_type'      => 'fmwp_reply',
				'posts_per_page' => -1,
				'post_status'    => $this->post_status,
				'fields'         => 'ids',
			);

			$args['suppress_filters'] = false;

			$args       = apply_filters( 'fmwp_ajax_get_sub_replies_args', $args, $reply_id );
			$subreplies = get_posts( $args );

			if ( ! empty( $subreplies ) && ! is_wp_error( $subreplies ) ) {
				$total_child += count( $subreplies );

				foreach ( $subreplies as $subpost_id ) {
					$total_child += $this->get_child_replies_count( $subpost_id );
				}
			}

			return $total_child;
		}

		/**
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function is_sub( $reply ) {
			$parent_post = get_post( $reply->post_parent );
			if ( empty( $parent_post ) || is_wp_error( $parent_post ) ) {
				return false;
			}

			if ( ! empty( $parent_post->post_parent ) ) {
				return false;
			}

			return true;
		}

		/**
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function is_subsub( $reply ) {
			$parent_post = get_post( $reply->post_parent );
			if ( empty( $parent_post ) || is_wp_error( $parent_post ) ) {
				return false;
			}

			if ( empty( $parent_post->post_parent ) ) {
				return false;
			}

			$subsub_post = get_post( $parent_post->post_parent );
			if ( empty( $subsub_post ) || is_wp_error( $subsub_post ) ) {
				return false;
			}

			return true;
		}

		/**
		 * @param int|WP_Post|array $post
		 *
		 * @return bool
		 */
		public function is_spam( $post ) {
			$spam = false;
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );

				if ( empty( $post ) || is_wp_error( $post ) ) {
					return false;
				}
			}

			if ( 'fmwp_reply' === $post->post_type ) {
				$is_spam = get_post_meta( $post->ID, 'fmwp_spam', true );
				$spam    = ! empty( $is_spam );
			}

			return $spam;
		}

		/**
		 * Set statuses
		 */
		public function init_statuses() {
			$this->post_status = array( 'publish' );
			if ( is_user_logged_in() ) {
				$this->post_status[] = 'pending';
				$this->post_status[] = 'trash';
			}
		}

		/**
		 * @param int $post_ID
		 * @param WP_Post $post
		 * @param bool $update
		 */
		public function save_post( $post_ID, $post, $update ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			$upgrade_last_update = apply_filters( 'fmwp_reply_upgrade_last_update', true, $post_ID );

			if ( $upgrade_last_update ) {
				$topic_id = $this->get_topic_id( $post_ID );
				if ( ! empty( $topic_id ) ) {
					update_post_meta( $topic_id, 'fmwp_last_update', time() );

					$forum_id = FMWP()->common()->topic()->get_forum_id( $topic_id );
					if ( ! empty( $forum_id ) ) {
						update_post_meta( $forum_id, 'fmwp_last_update', time() );
					}
				}
			}

			if ( $update ) {
				return;
			}

			update_user_meta( $post->post_author, 'fmwp_latest_reply_date', time() );
		}

		/**
		 * @param WP_Post $reply
		 *
		 * @return array
		 */
		public function build_replies_avatars( $reply ) {
			$child = array();

			$args = array(
				'post_parent'    => $reply->ID,
				'post_type'      => 'fmwp_reply',
				'posts_per_page' => -1,
				'post_status'    => $this->post_status,
				'order_by'       => 'post_date',
				'order'          => 'desc',
				'fields'         => array( 'post_author' ),
			);

			$args['suppress_filters'] = false;

			$args          = apply_filters( 'fmwp_ajax_get_sub_replies_args', $args, $reply->ID );
			$child_replies = get_posts( $args );

			if ( ! empty( $child_replies ) && ! is_wp_error( $child_replies ) ) {
				foreach ( $child_replies as $child_reply ) {

					$author               = get_userdata( $child_reply->post_author );
					$child[ $author->ID ] = array(
						'avatar' => FMWP()->user()->get_avatar( $author->ID, 'inline', 24 ),
					);

					if ( count( $child ) === 3 ) {
						break;
					}
				}
			}

			return array_values( $child );
		}

		/**
		 * Get replies topic ID
		 *
		 * @param $reply_id
		 *
		 * @return int
		 */
		public function get_topic_id( $reply_id ) {
			$topic_id = get_post_meta( $reply_id, 'fmwp_topic', true );
			if ( empty( $topic_id ) ) {
				$topic_id = false;
			}
			return (int) $topic_id;
		}

		/**
		 * @param $reply_id
		 *
		 * @return bool
		 */
		public function has_children( $reply_id ) {
			$args = array(
				'post_parent'    => $reply_id,
				'post_type'      => 'fmwp_reply',
				'posts_per_page' => -1,
				'post_status'    => $this->post_status,
				'fields'         => 'ids',
			);

			$args['suppress_filters'] = false;

			if ( $this->is_child( $reply_id ) ) {
				$args = apply_filters( 'fmwp_ajax_get_subsub_replies_args', $args, $reply_id );
			} else {
				$args = apply_filters( 'fmwp_ajax_get_sub_replies_args', $args, $reply_id );
			}

			$child_replies = get_posts( $args );

			return ! empty( $child_replies ) && ! is_wp_error( $child_replies );
		}

		/**
		 * @param $reply_id
		 *
		 * @return bool
		 */
		public function is_child( $reply_id ) {
			$reply = get_post( $reply_id );

			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				return false;
			}

			if ( ! empty( $reply->post_parent ) ) {
				$post_parent = get_post( $reply->post_parent );

				if ( empty( $post_parent ) || is_wp_error( $post_parent ) ) {
					return false;
				}

				return true;
			}

			return false;
		}

		/**
		 * @param array $data
		 *
		 * @return int
		 */
		public function create( $data ) {
			$topic  = get_post( $data['topic_id'] );
			$author = ! empty( $data['author_id'] ) ? $data['author_id'] : get_current_user_id();

			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_reply' );

			$args = array(
				'post_type'    => 'fmwp_reply',
				'post_status'  => 'publish',
				// translators: %s is a topic title
				'post_title'   => sprintf( __( 'Reply To: %s', 'forumwp' ), $topic->post_title ),
				'post_content' => $post_content,
				'post_author'  => $author,
				'post_parent'  => isset( $data['post_parent'] ) ? $data['post_parent'] : 0,
				'meta_input'   => array(
					'fmwp_original_content' => $orig_content,
					'fmwp_forum'            => $data['forum_id'],
					'fmwp_topic'            => $data['topic_id'],
				),
			);

			$args = apply_filters( 'fmwp_create_reply_args', $args, $data );

			$reply_id = wp_insert_post( $args );

			if ( ! is_wp_error( $reply_id ) ) {
				do_action( 'fmwp_reply_create_completed', $reply_id );
			}

			return $reply_id;
		}


		/**
		 * @param array $data
		 *
		 * @return int
		 */
		public function edit( $data ) {
			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_reply' );

			$args = array(
				'ID'           => $data['reply_id'],
				'post_content' => $post_content,
				'meta_input'   => array(
					'fmwp_original_content' => $orig_content,
				),
			);

			$args = apply_filters( 'fmwp_edit_reply_args', $args );

			$reply_id = wp_update_post( $args );

			if ( ! is_wp_error( $reply_id ) ) {
				do_action( 'fmwp_reply_edit_completed', $reply_id );
			}

			return $reply_id;
		}


		/**
		 * Spam Reply handler
		 *
		 * @param $reply_id
		 */
		public function spam( $reply_id ) {
			$post = get_post( $reply_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $this->is_spam( $post ) ) {
				return;
			}

			update_post_meta( $post->ID, 'fmwp_spam', true );

			do_action( 'fmwp_after_spam_reply', $reply_id );
		}


		/**
		 * Restore from Trash Reply handler
		 *
		 * @param $reply_id
		 */
		public function restore_spam( $reply_id ) {
			$post = get_post( $reply_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( ! $this->is_spam( $post ) ) {
				return;
			}

			update_post_meta( $post->ID, 'fmwp_spam', false );

			do_action( 'fmwp_after_restore_spam_reply', $post->ID );
		}


		/**
		 * Delete Reply handler
		 *
		 * @param $reply_id
		 */
		public function move_to_trash( $reply_id ) {
			$post = get_post( $reply_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( 'trash' === $post->post_status ) {
				return;
			}

			wp_update_post(
				array(
					'ID'          => $post->ID,
					'post_status' => 'trash',
					'meta_input'  => array(
						'fmwp_prev_status' => $post->post_status,
					),
				)
			);

			do_action( 'fmwp_after_trash_reply', $reply_id );
		}


		/**
		 * Restore from Trash Reply handler
		 *
		 * @param $reply_id
		 */
		public function restore( $reply_id ) {
			$post = get_post( $reply_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( 'trash' !== $post->post_status ) {
				return;
			}

			$prev_status = get_post_meta( $post->ID, 'fmwp_prev_status', true );
			if ( empty( $prev_status ) ) {
				$prev_status = 'publish';
			}

			do_action( 'fmwp_before_restore_reply', $post->ID );

			wp_update_post(
				array(
					'ID'          => $post->ID,
					'post_status' => $prev_status,
				)
			);

			delete_post_meta( $post->ID, 'fmwp_prev_status' );

			do_action( 'fmwp_after_restore_reply', $post->ID );
		}


		/**
		 * Delete Reply handler
		 *
		 * @param int $reply_id
		 * @param array $args
		 *
		 * @return array
		 */
		public function delete( $reply_id, $args = array() ) {
			$reply = get_post( $reply_id );

			$child_replies = array();

			if ( ! empty( $reply ) && ! is_wp_error( $reply ) ) {
				$sub_delete = FMWP()->options()->get( 'reply_delete' );

				if ( 'change_level' === $sub_delete ) {
					$request = array(
						'post_parent'    => $reply_id,
						'post_type'      => 'fmwp_reply',
						'posts_per_page' => -1,
						'post_status'    => array( 'any', 'trash' ),
						'fields'         => 'ids',
					);
					if ( ! empty( $args['order'] ) ) {
						list( $orderby, $order ) = explode( '_', $args['order'] );
						$request['orderby']      = $orderby;
						$request['order']        = $order;
					}
					$child_replies = get_posts( $request );

					if ( empty( $child_replies ) ) {
						$child_replies = array();
					}
				}

				wp_delete_post( $reply_id, true );
			}

			return $child_replies;
		}


		/**
		 * @param int $user_id
		 * @param array $args
		 *
		 * @return array
		 */
		public function get_replies_by_author( $user_id, $args = array() ) {
			$query_args = array(
				'post_type'        => 'fmwp_forum',
				'post_status'      => FMWP()->common()->forum()->post_status,
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'suppress_filters' => false,
			);

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_forums_all' ) ) {
				$query_args['meta_query'][] = array(
					'key'     => 'fmwp_visibility',
					'value'   => 'public',
					'compare' => '=',
				);
			}

			$query_args = apply_filters( 'fmwp_get_forums_arguments', $query_args );

			$forum_ids = get_posts( $query_args );

			if ( empty( $forum_ids ) ) {
				return array();
			}

			foreach ( $forum_ids as $k => $forum_id ) {
				if ( post_password_required( $forum_id ) ) {
					unset( $forum_ids[ $k ] );
				}
			}

			$forum_ids = array_values( $forum_ids );
			if ( empty( $forum_ids ) ) {
				return array();
			}

			$topic_args = array(
				'post_type'        => 'fmwp_topic',
				'posts_per_page'   => -1,
				'post_status'      => FMWP()->common()->topic()->post_status,
				'fields'           => 'ids',
				'meta_query'       => array(
					array(
						'key'     => 'fmwp_forum',
						'value'   => $forum_ids,
						'compare' => 'IN',
					),
				),
				'suppress_filters' => false,
			);

			$topic_args = apply_filters( 'fmwp_get_topics_arguments', $topic_args );

			$topics = get_posts( $topic_args );

			if ( empty( $topics ) || is_wp_error( $topics ) ) {
				return array();
			}

			foreach ( $topics as $k => $topic_id ) {
				if ( post_password_required( $topic_id ) ) {
					unset( $topics[ $k ] );
				}
			}

			$topics = array_values( $topics );

			if ( empty( $topics ) ) {
				return array();
			}

			$args = array_merge(
				array(
					'post_type'        => 'fmwp_reply',
					'posts_per_page'   => -1,
					'post_status'      => FMWP()->common()->reply()->post_status,
					'author'           => $user_id,
					'order'            => 'desc',
					'suppress_filters' => false,
					'meta_query'       => array(
						array(
							'key'     => 'fmwp_topic',
							'value'   => $topics,
							'compare' => 'IN',
						),
					),
				),
				$args
			);

			$args = apply_filters( 'fmwp_ajax_get_replies_by_author_args', $args );

			$replies = get_posts( $args );

			if ( empty( $replies ) || is_wp_error( $replies ) ) {
				$replies = array();
			}

			return $replies;
		}


		/**
		 * @param WP_Post $reply
		 *
		 * @return array
		 */
		public function get_author_tags( $reply ) {
			$tags = array();

			if ( FMWP()->options()->get( 'reply_user_role' ) ) {
				global $wp_roles;
				$user       = get_userdata( $reply->post_author );
				$user_roles = FMWP()->user()->get_roles( $user );

				if ( ! empty( $user_roles ) ) {
					foreach ( $user_roles as $role ) {
						$name   = translate_user_role( $wp_roles->roles[ $role ]['name'] );
						$tags[] = array(
							'title' => $name,
						);
					}
				}
			}

			return $tags;
		}


		public function status_tags() {
			$tags = array();
			if ( is_user_logged_in() ) {
				$tags['pending'] = array(
					'title' => __( 'Pending', 'forumwp' ),
				);

				$tags['trashed'] = array(
					'title' => __( 'Trashed', 'forumwp' ),
				);

				if ( current_user_can( 'manage_fmwp_replies_all' ) ) {
					$tags['spam'] = array(
						'title' => __( 'Spam', 'forumwp' ),
					);
				}

				$tags['reported'] = array(
					'title' => __( 'Reported', 'forumwp' ),
				);
			}

			return apply_filters( 'fmwp_reply_status_tags', $tags );
		}


		/**
		 * @param int|WP_Post|array $post
		 *
		 * @return bool
		 */
		public function is_locked( $post ) {
			$locked = false;
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );

				if ( empty( $post ) || is_wp_error( $post ) ) {
					return $locked;
				}
			}

			if ( 'fmwp_reply' === $post->post_type ) {
				$topic_id = $this->get_topic_id( $post->ID );

				if ( FMWP()->common()->topic()->is_locked( $topic_id ) ) {
					$locked = true;
				} else {
					$forum_id = FMWP()->common()->topic()->get_forum_id( $topic_id );
					if ( FMWP()->common()->forum()->is_locked( $forum_id ) ) {
						$locked = true;
					}
				}
			}

			return $locked;
		}


		/**
		 *
		 * @param WP_Post $reply
		 * @param int|bool $user_id
		 *
		 * @return array
		 */
		public function actions_list( $reply, $user_id = false ) {
			//Topic dropdown actions
			$items = array();

			if ( ! $user_id ) {
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
				} else {
					return $items;
				}
			}

			if ( FMWP()->user()->can_edit_reply( $user_id, $reply ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-edit-reply' => __( 'Edit reply', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_trash_reply( $user_id, $reply ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-trash-reply' => __( 'Move to trash', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_spam_reply( $user_id, $reply ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-mark-spam-reply' => __( 'Mark as spam', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_restore_spam_reply( $user_id, $reply ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-restore-spam-reply' => __( 'Isn\'t spam', 'forumwp' ),
					)
				);
			}

			if ( 'trash' !== $reply->post_status ) {
				if ( absint( $reply->post_author ) !== $user_id ) {
					if ( ! FMWP()->reports()->is_reported_by_user( $reply->ID, $user_id ) ) {
						$items = array_merge(
							$items,
							array(
								'fmwp-report-reply' => __( 'Report reply', 'forumwp' ),
							)
						);
					} else {
						$items = array_merge(
							$items,
							array(
								'fmwp-unreport-reply' => __( 'Un-report reply', 'forumwp' ),
							)
						);
					}
				}

				if ( FMWP()->reports()->is_reported( $reply->ID ) && FMWP()->user()->can_clear_reports( $user_id ) ) {
					$items = array_merge(
						$items,
						array(
							'fmwp-clear-reports-reply' => __( 'Clear reply\'s reports', 'forumwp' ),
						)
					);
				}
			}

			if ( FMWP()->user()->can_restore_reply( $user_id, $reply ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-restore-reply' => __( 'Restore reply', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_delete_reply( $user_id, $reply ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-remove-reply' => __( 'Remove reply', 'forumwp' ),
					)
				);
			}

			$items = apply_filters( 'fmwp_reply_dropdown_actions', $items, $user_id, $reply );
			$items = array_unique( $items );

			foreach ( $items as $key => $title ) {
				$items[ $key ] = array(
					'title'     => $title,
					'entity_id' => $reply->ID,
					'nonce'     => wp_create_nonce( $key . $reply->ID ),
				);
			}

			return $items;
		}

		public function get_reported_count() {
			return 0;
		}

		public function trash( $new_status, $old_status, $post ) {
			if ( 'fmwp_reply' === $post->post_type ) {
				if ( 'trash' === $new_status && 'trash' !== $old_status ) {
					update_post_meta( $post->ID, 'fmwp_user_trash_id', get_current_user_id() );
				} elseif ( 'trash' !== $new_status && 'trash' === $old_status ) {
					delete_post_meta( $post->ID, 'fmwp_user_trash_id' );
				}
			}
		}
	}
}
