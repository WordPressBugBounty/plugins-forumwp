<?php
namespace fmwp\common;

use WP_Error;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Forum' ) ) {

	/**
	 * Class Forum
	 *
	 * @package fmwp\common
	 */
	class Forum extends Post {

		/**
		 * @var array
		 */
		public $statuses = array();

		/**
		 * @var array
		 */
		public $visibilities = array();

		/**
		 * @var array
		 */
		public $post_status;

		/**
		 * Forum constructor.
		 */
		public function __construct() {
			$this->visibilities = array(
				'public'  => __( 'Public', 'forumwp' ),
				'private' => __( 'Private', 'forumwp' ),
				'hidden'  => __( 'Hidden', 'forumwp' ),
			);

			add_action( 'init', array( &$this, 'init_statuses' ) );

			add_action( 'save_post_fmwp_forum', array( &$this, 'save_post' ), 999997 );

			add_filter( 'the_posts', array( &$this, 'filter_private_forums' ), 99 );
			add_filter( 'posts_where', array( &$this, 'filter_trashed_for_author' ), 10, 2 );

			add_action( 'transition_post_status', array( &$this, 'trash' ), 10, 3 );
		}

		/**
		 * Make invisible private and hidden forums
		 *
		 * @param $posts
		 *
		 * @return array
		 */
		public function filter_private_forums( $posts ) {
			if ( FMWP()->is_request( 'admin' ) && ! FMWP()->is_request( 'ajax' ) ) {
				return $posts;
			}

			//if empty
			if ( empty( $posts ) ) {
				return $posts;
			}

			if ( is_user_logged_in() && current_user_can( 'manage_fmwp_forums_all' ) ) {
				return $posts;
			}

			$filtered_posts = array();

			foreach ( $posts as $post ) {
				if ( 'fmwp_forum' !== $post->post_type ) {
					$filtered_posts[] = $post;
					continue;
				}

				$visibility = get_post_meta( $post->ID, 'fmwp_visibility', true );
				if ( 'public' === $visibility ) {
					$filtered_posts[] = $post;
				}
			}

			return $filtered_posts;
		}

		/**
		 * @param $where
		 * @param $wp_query
		 *
		 * @return mixed
		 */
		public function filter_trashed_for_author( $where, $wp_query ) {
			if ( isset( $wp_query->query['post_type'] ) && 'fmwp_forum' === $wp_query->query['post_type'] ) {
				if ( isset( $wp_query->query['post_status'] ) && ( 'trash' === $wp_query->query['post_status'] || ( is_array( $wp_query->query['post_status'] ) && in_array( 'trash', $wp_query->query['post_status'], true ) ) ) ) {
					global $wpdb;
					if ( ! current_user_can( 'manage_fmwp_forums_all' ) ) {
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

		/**
		 * @param int $post_ID
		 */
		public function save_post( $post_ID ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			$upgrade_last_update = apply_filters( 'fmwp_forum_upgrade_last_update', true, $post_ID );

			if ( $upgrade_last_update ) {
				update_post_meta( $post_ID, 'fmwp_last_update', time() );
			}
		}

		/**
		 * Set statuses
		 */
		public function init_statuses() {
			$this->post_status = array( 'publish' );

			if ( is_user_logged_in() ) {
				$this->post_status[] = 'trash'; // will be visible for trash author or `manage_fmwp_forums_all`. See `$this->filter_trashed_for_author()`.
				if ( current_user_can( 'manage_fmwp_forums_all' ) ) {
					$this->post_status[] = 'private';
					$this->post_status[] = 'pending';
				}
			}
		}

		/**
		 * @param WP_Post $forum
		 * @param bool|int $user_id
		 *
		 * @return array
		 */
		public function actions_list( $forum, $user_id = false ) {
			//Forum dropdown actions
			$items = array();

			if ( ! $user_id ) {
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
				} else {
					return $items;
				}
			}

			if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
				if ( $this->is_locked( $forum ) ) {
					$items = array_merge(
						$items,
						array(
							'fmwp-unlock-forum' => __( 'Unlock forum', 'forumwp' ),
						)
					);
				} else {
					$items = array_merge(
						$items,
						array(
							'fmwp-lock-forum' => __( 'Lock forum', 'forumwp' ),
						)
					);
				}
			}

			if ( FMWP()->user()->can_trash_forum( $user_id, $forum ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-trash-forum' => __( 'Move to trash', 'forumwp' ),
					)
				);
			}

			if ( $this->is_trashed( $forum ) ) {
				if ( FMWP()->user()->can_restore_forum( $user_id, $forum ) || FMWP()->user()->can_delete_forum( $user_id, $forum ) ) {
					if ( ! empty( $items ) ) {
						$items = array();
					}
				}

				if ( FMWP()->user()->can_restore_forum( $user_id, $forum ) ) {
					$items = array_merge(
						$items,
						array(
							'fmwp-restore-forum' => __( 'Restore forum', 'forumwp' ),
						)
					);
				}

				if ( FMWP()->user()->can_delete_forum( $user_id, $forum ) ) {
					$items = array_merge(
						$items,
						array(
							'fmwp-remove-forum' => __( 'Remove forum', 'forumwp' ),
						)
					);
				}
			}

			$items = array_unique( $items );

			foreach ( $items as $key => $title ) {
				$items[ $key ] = array(
					'title'     => $title,
					'entity_id' => $forum->ID,
					'nonce'     => wp_create_nonce( $key . $forum->ID ),
				);
			}

			return $items;
		}

		/**
		 * @param int $forum_id
		 *
		 * @return array|int[]|void|WP_Post|WP_Post[]|null
		 */
		public function get_last_topic( $forum_id ) {
			if ( post_password_required( $forum_id ) ) {
				return;
			}

			$args = array(
				'post_type'      => 'fmwp_topic',
				'posts_per_page' => 1,
				'post_status'    => FMWP()->common()->topic()->post_status,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'desc',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'fmwp_forum',
						'value' => $forum_id,
					),
				),
			);

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_topics_all' ) ) {
				$args['meta_query'][] = array(
					/* Exclude spam topic from latest topic query */
					'relation' => 'OR',
					array(
						'key'     => 'fmwp_spam',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'fmwp_spam',
						'value'   => 1,
						'compare' => '!=',
					),
				);
			}

			$args['suppress_filters'] = false;

			$args = apply_filters( 'fmwp_get_topics_arguments', $args );

			$topic = get_posts( $args );

			if ( ! empty( $topic ) && ! is_wp_error( $topic ) ) {
				$topic = get_post( $topic[0] );
			}

			return $topic;
		}

		/**
		 * @param int $forum_id
		 * @param string $key
		 *
		 * @return int|array
		 */
		public function get_statistics( $forum_id, $key = 'all' ) {
			$stats = array();

			switch ( $key ) {
				case 'topics':
					if ( post_password_required( $forum_id ) ) {
						$stats = 0;
					} else {
						$args = array(
							'post_type'      => 'fmwp_topic',
							'posts_per_page' => -1,
							'post_status'    => FMWP()->common()->topic()->post_status,
							'fields'         => 'ids',
							'meta_query'     => array(
								'relation' => 'AND',
								array(
									'key'   => 'fmwp_forum',
									'value' => $forum_id,
								),
							),
						);

						if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_topics_all' ) ) {
							$args['meta_query'][] = array(
								/* Exclude spam topic from latest topic query */
								'relation' => 'OR',
								array(
									'key'     => 'fmwp_spam',
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => 'fmwp_spam',
									'value'   => 1,
									'compare' => '!=',
								),
							);
						}

						$args['suppress_filters'] = false;

						$args = apply_filters( 'fmwp_get_topics_arguments', $args );

						$topics = get_posts( $args );

						$stats = ( ! empty( $topics ) && ! is_wp_error( $topics ) ) ? count( $topics ) : 0;
					}

					break;
				case 'posts':
					if ( post_password_required( $forum_id ) ) {
						$stats = 0;
					} else {
						$args = array(
							'post_type'      => 'fmwp_topic',
							'posts_per_page' => -1,
							'post_status'    => FMWP()->common()->topic()->post_status,
							'fields'         => 'ids',
							'meta_query'     => array(
								'relation' => 'AND',
								array(
									'key'   => 'fmwp_forum',
									'value' => $forum_id,
								),
							),
						);

						if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_topics_all' ) ) {
							$args['meta_query'][] = array(
								/* Exclude spam topic from latest topic query */
								'relation' => 'OR',
								array(
									'key'     => 'fmwp_spam',
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => 'fmwp_spam',
									'value'   => 1,
									'compare' => '!=',
								),
							);
						}

						$args['suppress_filters'] = false;

						$args = apply_filters( 'fmwp_get_topics_arguments', $args );

						$topics = get_posts( $args );

						$stats = 0;
						foreach ( $topics as $topic_id ) {
							$stats += FMWP()->common()->topic()->get_statistics( $topic_id, 'replies' );
						}
					}
					break;
				case 'all':
					$keys = array(
						'topics',
						'all',
					);
					foreach ( $keys as $attr ) {
						$stats[ $attr ] = $this->get_statistics( $forum_id, $attr );
					}

					break;
			}

			return $stats;
		}

		/**
		 * @param int $forum_id
		 * @param bool|string $data
		 *
		 * @return array|WP_Error
		 */
		public function get_categories( $forum_id, $data = false ) {
			$args = array(
				'orderby' => 'name',
				'order'   => 'ASC',
			);
			if ( 'names' === $data ) {
				$args['fields'] = 'names';
			}

			$terms = wp_get_post_terms(
				$forum_id,
				'fmwp_forum_category',
				$args
			);

			return $terms;
		}

		/**
		 * Lock Forum handler
		 *
		 * @param int $forum_id
		 */
		public function lock( $forum_id ) {
			$post = get_post( $forum_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $forum_id, 'fmwp_locked', true );

			do_action( 'fmwp_after_lock_forum', $forum_id );
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
					return false;
				}
			}

			if ( isset( $post->post_type ) && 'fmwp_forum' === $post->post_type ) {
				$is_locked = get_post_meta( $post->ID, 'fmwp_locked', true );
				$locked    = ! empty( $is_locked );
			}

			return $locked;
		}

		/**
		 * Unlock Forum handler
		 *
		 * @param int $forum_id
		 */
		public function unlock( $forum_id ) {
			$post = get_post( $forum_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( ! $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $forum_id, 'fmwp_locked', false );

			do_action( 'fmwp_after_unlock_forum', $forum_id );
		}

		/**
		 * Move to Trash Forum handler
		 *
		 * @param int $forum_id
		 */
		public function move_to_trash( $forum_id ) {
			$post = get_post( $forum_id );

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

			do_action( 'fmwp_after_trash_forum', $forum_id );
		}

		/**
		 * Restore from Trash Forum handler
		 *
		 * @param $forum_id
		 */
		public function restore( $forum_id ) {
			$post = get_post( $forum_id );

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

			wp_update_post(
				array(
					'ID'          => $post->ID,
					'post_status' => $prev_status,
				)
			);

			delete_post_meta( $post->ID, 'fmwp_prev_status' );

			do_action( 'fmwp_after_restore_forum', $forum_id );
		}

		/**
		 * Delete Topic handler
		 *
		 * @param $forum_id
		 */
		public function delete( $forum_id ) {
			$forum = get_post( $forum_id );

			do_action( 'fmwp_before_delete_forum', $forum_id, $forum );

			if ( ! empty( $forum ) && ! is_wp_error( $forum ) ) {
				wp_delete_post( $forum_id );
				do_action( 'fmwp_after_delete_forum', $forum_id );
			}
		}

		/**
		 * Create Forum
		 *
		 * @param array $data
		 *
		 * @return int
		 */
		public function create( $data ) {
			$author = ! empty( $data['author_id'] ) ? $data['author_id'] : get_current_user_id();

			if ( ! array_key_exists( $data['visibility'], $this->visibilities ) ) {
				$data['visibility'] = 'public';
			}

			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_forum' );

			$args = array(
				'post_type'    => 'fmwp_forum',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_content' => $post_content,
				'post_author'  => $author,
				'meta_input'   => array(
					'fmwp_visibility'       => $data['visibility'],
					'fmwp_original_content' => $orig_content,
				),
			);

			$args = apply_filters( 'fmwp_create_forum_args', $args, $data );

			$forum_id = wp_insert_post( $args );

			if ( ! is_wp_error( $forum_id ) ) {
				if ( FMWP()->options()->get( 'forum_categories' ) ) {
					if ( ! empty( $data['categories'] ) ) {
						if ( ! is_array( $data['categories'] ) ) {
							$list = explode( ',', trim( $data['categories'], ', ' ) );
							$list = array_map( 'trim', $list );

							$data['categories'] = array_filter( $list );
						} else {
							$data['categories'] = array_filter( $data['categories'] );
						}

						$ids = array();
						foreach ( $data['categories'] as $name ) {
							$name = sanitize_text_field( $name );
							$term = get_term_by( 'name', $name, 'fmwp_forum_category' );
							if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
								$ids[] = (int) $term->term_id;
							}
						}

						wp_set_post_terms( $forum_id, $ids, 'fmwp_forum_category' );
					}
				}

				do_action( 'fmwp_forum_create_completed', $forum_id, $data );
			}

			return $forum_id;
		}

		public function trash( $new_status, $old_status, $post ) {
			if ( 'fmwp_forum' === $post->post_type ) {
				if ( 'trash' === $new_status && 'trash' !== $old_status ) {
					update_post_meta( $post->ID, 'fmwp_user_trash_id', get_current_user_id() );
				} elseif ( 'trash' !== $new_status && 'trash' === $old_status ) {
					delete_post_meta( $post->ID, 'fmwp_user_trash_id' );
				}
			}
		}
	}
}
