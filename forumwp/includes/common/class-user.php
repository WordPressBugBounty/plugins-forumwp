<?php
namespace fmwp\common;

use WP_Post;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\User' ) ) {

	/**
	 * Class User
	 *
	 * @package fmwp\common
	 */
	class User {

		/**
		 * @param WP_User $user
		 *
		 * @return string
		 */
		public function get_unique_permalink( $user ) {
			global $wpdb;

			if ( empty( $user ) ) {
				return '';
			}

			$meta = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_value
				FROM {$wpdb->usermeta}
				WHERE meta_key = 'fmwp_permalink' AND
				user_id != %d",
					$user->ID
				)
			);

			$i         = 1;
			$permalink = urldecode( sanitize_title( $user->display_name ) );
			if ( ! empty( $meta ) ) {
				while ( in_array( $permalink, $meta, true ) ) {
					$permalink = urldecode( sanitize_title( $user->display_name . ' ' . $i ) );
					++$i;
				}
			}

			return $permalink;
		}

		/**
		 * @param string $permalink
		 *
		 * @return bool|string
		 */
		public function get_user_by_permalink( $permalink ) {
			global $wpdb;

			$user_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT user_id
				FROM {$wpdb->usermeta}
				WHERE meta_key='fmwp_permalink' AND
				      meta_value = %s",
					urldecode( $permalink )
				)
			);

			if ( empty( $user_id ) ) {
				$user_id = false;
			}

			return $user_id;
		}

		/**
		 * Get usermeta 'fmwp_permalink',
		 * generate if empty and return this value
		 *
		 * @param int $user_id
		 *
		 * @return string
		 */
		public function maybe_get_slug( $user_id ) {
			$slug = get_user_meta( $user_id, 'fmwp_permalink', true );
			if ( empty( $slug ) ) {
				$user = get_userdata( $user_id );
				$slug = $this->get_unique_permalink( $user );
				update_user_meta( $user_id, 'fmwp_permalink', $slug );
			}

			return $slug;
		}

		/**
		 * @param int $user_id
		 * @param bool|string $tab
		 * @param bool|string $subtab
		 *
		 * @return string
		 */
		public function get_profile_link( $user_id, $tab = false, $subtab = false ) {
			$profile_page_link = FMWP()->common()->get_preset_page_link( 'profile' );

			$slug = $this->maybe_get_slug( $user_id );

			if ( FMWP()->is_permalinks ) {
				$link = user_trailingslashit( $profile_page_link ) . $slug;
				if ( ! empty( $tab ) ) {
					$link .= '/' . $tab;

					if ( ! empty( $subtab ) ) {
						$link .= '/' . $subtab;
					}
				}
			} else {
				$args = array(
					'fmwp_user' => $slug,
				);

				if ( ! empty( $tab ) ) {
					$args['fmwp_profiletab'] = $tab;

					if ( ! empty( $subtab ) ) {
						$args['fmwp_profilesubtab'] = $subtab;
					}
				}

				$link = add_query_arg( $args, $profile_page_link );
			}

			return apply_filters( 'fmwp_user_profile_link', $link, $user_id );
		}

		/**
		 * @param int|bool $user_id
		 *
		 * @return bool
		 */
		public function can_create_forum( $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( ! user_can( $user_id, 'manage_fmwp_forums' ) && ! user_can( $user_id, 'fmwp_post_forum' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * @param int $forum_id
		 * @param int|bool $user_id
		 *
		 * @return bool
		 */
		public function can_create_topic( $forum_id, $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( post_password_required( $forum_id ) ) {
				return false;
			}

			if ( FMWP()->common()->forum()->is_locked( $forum_id ) ) {
				return false;
			}

			if ( ! user_can( $user_id, 'manage_fmwp_topics' ) && ! user_can( $user_id, 'fmwp_post_topic' ) ) {
				return false;
			}

			return apply_filters( 'fmwp_user_can_create_topic', true, $user_id, $forum_id );
		}

		/**
		 * @param int $topic_id
		 * @param int|bool $user_id
		 *
		 * @return bool
		 */
		public function can_reply( $topic_id, $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			if ( FMWP()->common()->topic()->is_locked( $topic_id ) ) {
				return false;
			}

			if ( ! user_can( $user_id, 'manage_fmwp_replies' ) && ! user_can( $user_id, 'fmwp_post_reply' ) ) {
				return false;
			}

			return apply_filters( 'fmwp_user_can_create_reply', true, $user_id, $topic_id );
		}

		/**
		 * @param int $user_id
		 * @param string $displaying
		 * @param int $size
		 * @param array $attrs
		 *
		 * @return bool|mixed
		 */
		public function get_avatar( $user_id, $displaying = 'inline', $size = 50, $attrs = array() ) {
			$classes = ! empty( $attrs['class'] ) ? $attrs['class'] : '';
			$title   = ! empty( $attrs['title'] ) ? $attrs['title'] : '';
			return get_avatar(
				$user_id,
				$size,
				'',
				'',
				array(
					'class'      => "fmwp-user-avatar fmwp-$displaying $classes",
					'extra_attr' => 'title="' . $title . '"',
				)
			);
		}

		/**
		 * @param WP_User $user
		 *
		 * @return string
		 */
		public function display_name( $user ) {
			if ( empty( $user ) ) {
				return '';
			}

			$display_name = ! empty( $user->display_name ) ? $user->display_name : $user->user_login;
			return apply_filters( 'fmwp_user_display_name', $display_name, $user );
		}

		/**
		 * Check visibility of reply for selected reply
		 *
		 * @param int $user_id
		 * @param int $reply_id
		 *
		 * @return bool
		 */
		public function can_view_reply( $user_id, $reply_id ) {
			$can_view = true;

			$reply = get_post( $reply_id );

			$topic_id = FMWP()->common()->reply()->get_topic_id( $reply_id );
			$topic    = get_post( $topic_id );

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic_id );
			$forum    = get_post( $forum_id );

			if ( 'publish' !== $forum->post_status ) {
				$can_view = false;
				if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
					if ( 'pending' === $forum->post_status || 'private' === $forum->post_status ) {
						$can_view = true;
					}
				} elseif ( 'pending' === $forum->post_status && absint( $forum->post_author ) === $user_id ) {
					$can_view = true;
				}
			}

			if ( $can_view ) {
				if ( 'publish' !== $topic->post_status ) {
					$can_view = false;
					if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
						if ( 'pending' === $topic->post_status || 'private' === $topic->post_status ) {
							$can_view = true;
						}
					} elseif ( 'pending' === $topic->post_status && absint( $topic->post_author ) === $user_id ) {
						$can_view = true;
					}
				} elseif ( FMWP()->common()->topic()->is_spam( $topic ) ) {
						$can_view = false;
					if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
						$can_view = true;
					}
				}
			}

			if ( $can_view ) {
				if ( 'publish' !== $reply->post_status ) {
					$can_view = false;
					if ( user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
						if ( 'pending' === $reply->post_status || 'trash' === $reply->post_status ) {
							$can_view = true;
						}
					} elseif ( 'pending' === $reply->post_status && absint( $reply->post_author ) === $user_id ) {
						$can_view = true;
					} elseif ( 'trash' === $reply->post_status && absint( $reply->post_author ) === $user_id ) {
						$can_view = true;
					}
				} elseif ( FMWP()->common()->reply()->is_spam( $reply ) ) {
						$can_view = false;
					if ( user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
						$can_view = true;
					}
				}
			}

			return apply_filters( 'fmwp_user_can_view_reply', $can_view, $user_id, $reply_id );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function can_edit_reply( $user_id, $reply ) {
			$can_edit = false;

			if ( 'trash' !== $reply->post_status ) {

				if ( user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
					$can_edit = true;
				} elseif ( absint( $reply->post_author ) === $user_id && user_can( $user_id, 'fmwp_edit_own_reply' ) ) {
					$can_edit = true;
				}

				$topic_id = FMWP()->common()->reply()->get_topic_id( $reply->ID );
				$topic    = get_post( $topic_id );

				if ( empty( $topic ) ) {
					$can_edit = false;
				} else {
					if ( ! user_can( $user_id, 'manage_fmwp_topics_all' ) && FMWP()->common()->topic()->is_locked( $topic ) ) {
						$can_edit = false;
					}

					$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
					$forum    = get_post( $forum_id );

					if ( empty( $forum ) || ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) ) {
						$can_edit = false;
					}
				}
			}

			return apply_filters( 'fmwp_user_can_edit_reply', $can_edit, $user_id, $reply );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function can_trash_reply( $user_id, $reply ) {
			$can_trash = false;

			if ( 'trash' !== $reply->post_status ) {
				if ( absint( $reply->post_author ) === $user_id || user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
					$can_trash = true;
				}

				$topic_id = FMWP()->common()->reply()->get_topic_id( $reply->ID );
				$topic    = get_post( $topic_id );

				if ( empty( $topic ) ) {
					$can_trash = false;
				} else {
					if ( ! user_can( $user_id, 'manage_fmwp_topics_all' ) && FMWP()->common()->topic()->is_locked( $topic ) ) {
						$can_trash = false;
					}

					$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
					$forum    = get_post( $forum_id );

					if ( empty( $forum ) || ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) ) {
						$can_trash = false;
					}
				}
			}

			return apply_filters( 'fmwp_user_can_trash_reply', $can_trash, $user_id, $reply );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function can_restore_reply( $user_id, $reply ) {
			$can_restore = false;

			if ( 'trash' === $reply->post_status ) {
				if ( user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
					$can_restore = true;
				} else {
					$trash_author = get_post_meta( $reply->ID, 'fmwp_user_trash_id', true );
					if ( ! empty( $trash_author ) && absint( $trash_author ) === $user_id ) {
						$can_restore = true;
					}
				}

				$topic_id = FMWP()->common()->reply()->get_topic_id( $reply->ID );
				$topic    = get_post( $topic_id );

				if ( ! user_can( $user_id, 'manage_fmwp_topics_all' ) && FMWP()->common()->topic()->is_locked( $topic ) ) {
					$can_restore = false;
				}

				$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
				$forum    = get_post( $forum_id );

				if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
					$can_restore = false;
				}
			}

			return apply_filters( 'fmwp_user_can_restore_reply', $can_restore, $user_id, $reply );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function can_delete_reply( $user_id, $reply ) {
			$can_delete = false;

			if ( 'trash' === $reply->post_status ) {
				if ( absint( $reply->post_author ) === $user_id || user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
					$can_delete = true;
				}

				$topic_id = FMWP()->common()->reply()->get_topic_id( $reply->ID );
				$topic    = get_post( $topic_id );

				if ( ! user_can( $user_id, 'manage_fmwp_topics_all' ) && FMWP()->common()->topic()->is_locked( $topic ) ) {
					$can_delete = false;
				}

				$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
				$forum    = get_post( $forum_id );

				if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
					$can_delete = false;
				}
			}

			return apply_filters( 'fmwp_user_can_delete_reply', $can_delete, $user_id, $reply );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function can_spam_reply( $user_id, $reply ) {
			$can_spam = false;

			if ( 'trash' !== $reply->post_status ) {
				if ( user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
					if ( absint( $reply->post_author ) !== $user_id && ! FMWP()->common()->reply()->is_spam( $reply ) ) {
						$can_spam = true;
					}
				}

				$topic_id = FMWP()->common()->reply()->get_topic_id( $reply->ID );
				$topic    = get_post( $topic_id );

				if ( empty( $topic ) ) {
					$can_spam = false;
				} else {
					if ( ! user_can( $user_id, 'manage_fmwp_topics_all' ) && FMWP()->common()->topic()->is_locked( $topic ) ) {
						$can_spam = false;
					}

					$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
					$forum    = get_post( $forum_id );

					if ( empty( $forum ) || ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) ) {
						$can_spam = false;
					}
				}
			}

			return apply_filters( 'fmwp_user_can_spam_reply', $can_spam, $user_id, $reply );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $reply
		 *
		 * @return bool
		 */
		public function can_restore_spam_reply( $user_id, $reply ) {
			$can_restore_spam = false;

			if ( user_can( $user_id, 'manage_fmwp_replies_all' ) ) {
				if ( absint( $reply->post_author ) !== $user_id && FMWP()->common()->reply()->is_spam( $reply ) ) {
					$can_restore_spam = true;
				}
			}

			$topic_id = FMWP()->common()->reply()->get_topic_id( $reply->ID );
			$topic    = get_post( $topic_id );

			if ( empty( $topic ) ) {
				$can_restore_spam = false;
			} else {
				if ( ! user_can( $user_id, 'manage_fmwp_topics_all' ) && FMWP()->common()->topic()->is_locked( $topic ) ) {
					$can_restore_spam = false;
				}

				$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
				$forum    = get_post( $forum_id );

				if ( empty( $forum ) || ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) ) {
					$can_restore_spam = false;
				}
			}

			return apply_filters( 'fmwp_user_can_restore_spam_reply', $can_restore_spam, $user_id, $reply );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_spam_topic( $user_id, $topic ) {
			$can_spam = false;
			if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
				if ( absint( $topic->post_author ) !== $user_id && ! FMWP()->common()->topic()->is_spam( $topic ) ) {
					$can_spam = true;
				}
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum    = get_post( $forum_id );

			if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
				$can_spam = false;
			}

			return apply_filters( 'fmwp_user_can_spam_topic', $can_spam, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_restore_spam_topic( $user_id, $topic ) {
			$can_restore_spam = false;
			if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
				if ( absint( $topic->post_author ) !== $user_id && FMWP()->common()->topic()->is_spam( $topic ) ) {
					$can_restore_spam = true;
				}
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum    = get_post( $forum_id );

			if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
				$can_restore_spam = false;
			}

			return apply_filters( 'fmwp_user_can_restore_spam_topic', $can_restore_spam, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_edit_topic( $user_id, $topic ) {
			$can_edit = false;

			if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
				$can_edit = true;
			} elseif ( absint( $topic->post_author ) === $user_id && user_can( $user_id, 'fmwp_edit_own_topic' ) ) {
				if ( ! FMWP()->common()->topic()->is_locked( $topic ) && ! FMWP()->common()->topic()->is_spam( $topic ) ) {
					$can_edit = true;
				}
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum    = get_post( $forum_id );

			if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
				$can_edit = false;
			}

			return apply_filters( 'fmwp_user_can_edit_topic', $can_edit, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_pin_topic( $user_id, $topic ) {
			$can_pin = false;

			if ( user_can( $user_id, 'manage_fmwp_topics_all' ) && ! FMWP()->common()->topic()->is_pinned( $topic ) ) {
				$can_pin = true;
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum    = get_post( $forum_id );

			if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
				$can_pin = false;
			}

			return apply_filters( 'fmwp_user_can_pin_topic', $can_pin, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_unpin_topic( $user_id, $topic ) {
			$can_unpin = false;

			if ( user_can( $user_id, 'manage_fmwp_topics_all' ) && FMWP()->common()->topic()->is_pinned( $topic ) ) {
				$can_unpin = true;
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum    = get_post( $forum_id );

			if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
				$can_unpin = false;
			}

			return apply_filters( 'fmwp_user_can_unpin_topic', $can_unpin, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_lock_topic( $user_id, $topic ) {
			$can_lock = false;

			if ( ( user_can( $user_id, 'manage_fmwp_topics_all' ) || absint( $topic->post_author ) === $user_id ) &&
				! FMWP()->common()->topic()->is_locked( $topic ) ) {
				$can_lock = true;
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum    = get_post( $forum_id );

			if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
				$can_lock = false;
			}

			return apply_filters( 'fmwp_user_can_lock_topic', $can_lock, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_unlock_topic( $user_id, $topic ) {
			$can_unlock = false;

			if ( ( user_can( $user_id, 'manage_fmwp_topics_all' ) || absint( $topic->post_author ) === $user_id ) &&
				FMWP()->common()->topic()->is_locked( $topic ) ) {
				$can_unlock = true;
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum    = get_post( $forum_id );

			if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
				$can_unlock = false;
			}

			return apply_filters( 'fmwp_user_can_unlock_topic', $can_unlock, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_trash_topic( $user_id, $topic ) {
			$can_trash = false;

			if ( 'trash' !== $topic->post_status ) {
				if ( absint( $topic->post_author ) === $user_id || user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
					$can_trash = true;
				}

				$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
				$forum    = get_post( $forum_id );

				if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
					$can_trash = false;
				}
			}

			return apply_filters( 'fmwp_user_can_trash_topic', $can_trash, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $forum
		 *
		 * @return bool
		 */
		public function can_trash_forum( $user_id, $forum ) {
			$can_trash = false;

			if ( 'trash' !== $forum->post_status ) {
				if ( absint( $forum->post_author ) === $user_id || user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
					$can_trash = true;
				}
			}

			return apply_filters( 'fmwp_user_can_trash_forum', $can_trash, $user_id, $forum );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $forum
		 *
		 * @return bool
		 */
		public function can_edit_forum( $user_id, $forum ) {
			$can_edit = false;

			if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
				$can_edit = true;
			} elseif ( absint( $forum->post_author ) === $user_id && user_can( $user_id, 'manage_fmwp_forums_own' ) ) {
				$can_edit = true;
			}

			return apply_filters( 'fmwp_user_can_edit_forum', $can_edit, $user_id, $forum );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $forum
		 *
		 * @return bool
		 */
		public function can_restore_forum( $user_id, $forum ) {
			$can_restore = false;

			if ( 'trash' === $forum->post_status ) {
				if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
					$can_restore = true;
				} else {
					$trash_author = get_post_meta( $forum->ID, 'fmwp_user_trash_id', true );
					if ( ! empty( $trash_author ) && absint( $trash_author ) === $user_id ) {
						$can_restore = true;
					}
				}
			}

			return apply_filters( 'fmwp_user_can_restore_forum', $can_restore, $user_id, $forum );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $forum
		 *
		 * @return bool
		 */
		public function can_delete_forum( $user_id, $forum ) {
			$can_delete = false;

			if ( 'trash' === $forum->post_status ) {
				if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
					$can_delete = true;
				} else {
					$trash_author = get_post_meta( $forum->ID, 'fmwp_user_trash_id', true );
					if ( ! empty( $trash_author ) && absint( $trash_author ) === $user_id ) {
						$can_delete = true;
					}
				}
			}

			return apply_filters( 'fmwp_user_can_delete_forum', $can_delete, $user_id, $forum );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_delete_topic( $user_id, $topic ) {
			$can_delete = false;

			if ( 'trash' === $topic->post_status ) {
				if ( absint( $topic->post_author ) === $user_id || user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
					$can_delete = true;
				}

				$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
				$forum    = get_post( $forum_id );

				if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
					$can_delete = false;
				}
			}

			return apply_filters( 'fmwp_user_can_delete_topic', $can_delete, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 * @param WP_Post $topic
		 *
		 * @return bool
		 */
		public function can_restore_topic( $user_id, $topic ) {
			$can_restore = false;

			if ( 'trash' === $topic->post_status ) {
				if ( user_can( $user_id, 'manage_fmwp_topics_all' ) ) {
					$can_restore = true;
				} else {
					$trash_author = get_post_meta( $topic->ID, 'fmwp_user_trash_id', true );
					if ( ! empty( $trash_author ) && absint( $trash_author ) === $user_id ) {
						$can_restore = true;
					}
				}

				$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
				$forum    = get_post( $forum_id );

				if ( ! user_can( $user_id, 'manage_fmwp_forums_all' ) && FMWP()->common()->forum()->is_locked( $forum ) ) {
					$can_restore = false;
				}
			}

			return apply_filters( 'fmwp_user_can_restore_topic', $can_restore, $user_id, $topic );
		}

		/**
		 * @param int $user_id
		 *
		 * @return integer
		 */
		public function get_topics_count( $user_id ) {
			$topics = FMWP()->common()->topic()->get_topics_by_author( $user_id, array( 'fields' => 'ids' ) );

			return ( ! empty( $topics ) && ! is_wp_error( $topics ) ) ? count( $topics ) : 0;
		}

		/**
		 * @param int $user_id
		 *
		 * @return integer
		 */
		public function get_replies_count( $user_id ) {
			$replies = FMWP()->common()->reply()->get_replies_by_author( $user_id, array( 'fields' => 'ids' ) );

			return ( ! empty( $replies ) && ! is_wp_error( $replies ) ) ? count( $replies ) : 0;
		}

		/**
		 * Get ForumWP user's role
		 *
		 *
		 * @param int|WP_User|bool $user
		 *
		 * @return bool|array
		 */
		public function get_roles( $user ) {
			if ( is_numeric( $user ) ) {
				$user = get_userdata( $user );
				if ( empty( $user ) || is_wp_error( $user ) ) {
					return false;
				}
			}

			return $user->roles;
		}

		/**
		 * @param int $user_id
		 *
		 * @return string
		 */
		public function generate_card( $user_id ) {
			$user = get_userdata( $user_id );
			if ( empty( $user ) ) {
				return '';
			}

			ob_start();

			FMWP()->get_template_part( 'user-card', $user );

			return ob_get_clean();
		}

		/**
		 * @param $user_id
		 * @param $post_id
		 *
		 * @return bool
		 */
		public function can_unreport( $user_id, $post_id ) {
			return FMWP()->reports()->is_reported_by_user( $post_id, $user_id );
		}

		/**
		 * @param bool|int $user_id
		 *
		 * @return bool
		 */
		public function can_clear_reports( $user_id = false ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}
			return user_can( $user_id, 'fmwp_remove_reports' );
		}
	}
}
