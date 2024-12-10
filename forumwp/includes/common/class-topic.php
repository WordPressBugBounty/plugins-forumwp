<?php
namespace fmwp\common;

use WP_Error;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Topic' ) ) {

	/**
	 * Class Topic
	 *
	 * @package fmwp\common
	 */
	final class Topic extends Post {

		/**
		 * @var array
		 */
		public $statuses = array();

		/**
		 * @var array
		 */
		public $types = array();

		/**
		 * @var array
		 */
		public $status_markers = array();

		/**
		 * @var array
		 */
		public $sort_by = array();

		public $post_status = array();

		/**
		 * Rewrite constructor.
		 */
		public function __construct() {
			add_action( 'forumwp_init', array( &$this, 'init_variables' ), 9 );

			add_action( 'save_post_fmwp_topic', array( &$this, 'save_post' ), 999997, 3 );

			add_action( 'wp_head', array( &$this, 'views_increment' ) );

			add_filter( 'the_posts', array( &$this, 'filter_topics_from_hidden_forums' ), 99, 2 );

			add_filter( 'posts_where', array( &$this, 'filter_pending_for_author' ), 10, 2 );

			add_action( 'init', array( &$this, 'init_statuses' ), 10 );

			add_action( 'transition_post_status', array( &$this, 'trash' ), 10, 3 );
		}


		/**
		 * Set statuses
		 */
		public function init_statuses() {
			$this->post_status = array( 'publish' );
			if ( is_user_logged_in() ) {
				$this->post_status[] = 'trash';
				$this->post_status[] = 'pending'; //pending can be visible for author
				if ( current_user_can( 'manage_fmwp_topics_all' ) ) {
					$this->post_status[] = 'private';
				}
			}
		}


		/**
		 * @param $where
		 * @param $wp_query
		 *
		 * @return mixed
		 */
		public function filter_pending_for_author( $where, $wp_query ) {
			if ( isset( $wp_query->query['post_type'] ) && 'fmwp_topic' === $wp_query->query['post_type'] ) {
				if ( isset( $wp_query->query['post_status'] ) && ( 'pending' === $wp_query->query['post_status'] || ( is_array( $wp_query->query['post_status'] ) && in_array( 'pending', $wp_query->query['post_status'], true ) ) ) ) {
					global $wpdb;
					if ( ! current_user_can( 'manage_fmwp_topics_all' ) ) {
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
		 * Make invisible topics from trashed forums
		 *
		 * @param $posts
		 * @param $query
		 *
		 * @return array
		 */
		public function filter_topics_from_hidden_forums( $posts, $query ) {
			if ( FMWP()->is_request( 'admin' ) && ! FMWP()->is_request( 'ajax' ) ) {
				return $posts;
			}

			$filtered_posts = array();

			//if empty
			if ( empty( $posts ) ) {
				return $posts;
			}

			foreach ( $posts as $post ) {

				if ( 'fmwp_topic' !== $post->post_type ) {
					$filtered_posts[] = $post;
					continue;
				}

				if ( ! current_user_can( 'manage_fmwp_topics_all' ) ) {
					if ( $this->is_spam( $post ) ) {
						continue;
					}
				}

				$forum_id = $this->get_forum_id( $post->ID );
				$forum    = get_post( $forum_id );

				if ( in_array( $forum->post_status, array( 'private', 'pending' ), true ) && current_user_can( 'manage_fmwp_forums_all' ) ) {
					$filtered_posts[] = $post;
					continue;
				}

				if ( 'publish' === $forum->post_status ) {
					if ( current_user_can( 'manage_fmwp_forums_all' ) ) {
						$filtered_posts[] = $post;
						continue;
					}

					$visibility = get_post_meta( $forum_id, 'fmwp_visibility', true );
					if ( 'public' === $visibility ) {
						$filtered_posts[] = $post;
					}
				}
			}
			return $filtered_posts;
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

			if ( 'fmwp_topic' === $post->post_type ) {
				$is_locked = get_post_meta( $post->ID, 'fmwp_locked', true );
				$locked    = ! empty( $is_locked );
			}

			return $locked;
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

			if ( 'fmwp_topic' === $post->post_type ) {
				$is_spam = get_post_meta( $post->ID, 'fmwp_spam', true );
				$spam    = ! empty( $is_spam );
			}

			return $spam;
		}


		/**
		 *
		 */
		public function init_variables() {
			$this->statuses = apply_filters(
				'fmwp_topic_statuses',
				array(
					'publish' => __( 'Open', 'forumwp' ),
					'pending' => __( 'Pending', 'forumwp' ),
					'trash'   => __( 'Trash', 'forumwp' ),
				)
			);

			$this->types = array(
				'normal'       => array(
					'title' => __( 'Normal', 'forumwp' ),
					'order' => 4,
				),
				'pinned'       => array(
					'title' => __( 'Pinned', 'forumwp' ),
					'order' => 2,
				),
				'announcement' => array(
					'title' => __( 'Announcement', 'forumwp' ),
					'order' => 3,
				),
				'global'       => array(
					'title' => __( 'Global', 'forumwp' ),
					'order' => 1,
				),
			);

			$this->status_markers = apply_filters(
				'fmwp_topic_status_markers',
				array(
					'fmwp-topic-locked-marker'       => array(
						'icon'  => 'fas fa-lock',
						'title' => __( 'Locked', 'forumwp' ),
					),
					'fmwp-topic-pinned-marker'       => array(
						'icon'  => 'fas fa-thumbtack',
						'title' => __( 'Pinned', 'forumwp' ),
					),
					'fmwp-topic-announcement-marker' => array(
						'icon'  => 'fas fa-bullhorn',
						'title' => __( 'Announcement', 'forumwp' ),
					),
					'fmwp-topic-global-marker'       => array(
						'icon'  => 'fas fa-globe-americas',
						'title' => __( 'Global', 'forumwp' ),
					),
				)
			);

			$this->sort_by = apply_filters(
				'fmwp_topics_sorting',
				array(
					'date_asc'    => __( 'Oldest to Newest', 'forumwp' ),
					'date_desc'   => __( 'Newest to Oldest', 'forumwp' ),
					'update_desc' => __( 'Recently updated', 'forumwp' ),
					'views_desc'  => __( 'Most views', 'forumwp' ),
				)
			);
		}

		/**
		 *
		 */
		public function views_increment() {
			if ( is_admin() ) {
				return;
			}

			if ( FMWP()->options()->get( 'ajax_increment_views' ) ) {
				return;
			}

			global $post;
			if ( is_int( $post ) ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- operate with current post.
				$post = get_post( $post );
			}
			if ( ! wp_is_post_revision( $post ) && ! is_preview() && is_singular( 'fmwp_topic' ) ) {
				$id = $post->ID;

				$post_views = get_post_meta( $post->ID, 'fmwp_views', true );
				if ( ! $post_views ) {
					$post_views = 0;
				}

				$should_count = true;
				$bots         = array(
					'Google Bot'    => 'google',
					'MSN'           => 'msnbot',
					'Alex'          => 'ia_archiver',
					'Lycos'         => 'lycos',
					'Ask Jeeves'    => 'jeeves',
					'Altavista'     => 'scooter',
					'AllTheWeb'     => 'fast-webcrawler',
					'Inktomi'       => 'slurp@inktomi',
					'Turnitin.com'  => 'turnitinbot',
					'Technorati'    => 'technorati',
					'Yahoo'         => 'yahoo',
					'Findexa'       => 'findexa',
					'NextLinks'     => 'findlinks',
					'Gais'          => 'gaisbo',
					'WiseNut'       => 'zyborg',
					'WhoisSource'   => 'surveybot',
					'Bloglines'     => 'bloglines',
					'BlogSearch'    => 'blogsearch',
					'PubSub'        => 'pubsub',
					'Syndic8'       => 'syndic8',
					'RadioUserland' => 'userland',
					'Gigabot'       => 'gigabot',
					'Become.com'    => 'become.com',
					'Baidu'         => 'baiduspider',
					'so.com'        => '360spider',
					'Sogou'         => 'spider',
					'soso.com'      => 'sosospider',
					'Yandex'        => 'yandex',
				);
				$useragent    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
				foreach ( $bots as $lookfor ) {
					if ( ! empty( $useragent ) && ( false !== stripos( $useragent, $lookfor ) ) ) {
						$should_count = false;
						break;
					}
				}

				// md5 auth_id with user_id or IP address
				$auth_id = $this->get_auth( $post->ID );

				// check if view already exists
				$exists = $this->check_auth_topic_view( $auth_id, $post->ID );

				if ( $should_count && false === $exists ) {
					update_post_meta( $id, 'fmwp_views', $post_views + 1 );

					// add auth and post_id to the DB
					$this->insert_auth_topic_view( $auth_id, $post->ID );
				}
			}
		}

		public function get_auth( $post_id ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				$auth_id = md5( $user_id . $post_id . 'topic_view' );
			} else {
				$user_ip = $this->get_user_ip() ? sanitize_text_field( $this->get_user_ip() ) : 'unknown_ip';
				$auth_id = md5( $user_ip . $post_id . 'topic_view' );
			}

			return $auth_id;
		}

		public function get_user_ip() {
			$data = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
			foreach ( $data as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) as $ip ) {
						$ip = trim( $ip );
						if ( $this->validate_user_ip( $ip ) ) {
							continue;
						}
					}
				}
			}

			return $ip;
		}

		public function validate_user_ip( $ip ) {
			if ( false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return false;
			}

			return true;
		}

		public function check_auth_topic_view( $auth_id, $post_id ) {
			global $wpdb;

			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}fmwp_topic_views WHERE auth_id = %s AND post_id = %d",
					$auth_id,
					$post_id
				)
			);

			return (bool) $exists;
		}

		public function insert_auth_topic_view( $auth_id, $post_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'fmwp_topic_views';

			$wpdb->insert(
				$table_name,
				array(
					'auth_id' => $auth_id,
					'post_id' => $post_id,
				),
				array( '%s', '%d' )
			);
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

			if ( 'auto-draft' === $post->post_status ) {
				return;
			}

			$upgrade_last_update = apply_filters( 'fmwp_topic_upgrade_last_update', true, $post_ID );

			if ( $upgrade_last_update ) {
				update_post_meta( $post_ID, 'fmwp_last_update', time() );
				$forum_id = $this->get_forum_id( $post_ID );
				if ( ! empty( $forum_id ) ) {
					update_post_meta( $forum_id, 'fmwp_last_update', time() );
				}
			}

			if ( $update ) {
				return;
			}

			update_user_meta( $post->post_author, 'fmwp_latest_topic_date', time() );

			update_post_meta( $post_ID, 'fmwp_views', 0 );
		}

		/**
		 * @param int|WP_Post|array $topic
		 *
		 * @return bool|null
		 */
		public function is_pinned( $topic ) {
			if ( is_numeric( $topic ) ) {
				$topic = get_post( $topic );

				if ( empty( $topic ) || is_wp_error( $topic ) ) {
					return null;
				}
			}

			$type = get_post_meta( $topic->ID, 'fmwp_type', true );
			$type = ! empty( $type ) ? $type : 'normal';

			return 'pinned' === $type;
		}

		/**
		 * @param int|WP_Post|array $topic
		 *
		 * @return bool|null
		 */
		public function is_announcement( $topic ) {
			if ( is_numeric( $topic ) ) {
				$topic = get_post( $topic );

				if ( empty( $topic ) || is_wp_error( $topic ) ) {
					return null;
				}
			}

			$type = get_post_meta( $topic->ID, 'fmwp_type', true );
			$type = ! empty( $type ) ? $type : 'normal';

			return 'announcement' === $type;
		}

		/**
		 * @param int|WP_Post|array $topic
		 *
		 * @return bool|null
		 */
		public function is_global( $topic ) {
			if ( is_numeric( $topic ) ) {
				$topic = get_post( $topic );

				if ( empty( $topic ) || is_wp_error( $topic ) ) {
					return null;
				}
			}

			$type = get_post_meta( $topic->ID, 'fmwp_type', true );
			$type = ! empty( $type ) ? $type : 'normal';

			return 'global' === $type;
		}

		public function status_tags() {
			$tags = array();
			if ( is_user_logged_in() ) {
				$tags['trashed'] = __( 'Trashed', 'forumwp' );
				if ( current_user_can( 'manage_fmwp_topics_all' ) ) {
					$tags['spam'] = __( 'Spam', 'forumwp' );
				}
				$tags['reported'] = __( 'Reported', 'forumwp' );
				$tags['pending']  = __( 'Pending', 'forumwp' );
			}

			return apply_filters( 'fmwp_topic_status_tags', $tags );
		}

		/**
		 *
		 * @param WP_Post $topic
		 * @param int|bool $user_id
		 *
		 * @return array
		 */
		public function actions_list( $topic, $user_id = false ) {
			//Topic dropdown actions
			$items = array();

			if ( ! $user_id ) {
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
				} else {
					return $items;
				}
			}

			if ( FMWP()->user()->can_edit_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-edit-topic' => __( 'Edit topic', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_pin_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-pin-topic' => __( 'Pin topic', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_unpin_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-unpin-topic' => __( 'Unpin topic', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_lock_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-lock-topic' => __( 'Lock topic', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_unlock_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-unlock-topic' => __( 'Unlock topic', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_trash_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-trash-topic' => __( 'Move to trash', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_spam_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-mark-spam-topic' => __( 'Mark as spam', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->user()->can_restore_spam_topic( $user_id, $topic ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-restore-spam-topic' => __( 'Isn\'t spam', 'forumwp' ),
					)
				);
			}

			if ( absint( $topic->post_author ) !== $user_id ) {
				if ( ! FMWP()->reports()->is_reported_by_user( $topic->ID, $user_id ) ) {
					$items = array_merge(
						$items,
						array(
							'fmwp-report-topic' => __( 'Report topic', 'forumwp' ),
						)
					);
				} else {
					$items = array_merge(
						$items,
						array(
							'fmwp-unreport-topic' => __( 'Un-report topic', 'forumwp' ),
						)
					);
				}
			}

			if ( FMWP()->reports()->is_reported( $topic->ID ) && FMWP()->user()->can_clear_reports( $user_id ) ) {
				$items = array_merge(
					$items,
					array(
						'fmwp-clear-reports-topic' => __( 'Clear topic\'s reports', 'forumwp' ),
					)
				);
			}

			if ( FMWP()->common()->topic()->is_trashed( $topic->ID ) ) {
				if ( FMWP()->user()->can_restore_topic( $user_id, $topic ) || FMWP()->user()->can_delete_topic( $user_id, $topic ) ) {
					if ( ! empty( $items ) ) {
						$items = array();
					}
				}

				if ( FMWP()->user()->can_restore_topic( $user_id, $topic ) ) {
					$items = array_merge(
						$items,
						array(
							'fmwp-restore-topic' => __( 'Restore topic', 'forumwp' ),
						)
					);
				}

				if ( FMWP()->user()->can_delete_topic( $user_id, $topic ) ) {
					$items = array_merge(
						$items,
						array(
							'fmwp-remove-topic' => __( 'Remove topic', 'forumwp' ),
						)
					);
				}
			}

			$items = apply_filters( 'fmwp_topic_dropdown_actions', $items, $user_id, $topic );
			$items = array_unique( $items );

			foreach ( $items as $key => $title ) {
				$items[ $key ] = array(
					'title'     => $title,
					'entity_id' => $topic->ID,
					'nonce'     => wp_create_nonce( $key . $topic->ID ),
				);
			}

			return $items;
		}

		/**
		 * @param array $data
		 *
		 * @return int
		 */
		public function edit( $data ) {
			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_topic' );

			$args = array(
				'ID'           => $data['topic_id'],
				'post_title'   => $data['title'],
				'post_content' => $post_content,
				'meta_input'   => array(
					'fmwp_original_content' => $orig_content,
				),
			);

			$args = apply_filters( 'fmwp_edit_topic_args', $args );

			$topic_id = wp_update_post( $args );

			if ( ! is_wp_error( $topic_id ) ) {

				if ( FMWP()->options()->get( 'topic_tags' ) ) {
					if ( ! empty( $data['tags'] ) ) {

						$terms = $this->get_tags( $topic_id );

						$terms_ids = array();
						foreach ( $terms as $term ) {
							$terms_ids[] = $term->term_id;
						}

						wp_remove_object_terms( $topic_id, $terms_ids, 'fmwp_topic_tag' );

						if ( ! is_array( $data['tags'] ) ) {
							$list = explode( ',', trim( $data['tags'], ', ' ) );
							$list = array_map( 'trim', $list );

							$data['tags'] = array_filter( $list );
						} else {
							$data['tags'] = array_filter( $data['tags'] );
						}

						$ids = array();
						foreach ( $data['tags'] as $name ) {
							$name = sanitize_text_field( $name );
							$term = get_term_by( 'name', $name, 'fmwp_topic_tag' );
							if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
								$ids[] = $term->term_id;
							} else {
								$term = wp_insert_term( $name, 'fmwp_topic_tag' );
								if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
									$ids[] = $term['term_id'];
								}
							}
						}

						wp_set_post_terms( $topic_id, $ids, 'fmwp_topic_tag' );
					} else {
						$terms     = $this->get_tags( $topic_id );
						$terms_ids = array();
						foreach ( $terms as $term ) {
							$terms_ids[] = $term->term_id;
						}
						wp_remove_object_terms( $topic_id, $terms_ids, 'fmwp_topic_tag' );
					}
				}

				do_action( 'fmwp_topic_edit_completed', $topic_id, $data );
			}

			return $topic_id;
		}

		/**
		 * Create Topic
		 *
		 * @param array $data
		 *
		 * @return int
		 */
		public function create( $data ) {
			$author = ! empty( $data['author_id'] ) ? $data['author_id'] : get_current_user_id();

			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_topic' );

			$args = array(
				'post_type'    => 'fmwp_topic',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_content' => $post_content,
				'post_author'  => $author,
				'meta_input'   => array(
					'fmwp_original_content' => $orig_content,
					'fmwp_forum'            => $data['forum_id'],
					'fmwp_type'             => $data['type'],
					'fmwp_type_order'       => $this->types[ $data['type'] ]['order'],
				),
			);

			$args = apply_filters( 'fmwp_create_topic_args', $args );

			$topic_id = wp_insert_post( $args );

			if ( ! is_wp_error( $topic_id ) ) {

				if ( ! empty( $data['tags'] ) ) {
					if ( ! is_array( $data['tags'] ) ) {
						$list = explode( ',', trim( $data['tags'], ', ' ) );
						$list = array_map( 'trim', $list );

						$data['tags'] = array_filter( $list );
					} else {
						$data['tags'] = array_filter( $data['tags'] );
					}

					$ids = array();
					foreach ( $data['tags'] as $name ) {
						$name = sanitize_text_field( $name );
						$term = get_term_by( 'name', $name, 'fmwp_topic_tag' );
						if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
							$ids[] = $term->term_id;
						} else {
							$term = wp_insert_term( $name, 'fmwp_topic_tag' );
							if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
								$ids[] = $term['term_id'];
							}
						}
					}

					wp_set_post_terms( $topic_id, $ids, 'fmwp_topic_tag' );
				}

				do_action( 'fmwp_topic_create_completed', $topic_id, $data );
			}

			return $topic_id;
		}

		/**
		 * @param int $topic_id
		 *
		 * @return int|bool
		 */
		public function get_forum_id( $topic_id ) {
			$forum_id = get_post_meta( $topic_id, 'fmwp_forum', true );
			if ( empty( $forum_id ) ) {
				return false;
			}
			return absint( $forum_id );
		}

		/**
		 * @param int $topic_id
		 * @param string $key
		 *
		 * @return int|array
		 */
		public function get_statistics( $topic_id, $key = 'all' ) {
			$stats = array();

			switch ( $key ) {
				default:
					$stats = apply_filters( 'fmwp_calculate_topic_stats', 0, $topic_id, $key );
					break;
				case 'replies':
					if ( post_password_required( $topic_id ) ) {
						$stats = 0;
					} else {
						$args = array(
							'post_type'      => 'fmwp_reply',
							'posts_per_page' => -1,
							'post_status'    => FMWP()->common()->reply()->post_status,
							'meta_key'       => 'fmwp_topic',
							'meta_value'     => $topic_id,
							'fields'         => 'ids',
						);

						$args['suppress_filters'] = false;

						$args = apply_filters( 'fmwp_ajax_get_replies_args', $args, $topic_id );

						$replies = get_posts( $args );

						$stats = ( ! empty( $replies ) && ! is_wp_error( $replies ) ) ? count( $replies ) : 0;
					}
					break;
				case 'views':
					if ( post_password_required( $topic_id ) ) {
						$stats = 0;
					} else {
						$views = get_post_meta( $topic_id, 'fmwp_views', true );
						if ( empty( $views ) ) {
							$views = 0;
						}

						$stats = $views;
					}
					break;
				case 'all':
					$keys = apply_filters(
						'fmwp_topic_statistic_keys',
						array(
							'replies',
							'views',
						)
					);
					foreach ( $keys as $attr ) {
						$stats[ $attr ] = $this->get_statistics( $topic_id, $attr );
					}

					break;
			}

			return $stats;
		}

		/**
		 * Spam Topic handler
		 *
		 * @param $topic_id
		 */
		public function spam( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $this->is_spam( $post ) ) {
				return;
			}

			update_post_meta( $post->ID, 'fmwp_spam', true );

			do_action( 'fmwp_after_spam_topic', $topic_id );
		}

		/**
		 * Restore from Trash Topic handler
		 *
		 * @param $topic_id
		 */
		public function restore_spam( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( ! $this->is_spam( $post ) ) {
				return;
			}

			update_post_meta( $post->ID, 'fmwp_spam', false );

			do_action( 'fmwp_after_restore_spam_topic', $topic_id );
		}

		/**
		 * Delete Topic handler
		 *
		 * @param $topic_id
		 */
		public function move_to_trash( $topic_id ) {
			$post = get_post( $topic_id );

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

			do_action( 'fmwp_after_trash_topic', $topic_id );
		}

		/**
		 * Restore from Trash Topic handler
		 *
		 * @param $topic_id
		 */
		public function restore( $topic_id ) {
			$post = get_post( $topic_id );

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

			do_action( 'fmwp_after_restore_topic', $topic_id );
		}

		/**
		 * @param int $user_id
		 * @param array $args
		 *
		 * @return array
		 */
		public function get_topics_by_author( $user_id, $args = array() ) {
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

			$args = array_merge(
				array(
					'post_type'        => 'fmwp_topic',
					'posts_per_page'   => -1,
					'post_status'      => FMWP()->common()->topic()->post_status,
					'author'           => $user_id,
					'order'            => 'desc',
					'meta_query'       => array(
						array(
							'key'     => 'fmwp_forum',
							'value'   => $forum_ids,
							'compare' => 'IN',
						),
					),
					'suppress_filters' => false,
				),
				$args
			);

			$args = apply_filters( 'fmwp_get_topics_arguments', $args );

			$topics = get_posts( $args );

			if ( empty( $topics ) || is_wp_error( $topics ) ) {
				$topics = array();
			}

			return $topics;
		}

		/**
		 * @param WP_Post $topic
		 *
		 * @return array
		 */
		public function get_author_tags( $topic ) {
			$tags = array();

			if ( FMWP()->options()->get( 'reply_user_role' ) ) {
				global $wp_roles;
				$user       = get_userdata( $topic->post_author );
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

		/**
		 * @param $id
		 * @param bool $data
		 *
		 * @return array|WP_Error
		 */
		public function get_tags( $id, $data = false ) {
			$args = array(
				'orderby' => 'name',
				'order'   => 'ASC',
			);
			if ( 'names' === $data ) {
				$args['fields'] = 'names';
			}

			$terms = wp_get_post_terms(
				$id,
				'fmwp_topic_tag',
				$args
			);

			if ( empty( $data ) && count( $terms ) ) {
				foreach ( $terms as $tag ) {
					$tag->permalink = get_term_link( $tag->term_id, 'fmwp_topic_tag' );
				}
			}

			return $terms;
		}

		/**
		 * @param WP_Post $topic
		 * @return bool
		 */
		public function is_reply_button_hidden( $topic ) {
			$hidden = false;

			$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

			if ( 'publish' === $topic->post_status ) {

				if ( is_user_logged_in() ) {
					if ( FMWP()->user()->can_reply( $topic->ID ) ) {
						?>
						<input type="button" class="fmwp-write-reply" title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" />
						<?php
					} else {
						echo wp_kses( apply_filters( 'fmwp_reply_disabled_reply_text', '', $topic->ID ), FMWP()->get_allowed_html( 'templates' ) );
					}
				} else {
					?>
					<input type="button" class="<?php echo esc_attr( $unlogged_class ); ?>" title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ); ?>" />
					<?php
				}
			} else {
				esc_html_e( 'This topic is closed to new replies', 'forumwp' );
			}

			return $hidden;
		}

		/**
		 * @param WP_Post $topic
		 */
		public function pin( $topic ) {
			update_post_meta( $topic->ID, 'fmwp_type', 'pinned' );
			update_post_meta( $topic->ID, 'fmwp_type_order', $this->types['pinned']['order'] );
		}

		/**
		 * @param WP_Post $topic
		 */
		public function unpin( $topic ) {
			update_post_meta( $topic->ID, 'fmwp_type', 'normal' );
			update_post_meta( $topic->ID, 'fmwp_type_order', $this->types['normal']['order'] );
		}

		/**
		 * @param int $topic_id
		 */
		public function lock( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $topic_id, 'fmwp_locked', true );

			do_action( 'fmwp_after_lock_topic', $topic_id );
		}

		/**
		 * @param int $topic_id
		 */
		public function unlock( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( ! $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $topic_id, 'fmwp_locked', false );

			do_action( 'fmwp_after_unlock_topic', $topic_id );
		}

		/**
		 * Delete Topic handler
		 *
		 * @param $topic_id
		 */
		public function delete( $topic_id ) {
			$topic = get_post( $topic_id );

			do_action( 'fmwp_before_delete_topic', $topic_id, $topic );

			if ( ! empty( $topic ) && ! is_wp_error( $topic ) ) {
				wp_delete_post( $topic_id );
				do_action( 'fmwp_after_delete_topic', $topic_id );
			}
		}

		public function trash( $new_status, $old_status, $post ) {
			if ( 'fmwp_topic' === $post->post_type ) {
				if ( 'trash' === $new_status && 'trash' !== $old_status ) {
					update_post_meta( $post->ID, 'fmwp_user_trash_id', get_current_user_id() );
				} elseif ( 'trash' !== $new_status && 'trash' === $old_status ) {
					delete_post_meta( $post->ID, 'fmwp_user_trash_id' );
				}
			}
		}
	}
}
