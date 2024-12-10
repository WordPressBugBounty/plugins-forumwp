<?php
namespace fmwp\admin;

use WP_Post;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\admin\Columns' ) ) {

	/**
	 * Class Columns
	 *
	 * @package fmwp\admin
	 */
	class Columns {

		/**
		 * Columns constructor.
		 */
		public function __construct() {
			add_filter( 'display_post_states', array( &$this, 'add_display_post_states' ), 10, 2 );

			add_filter( 'manage_edit-fmwp_forum_columns', array( &$this, 'forum_columns' ) );
			add_action( 'manage_fmwp_forum_posts_custom_column', array( &$this, 'forum_columns_content' ), 10, 3 );

			add_filter( 'manage_edit-fmwp_forum_category_columns', array( &$this, 'forum_category_columns' ) );
			add_action( 'manage_fmwp_forum_category_custom_column', array( &$this, 'forum_category_columns_content' ), 10, 3 );

			add_filter( 'manage_edit-fmwp_topic_tag_columns', array( &$this, 'topic_tag_columns' ) );
			add_action( 'manage_fmwp_topic_tag_custom_column', array( &$this, 'topic_tag_columns_content' ), 10, 3 );

			add_filter( 'manage_edit-fmwp_topic_columns', array( &$this, 'topic_columns' ) );
			add_action( 'manage_fmwp_topic_posts_custom_column', array( &$this, 'topic_columns_content' ), 10, 3 );

			add_action( 'pre_get_posts', array( &$this, 'remove_post_status_all_edit_flow' ) );

			add_action( 'pre_get_posts', array( &$this, 'reported_posts' ) );

			add_filter( 'views_edit-fmwp_reply', array( &$this, 'add_reported_reply_folder' ) );
			add_filter( 'views_edit-fmwp_topic', array( &$this, 'add_reported_topic_folder' ) );
			add_filter( 'post_row_actions', array( &$this, 'row_actions' ), 10, 2 );

			add_filter( 'views_edit-fmwp_forum', array( $this, 'add_locked_forum_folder' ) );
			add_filter( 'views_edit-fmwp_topic', array( $this, 'add_locked_topic_folder' ) );
			add_action( 'pre_get_posts', array( &$this, 'locked_posts' ) );

			add_filter( 'views_edit-fmwp_reply', array( $this, 'add_spam_reply_folder' ) );
			add_filter( 'views_edit-fmwp_topic', array( $this, 'add_spam_topic_folder' ) );
			add_action( 'pre_get_posts', array( &$this, 'spam_posts' ) );
		}

		public function add_reported_topic_folder( $views ) {
			$reported_topics = FMWP()->reports()->get_all_reports_count( 'fmwp_topic' );
			$current         = isset( $_GET['post_status'] ) && 'fmwp_reported' === $_GET['post_status']; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! empty( $reported_topics ) ) {
				$views['fmwp_reported'] = '<a href="edit.php?post_type=fmwp_topic&post_status=fmwp_reported" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Reported', 'forumwp' ) . ' <span class="count">(' . $reported_topics . ')</span></a>';
			}
			return $views;
		}

		public function add_reported_reply_folder( $views ) {
			$reported_replies = FMWP()->reports()->get_all_reports_count( 'fmwp_reply' );
			$current          = isset( $_GET['post_status'] ) && 'fmwp_reported' === $_GET['post_status']; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! empty( $reported_replies ) ) {
				$views['fmwp_reported'] = '<a href="edit.php?post_type=fmwp_reply&post_status=fmwp_reported" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Reported', 'forumwp' ) . ' <span class="count">(' . $reported_replies . ')</span></a>';
			}
			return $views;
		}

		/**
		 * @param array $views
		 *
		 * @return array
		 */
		public function add_locked_forum_folder( $views ) {
			$locked_forums = get_posts(
				array(
					'post_type'      => 'fmwp_forum',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'fmwp_locked',
							'value'   => true,
							'compare' => '=',
						),
					),
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $locked_forums ) && ! is_wp_error( $locked_forums ) ) {
				$locked_forums = count( $locked_forums );
			} else {
				return $views;
			}

			$current              = isset( $_GET['post_status'] ) && 'fmwp_locked' === $_GET['post_status']; // phpcs:ignore WordPress.Security.NonceVerification
			$views['fmwp_locked'] = '<a href="edit.php?post_type=fmwp_forum&post_status=fmwp_locked" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Locked', 'forumwp' ) . ' <span class="count">(' . $locked_forums . ')</span></a>';

			return $views;
		}

		/**
		 * @param array $views
		 *
		 * @return array
		 */
		public function add_locked_topic_folder( $views ) {
			$locked_topics = get_posts(
				array(
					'post_type'      => 'fmwp_topic',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'fmwp_locked',
							'value'   => true,
							'compare' => '=',
						),
					),
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $locked_topics ) && ! is_wp_error( $locked_topics ) ) {
				$locked_topics = count( $locked_topics );
			} else {
				return $views;
			}

			$current              = isset( $_GET['post_status'] ) && 'fmwp_locked' === $_GET['post_status']; // phpcs:ignore WordPress.Security.NonceVerification
			$views['fmwp_locked'] = '<a href="edit.php?post_type=fmwp_topic&post_status=fmwp_locked" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Locked', 'forumwp' ) . ' <span class="count">(' . $locked_topics . ')</span></a>';

			return $views;
		}

		/**
		 * @param array $views
		 *
		 * @return array
		 */
		public function add_spam_reply_folder( $views ) {
			$spam_replies = get_posts(
				array(
					'post_type'      => 'fmwp_reply',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'fmwp_spam',
							'value'   => true,
							'compare' => '=',
						),
					),
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $spam_replies ) && ! is_wp_error( $spam_replies ) ) {
				$spam_replies = count( $spam_replies );
			} else {
				return $views;
			}

			$current            = isset( $_GET['post_status'] ) && 'fmwp_spam' === $_GET['post_status']; // phpcs:ignore WordPress.Security.NonceVerification
			$views['fmwp_spam'] = '<a href="edit.php?post_type=fmwp_reply&post_status=fmwp_spam" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Spam', 'forumwp' ) . ' <span class="count">(' . $spam_replies . ')</span></a>';

			return $views;
		}

		/**
		 * @param array $views
		 *
		 * @return array
		 */
		public function add_spam_topic_folder( $views ) {
			$spam_topics = get_posts(
				array(
					'post_type'      => 'fmwp_topic',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'fmwp_spam',
							'value'   => true,
							'compare' => '=',
						),
					),
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $spam_topics ) && ! is_wp_error( $spam_topics ) ) {
				$spam_topics = count( $spam_topics );
			} else {
				return $views;
			}

			$current            = isset( $_GET['post_status'] ) && 'fmwp_spam' === $_GET['post_status']; // phpcs:ignore WordPress.Security.NonceVerification
			$views['fmwp_spam'] = '<a href="edit.php?post_type=fmwp_topic&post_status=fmwp_spam" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Spam', 'forumwp' ) . ' <span class="count">(' . $spam_topics . ')</span></a>';

			return $views;
		}

		/**
		 * @param WP_Query $query
		 *
		 * @return void
		 */
		public function locked_posts( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( 'edit.php' !== $pagenow ) {
				return;
			}

			if ( 'fmwp_forum' !== $query->query['post_type'] && 'fmwp_topic' !== $query->query['post_type'] ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['post_status'] ) && 'fmwp_locked' === sanitize_key( $_GET['post_status'] ) ) {
				if ( ! isset( $query->query_vars['meta_query'] ) ) {
					$query->query_vars['meta_query']             = array();
					$query->query_vars['meta_query']['relation'] = 'AND';
				}

				if ( ! isset( $query->query['meta_query'] ) ) {
					$query->query['meta_query']             = array();
					$query->query['meta_query']['relation'] = 'AND';
				}

				$query->query_vars['meta_query'][] = array(
					'key'     => 'fmwp_locked',
					'value'   => true,
					'compare' => '=',
				);

				$query->query['meta_query'][] = array(
					'key'     => 'fmwp_locked',
					'value'   => true,
					'compare' => '=',
				);
			}
		}

		/**
		 * @param $query
		 */
		public function spam_posts( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( 'edit.php' !== $pagenow ) {
				return;
			}

			if ( 'fmwp_reply' !== $query->query['post_type'] && 'fmwp_topic' !== $query->query['post_type'] ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['post_status'] ) && 'fmwp_spam' === $_GET['post_status'] ) {
				if ( ! isset( $query->query_vars['meta_query'] ) ) {
					$query->query_vars['meta_query']             = array();
					$query->query_vars['meta_query']['relation'] = 'AND';
				}

				if ( ! isset( $query->query['meta_query'] ) ) {
					$query->query['meta_query']             = array();
					$query->query['meta_query']['relation'] = 'AND';
				}

				$query->query_vars['meta_query'][] = array(
					'key'     => 'fmwp_spam',
					'value'   => true,
					'compare' => '=',
				);

				$query->query['meta_query'][] = array(
					'key'     => 'fmwp_spam',
					'value'   => true,
					'compare' => '=',
				);
			}
		}


		/**
		 * @param array $actions
		 * @param $post
		 *
		 * @return array
		 */
		public function row_actions( $actions, $post ) {
			if ( 'fmwp_reply' === $post->post_type || 'fmwp_topic' === $post->post_type ) {
				if ( FMWP()->reports()->is_reported( $post->ID ) ) {
					$url                           = add_query_arg(
						array(
							'fmwp_adm_action' => 'clear_reports',
							'post_id'         => $post->ID,
							'_wpnonce'        => wp_create_nonce( 'fmwp_clear_reports' . $post->ID ),
						)
					);
					$confirm                       = 'return confirm("' . __( 'Are you sure?', 'forumwp' ) . '") ? true : false;';
					$actions['fmwp_clear_reports'] = '<a href="' . esc_url( $url ) . '" onclick="' . esc_attr( $confirm ) . '">' . __( 'Clear Reports', 'forumwp' ) . '</a>';
				}
			}

			return $actions;
		}

		/**
		 * @param WP_Query $query
		 *
		 * @return void
		 */
		public function remove_post_status_all_edit_flow( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( 'edit.php' !== $pagenow ) {
				return;
			}

			if ( 'fmwp_reply' !== $query->query['post_type'] && 'fmwp_topic' !== $query->query['post_type'] ) {
				return;
			}

			if ( 'all' === $query->query['post_status'] || '' === $query->query['post_status'] ) {
				$query->query_vars['post_status'] = 'any';
				$query->query['post_status']      = 'any';
			}
		}

		/**
		 * @param WP_Query $query
		 *
		 * @return void
		 */
		public function reported_posts( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( 'edit.php' !== $pagenow ) {
				return;
			}

			if ( 'fmwp_reply' !== $query->query['post_type'] && 'fmwp_topic' !== $query->query['post_type'] ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['post_status'] ) && 'fmwp_reported' === $_GET['post_status'] ) {
				$query->query_vars['post_status'] = array( 'any', 'trash' );
				$query->query['post_status']      = array( 'any', 'trash' );

				$post_ids = FMWP()->reports()->get_post_id_reports( $query->query['post_type'] );

				$query->query_vars['post__in'] = $post_ids;
				$query->query['post__in']      = $post_ids;
			}
		}

		/**
		 * Add a post display state for special ForumWP pages in the page list table.
		 *
		 * @param array   $post_states An array of post display states.
		 * @param WP_Post $post        The current post object.
		 *
		 * @return array
		 */
		public function add_display_post_states( $post_states, $post ) {
			// phpcs:disable WordPress.Security.NonceVerification -- just for displaying
			if ( 'page' === $post->post_type ) {
				foreach ( FMWP()->config()->get( 'core_pages' ) as $page_key => $page_value ) {
					if ( absint( FMWP()->options()->get( $page_key . '_page' ) ) === $post->ID ) {
						$post_states[ 'fmwp_page_' . $page_key ] = sprintf( 'ForumWP %s', $page_value['title'] );
					}
				}
			} elseif ( 'fmwp_forum' === $post->post_type ) {
				if ( ( ! isset( $_GET['post_status'] ) || 'fmwp_locked' !== $_GET['post_status'] ) && FMWP()->common()->forum()->is_locked( $post ) ) {
					$post_states['fmwp_locked'] = __( 'Locked', 'forumwp' );
				}
			} elseif ( 'fmwp_topic' === $post->post_type ) {
				if ( ( ! isset( $_GET['post_status'] ) || 'fmwp_locked' !== $_GET['post_status'] ) && FMWP()->common()->topic()->is_locked( $post ) ) {
					$post_states['fmwp_locked'] = __( 'Locked', 'forumwp' );
				}

				if ( ( ! isset( $_GET['post_status'] ) || 'fmwp_spam' !== $_GET['post_status'] ) && FMWP()->common()->topic()->is_spam( $post ) ) {
					$post_states['fmwp_status'] = __( 'Spam', 'forumwp' );
				}

				if ( ! isset( $_GET['post_status'] ) || 'fmwp_reported' !== $_GET['post_status'] ) {
					if ( FMWP()->reports()->is_reported( $post->ID ) ) {
						$post_states['fmwp_reported'] = __( 'Reported', 'forumwp' );
					}
				}
			} elseif ( 'fmwp_reply' === $post->post_type ) {
				if ( ( ! isset( $_GET['post_status'] ) || 'fmwp_spam' !== $_GET['post_status'] ) && FMWP()->common()->reply()->is_spam( $post ) ) {
					$post_states['fmwp_status'] = __( 'Spam', 'forumwp' );
				}

				if ( ! isset( $_GET['post_status'] ) || 'fmwp_reported' !== $_GET['post_status'] ) {
					if ( FMWP()->reports()->is_reported( $post->ID ) ) {
						$post_states['fmwp_reported'] = __( 'Reported', 'forumwp' );
					}
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification -- just for displaying
			return $post_states;
		}

		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		public function forum_columns( $columns ) {
			$additional_columns = array(
				'topics'     => __( 'Topics', 'forumwp' ),
				'replies'    => __( 'Replies', 'forumwp' ),
				'category'   => __( 'Category', 'forumwp' ),
				'status'     => __( 'Status', 'forumwp' ),
				'visibility' => __( 'Visibility', 'forumwp' ),
			);

			if ( ! FMWP()->options()->get( 'forum_categories' ) ) {
				unset( $additional_columns['category'] );
			}

			return FMWP()->array_insert_before( $columns, 'author', $additional_columns );
		}

		/**
		 * Display custom columns for Forum
		 *
		 * @param string $column_name
		 * @param int $id
		 */
		public function forum_columns_content( $column_name, $id ) {
			switch ( $column_name ) {
				case 'topics':
					echo wp_kses( FMWP()->common()->forum()->get_statistics( $id, 'topics' ), FMWP()->get_allowed_html( 'templates' ) );
					break;
				case 'replies':
					echo wp_kses( FMWP()->common()->forum()->get_statistics( $id, 'posts' ), FMWP()->get_allowed_html( 'templates' ) );
					break;
				case 'category':
					$terms = wp_get_post_terms(
						$id,
						'fmwp_forum_category',
						array(
							'orderby' => 'name',
							'order'   => 'ASC',
							'fields'  => 'names',
						)
					);

					if ( ! empty( $terms ) ) {
						echo wp_kses_post( implode( ',', $terms ) );
					}
					break;
				case 'status':
					$post = get_post( $id );

					$status = __( 'Unknown', 'forumwp' );
					if ( FMWP()->common()->forum()->is_locked( $post ) ) {
						$status = __( 'Locked', 'forumwp' );
					} else {
						$status_obj = get_post_status_object( $post->post_status );
						if ( $status_obj ) {
							$status = ! empty( $status_obj->label ) ? $status_obj->label : $post->post_status;
						}
					}

					echo esc_html( $status );
					break;
				case 'visibility':
					$visibility = get_post_meta( $id, 'fmwp_visibility', true );
					$visibility = ! empty( FMWP()->common()->forum()->visibilities[ $visibility ] ) ? FMWP()->common()->forum()->visibilities[ $visibility ] : $visibility;
					echo esc_html( $visibility );
					break;
			}
		}

		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		public function forum_category_columns( $columns ) {
			$additional_columns = array(
				'ID' => __( 'Category ID', 'forumwp' ),
			);

			return FMWP()->array_insert_after( $columns, 'slug', $additional_columns );
		}

		/**
		 * Display custom columns for Forum Category
		 *
		 * @param string $content
		 * @param string $column_name
		 * @param int    $term_id
		 *
		 * @return string
		 */
		public function forum_category_columns_content( $content, $column_name, $term_id ) {
			if ( 'ID' === $column_name ) {
				$content = (string) $term_id;
			}

			return $content;
		}

		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		public function topic_tag_columns( $columns ) {
			$additional_columns = array(
				'ID' => __( 'Tag ID', 'forumwp' ),
			);

			return FMWP()->array_insert_after( $columns, 'slug', $additional_columns );
		}

		/**
		 * Display custom columns for Forum Category
		 *
		 * @param string $content
		 * @param string $column_name
		 * @param int    $term_id
		 *
		 * @return string
		 */
		public function topic_tag_columns_content( $content, $column_name, $term_id ) {
			if ( 'ID' === $column_name ) {
				$content = (string) $term_id;
			}

			return $content;
		}

		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		public function topic_columns( $columns ) {
			$additional_columns = array(
				'forum'      => __( 'Forum', 'forumwp' ),
				'type'       => __( 'Type', 'forumwp' ),
				'topic_tags' => __( 'Tags', 'forumwp' ),
				'status'     => __( 'Status', 'forumwp' ),
			);

			if ( ! FMWP()->options()->get( 'topic_tags' ) ) {
				unset( $additional_columns['topic_tags'] );
			}

			return FMWP()->array_insert_before( $columns, 'author', $additional_columns );
		}

		/**
		 * Display custom columns for Forum
		 *
		 * @param string $column_name
		 * @param int $id
		 */
		public function topic_columns_content( $column_name, $id ) {
			switch ( $column_name ) {
				case 'forum':
					$forum_id = get_post_meta( $id, 'fmwp_forum', true );
					$forum    = get_post( $forum_id );

					if ( ! empty( $forum ) && ! is_wp_error( $forum ) ) {
						echo wp_kses_post( $forum->post_title );
					}
					break;
				case 'type':
					$type = get_post_meta( $id, 'fmwp_type', true );
					$type = ! empty( FMWP()->common()->topic()->types[ $type ]['title'] ) ? FMWP()->common()->topic()->types[ $type ]['title'] : $type;
					echo esc_html( $type );
					break;
				case 'status':
					$post   = get_post( $id );
					$status = ! empty( FMWP()->common()->topic()->statuses[ $post->post_status ] ) ? FMWP()->common()->topic()->statuses[ $post->post_status ] : $post->post_status;
					echo esc_html( $status );
					break;
				case 'topic_tags':
					$terms = FMWP()->common()->topic()->get_tags( $id, 'names' );

					if ( ! empty( $terms ) ) {
						echo wp_kses_post( implode( ',', $terms ) );
					}
					break;
			}
		}
	}
}
