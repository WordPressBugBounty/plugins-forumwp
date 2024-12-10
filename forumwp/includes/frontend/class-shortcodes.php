<?php
namespace fmwp\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\frontend\Shortcodes' ) ) {

	/**
	 * Class Shortcodes
	 *
	 * @package fmwp\frontend
	 */
	class Shortcodes {

		/**
		 * @var bool
		 */
		private $reply_popup_loaded = false;

		/**
		 * @var bool
		 */
		private $topic_popup_loaded = false;

		/**
		 * @var bool
		 */
		private $login_popup_loaded = false;

		/**
		 * @var array
		 */
		private $preloader_styles = array();

		/**
		 * @var null
		 */
		private $forum_category_request;

		/**
		 * @var null
		 */
		private $topic_tag_request;

		/**
		 * Shortcodes constructor.
		 */
		public function __construct() {
			//page shortcodes
			add_shortcode( 'fmwp_login_form', array( &$this, 'login' ) );
			add_shortcode( 'fmwp_registration_form', array( &$this, 'registration' ) );
			add_shortcode( 'fmwp_new_forum', array( &$this, 'forum_form' ) );
			add_shortcode( 'fmwp_forums', array( &$this, 'forums_list' ) );
			add_shortcode( 'fmwp_topics', array( &$this, 'topics_list' ) );

			add_shortcode( 'fmwp_forum', array( &$this, 'forum' ) );
			add_shortcode( 'fmwp_topic', array( &$this, 'topic' ) );

			add_shortcode( 'fmwp_user_profile', array( &$this, 'user_profile' ) );

			add_shortcode( 'fmwp_forum_categories', array( &$this, 'forum_categories_list' ) );

			/**
			 * Separate User Profile tabs shortcodes
			 */
			add_shortcode( 'fmwp_user_topics', array( &$this, 'user_topics' ) );
			add_shortcode( 'fmwp_user_replies', array( &$this, 'user_replies' ) );
			add_shortcode( 'fmwp_user_edit', array( &$this, 'user_edit' ) );

			add_filter( 'body_class', array( &$this, 'body_class' ), 0 );
			/**
			 * Handlers for single topic/forum templates
			 */
			add_filter( 'single_template', array( &$this, 'cpt_template' ) );
			add_filter( 'request', array( &$this, 'taxonomy_template' ) );
		}

		/**
		 * @return string
		 */
		public function unlogged_class() {
			$unlogged_class = ! is_user_logged_in() ? 'fmwp-login-to-action' : '';
			return apply_filters( 'fmwp_unlogged_class', $unlogged_class );
		}

		/**
		 * Extend body classes
		 *
		 * @param array $classes
		 *
		 * @return array
		 */
		public function body_class( $classes ) {
			$preset_pages = FMWP()->config()->get( 'core_pages' );

			if ( ! count( $preset_pages ) ) {
				return $classes;
			}

			$preset_pages = array_keys( $preset_pages );
			foreach ( $preset_pages as $slug ) {
				if ( ! FMWP()->is_core_page( $slug ) ) {
					continue;
				}

				$classes[] = 'fmwp-page-' . $slug;

				if ( is_user_logged_in() ) {
					$classes[] = 'fmwp-page-loggedin';
				} else {
					$classes[] = 'fmwp-page-loggedout';
				}
			}

			return $classes;
		}

		/**
		 * Check if the Forum or Topic has custom template, or load by default page template
		 *
		 * @param string $single_template
		 *
		 * @return string
		 */
		public function cpt_template( $single_template ) {
			global $post;

			if ( 'fmwp_forum' === $post->post_type || 'fmwp_topic' === $post->post_type ) {
				if ( 'fmwp_forum' === $post->post_type ) {
					$global_template = FMWP()->options()->get( 'default_forums_template' );
				} else {
					$global_template = FMWP()->options()->get( 'default_topic_template' );
				}

				$individual_template = get_post_meta( $post->ID, 'fmwp_template', true );

				if ( empty( $individual_template ) ) {
					$template = $global_template;
				} elseif ( 'fmwp_individual_default' === $individual_template ) {
					$template = '';
				} else {
					$template = $individual_template;
				}

				if ( ! empty( $template ) ) {
					$custom_template = FMWP()->get_template( $template );
					if ( file_exists( $custom_template ) ) {
						$single_template = $custom_template;
					}
				} else {
					add_filter( 'template_include', array( &$this, 'cpt_template_include' ) );
				}
			}

			return $single_template;
		}

		/**
		 * If it's forum or topic individual page loading by default Page template from theme
		 *
		 * @param string $template
		 *
		 * @return string
		 */
		public function cpt_template_include( $template ) {
			if ( FMWP()->is_forum_page() || FMWP()->is_topic_page() ) {
				if ( ! wp_is_block_theme() ) {
					$t              = get_template_directory() . DIRECTORY_SEPARATOR . 'page.php';
					$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'page.php';
					if ( file_exists( $child_template ) ) {
						$t = $child_template;
					}

					// load index.php if page isn't found
					if ( ! file_exists( $t ) ) {
						$t              = get_template_directory() . DIRECTORY_SEPARATOR . 'index.php';
						$child_template = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'index.php';
						if ( file_exists( $child_template ) ) {
							$template = $t;
						}
					}

					if ( ! file_exists( $t ) ) {
						return $template;
					}
				}

				add_action( 'wp_head', array( &$this, 'on_wp_head_finish' ), 99999999 );
				add_filter( 'the_content', array( &$this, 'cpt_content' ) );
				add_filter( 'post_class', array( &$this, 'hidden_title_class' ) );
			}

			return apply_filters( 'fmwp_template_include', $template );
		}

		/**
		 * Add hidden class if users need to add some custom CSS on page template to hide a header when title is hidden
		 *
		 * @param array $classes
		 *
		 * @return array
		 */
		public function hidden_title_class( $classes ) {
			$classes[] = 'fmwp-hidden-title';
			return $classes;
		}


		/**
		 * Clear the post title
		 */
		public function on_wp_head_finish() {
			if ( ! wp_is_block_theme() ) {
				add_filter( 'the_title', array( $this, 'clear_title' ), 10, 2 );
			}
		}

		/**
		 * Return empty title
		 * @param $title
		 * @param $post_id
		 *
		 * @return string
		 */
		public function clear_title( $title, $post_id ) {
			$post = get_post( $post_id );

			if ( in_array( $post->post_type, array( 'fmwp_forum', 'fmwp_topic' ), true ) ) {
				$title = '';
			}

			return $title;
		}

		/**
		 * Set default content of the forum and topic page
		 *
		 * @param $content
		 *
		 * @return string
		 */
		public function cpt_content( $content ) {
			global $post;

			if ( ! wp_is_block_theme() ) {
				remove_filter( 'the_title', array( $this, 'clear_title' ) );
			}

			if ( FMWP()->is_forum_page() ) {
				$content = $this->forum(
					array(
						'id'          => $post->ID,
						'show_header' => 'yes',
					)
				);
			} elseif ( FMWP()->is_topic_page() ) {
				$content = $this->topic(
					array(
						'id'          => $post->ID,
						'show_header' => 'yes',
					)
				);
			}

			return $content;
		}

		/**
		 * Replace query if load permalink of Topic Tag or Forum Category
		 *
		 * @param array $query_request
		 *
		 * @return array
		 */
		public function taxonomy_template( $query_request ) {
			if ( ! empty( $query_request['fmwp_forum_category'] ) ) {
				$forums_page_id = FMWP()->common()->get_preset_page_id( 'forums' );
				$forums_slug    = '';
				if ( $forums_page_id ) {
					$forums_page = get_post( $forums_page_id );
					if ( ! empty( $forums_page ) && ! is_wp_error( $forums_page ) ) {
						$forums_slug = $forums_page->post_name;
					}
				}

				$this->forum_category_request = $query_request['fmwp_forum_category'];

				$query_request = array(
					'page'     => '',
					'pagename' => $forums_slug,
				);

				add_filter( 'the_title', array( &$this, 'tax_title' ), 10, 2 );
				add_filter( 'the_content', array( &$this, 'tax_content' ) );
				add_filter( 'post_class', array( &$this, 'tax_class' ) );
			}

			if ( ! empty( $query_request['fmwp_topic_tag'] ) ) {
				$topics_page_id = FMWP()->common()->get_preset_page_id( 'topics' );
				$topics_slug    = '';
				if ( $topics_page_id ) {
					$topics_page = get_post( $topics_page_id );
					if ( ! empty( $topics_page ) && ! is_wp_error( $topics_page ) ) {
						$topics_slug = $topics_page->post_name;
					}
				}

				$this->topic_tag_request = $query_request['fmwp_topic_tag'];

				$query_request = array(
					'page'     => '',
					'pagename' => $topics_slug,
				);

				add_filter( 'the_title', array( &$this, 'tax_title' ), 10, 2 );
				add_filter( 'the_content', array( &$this, 'tax_content' ) );
				add_filter( 'post_class', array( &$this, 'tax_class' ) );
			}

			return $query_request;
		}

		/**
		 * Replace page title if load CPT terms
		 *
		 * @param $title
		 * @param $post_id
		 *
		 * @return string
		 */
		public function tax_title( $title, $post_id ) {
			if ( FMWP()->common()->get_preset_page_id( 'forums' ) === $post_id || FMWP()->common()->get_preset_page_id( 'topics' ) === $post_id ) {
				if ( ! empty( $this->forum_category_request ) ) {
					$term = get_term_by( 'slug', $this->forum_category_request, 'fmwp_forum_category' );
					if ( ! empty( $term ) ) {
						// translators: %s is a forum category name
						$title = sprintf( __( 'Forum Category: %s', 'forumwp' ), $term->name );
					}
				} elseif ( ! empty( $this->topic_tag_request ) ) {
					$term = get_term_by( 'slug', $this->topic_tag_request, 'fmwp_topic_tag' );
					if ( ! empty( $term ) ) {
						// translators: %s is a topic tag name
						$title = sprintf( __( 'Topic Tag: %s', 'forumwp' ), $term->name );
					}
				}
			}

			return $title;
		}

		/**
		 * Replace page content if load CPT terms
		 * @param $content
		 *
		 * @return string
		 */
		public function tax_content( $content ) {
			if ( ! empty( $this->forum_category_request ) ) {
				$term = get_term_by( 'slug', $this->forum_category_request, 'fmwp_forum_category' );
				if ( ! empty( $term ) ) {
					$content = '[fmwp_forums category="' . $term->term_id . '" new_forum="no" /]';
				}
			} elseif ( ! empty( $this->topic_tag_request ) ) {
				$term = get_term_by( 'slug', $this->topic_tag_request, 'fmwp_topic_tag' );
				if ( ! empty( $term ) ) {
					$content = '[fmwp_topics tag="' . $term->term_id . '" new_topic="no" /]';
				}
			}
			return $content;
		}

		/**
		 * Replace page classes if load CPT terms
		 *
		 * @param $classes
		 *
		 * @return array
		 */
		public function tax_class( $classes ) {
			if ( ! empty( $this->forum_category_request ) ) {
				$classes[] = 'fmwp-tax-forum-category';
			} elseif ( ! empty( $this->topic_tag_request ) ) {
				$classes[] = 'fmwp-tax-topic-tag';
			}
			return $classes;
		}

		/**
		 * Check if preloader styles already loaded
		 *
		 * @param $size
		 * @param $display
		 *
		 * @return bool
		 */
		public function check_preloader_css( $size, $display ) {
			if ( ! empty( $this->preloader_styles[ $size ][ $display ] ) ) {
				return true;
			}

			$this->preloader_styles[ $size ][ $display ] = true;
			return false;
		}

		/**
		 * Forums list shortcode callback
		 *
		 * [fmwp_forums /]
		 *
		 * @param array $args
		 *
		 * @return string
		 *
		 * @version 2.0
		 */
		public function forums_list( $args ) {
			$default_args = apply_filters(
				'fmwp_forums_list_shortcode_default_args',
				array(
					'search'   => 'yes',
					'category' => '',
					'with_sub' => 1,
					'order'    => FMWP()->options()->get( 'default_forums_order' ),
				)
			);

			$args = shortcode_atts( $default_args, $args );

			wp_enqueue_script( 'fmwp-forums-list' );
			wp_enqueue_style( 'fmwp-forums-list' );

			do_action( 'fmwp_on_forums_shortcode_init' );

			ob_start();

			FMWP()->get_template_part(
				'js/forums-list',
				array(
					'actions' => 'edit',
				)
			);

			FMWP()->get_template_part( 'archive-forum', $args );

			return ob_get_clean();
		}

		/**
		 * Topics list shortcode callback
		 *
		 * [fmwp_topics /]
		 *
		 * @param array $args
		 *
		 *
		 * search yes|no
		 * new_topic yes|no
		 *
		 * tag Topic Tag ID
		 *
		 * status open|pending|locked|spam|trash <- these values need to be operated via WP native Post Status
		 * type normal|pinned|announcement|global <- these values from meta
		 *
		 * order any value from sort dropdown
		 *
		 * @return string
		 */
		public function topics_list( $args ) {
			$default_args = apply_filters(
				'fmwp_topics_list_shortcode_default_args',
				array(
					'search'        => 'yes',
					'new_topic'     => 'yes',
					'show_forum'    => FMWP()->options()->get( 'show_forum' ) ? 'yes' : 'no',
					'tag'           => '',
					'status'        => '',
					'type'          => '',
					'order'         => FMWP()->options()->get( 'default_topics_order' ),
					'default_forum' => FMWP()->options()->get( 'default_forum' ),
					'is_block'      => false,
				)
			);

			$args = shortcode_atts( $default_args, $args );

			wp_enqueue_script( 'fmwp-topics-list' );
			wp_enqueue_style( 'fmwp-topics-list' );

			do_action( 'fmwp_on_topics_shortcode_init' );

			if ( ! is_user_logged_in() ) {
				if ( isset( $args['new_topic'] ) && 'yes' === $args['new_topic'] && ! empty( $args['default_forum'] ) ) {
					add_action( 'wp_footer', array( &$this, 'login_popup' ), -1 );
				}
			} else {
				$add_new = 'yes' === $args['new_topic'] && ! empty( $args['default_forum'] ) &&
							FMWP()->user()->can_create_topic( $args['default_forum'] );

				if ( current_user_can( 'manage_fmwp_topics_all' ) ) {
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

					if ( ! empty( $forum_ids ) ) {

						foreach ( $forum_ids as $k => $forum_id ) {
							if ( post_password_required( $forum_id ) ) {
								unset( $forum_ids[ $k ] );
							}
						}

						$forum_ids = array_values( $forum_ids );

						if ( ! empty( $forum_ids ) ) {
							$posts = get_posts(
								array(
									'post_type'      => 'fmwp_topic',
									'post_status'    => FMWP()->common()->topic()->post_status,
									'posts_per_page' => 1,
									'paged'          => 1,
									'meta_query'     => array(
										array(
											'key'     => 'fmwp_forum',
											'value'   => $forum_ids,
											'compare' => 'IN',
										),
									),
								)
							);
						}
					}
				} elseif ( current_user_can( 'fmwp_edit_own_topic' ) ) {

					$posts = FMWP()->common()->topic()->get_topics_by_author(
						get_current_user_id(),
						array(
							'paged'          => 1,
							'posts_per_page' => 1,
						)
					);

				}

				$edit_topics = ! empty( $posts );

				if ( $add_new || $edit_topics ) {
					// pre-init ajax loader styles
					FMWP()->ajax_loader_styles( 25 );

					add_action( 'wp_footer', array( &$this, 'topic_popup' ), -1 );
				}
			}

			ob_start();

			FMWP()->get_template_part(
				'js/topics-list',
				array(
					'actions'    => 'edit',
					'show_forum' => ( 'yes' === $args['show_forum'] ),
					'is_block'   => (bool) $args['is_block'],
				)
			);

			FMWP()->get_template_part( 'archive-topic', $args );

			return ob_get_clean();
		}

		/**
		 * @param $args
		 *
		 * @return string
		 */
		public function forum( $args ) {
			$default_args = apply_filters(
				'fmwp_forum_shortcode_default_args',
				array(
					'id'          => '',
					'order'       => FMWP()->options()->get( 'default_topics_order' ),
					'show_header' => 'no',
				)
			);

			$args = shortcode_atts( $default_args, $args );
			if ( empty( $args['id'] ) ) {
				return '';
			}

			if ( is_user_logged_in() ) {
				wp_enqueue_script( 'fmwp-forum-logged' );
			} else {
				wp_enqueue_script( 'fmwp-forum' );
				wp_enqueue_script( 'fmwp-unlogged-user' );
			}
			wp_enqueue_style( 'fmwp-forum' );

			do_action( 'fmwp_on_forum_shortcode_init' );

			if ( ! is_user_logged_in() ) {
				add_action( 'wp_footer', array( &$this, 'login_popup' ), -1 );
			} elseif ( FMWP()->user()->can_create_topic( $args['id'] ) ) {
					// pre-init ajax loader styles
					FMWP()->ajax_loader_styles( 25 );

					add_action( 'wp_footer', array( &$this, 'topic_popup' ), -1 );
			}

			ob_start();

			FMWP()->get_template_part(
				'js/topics-list',
				array(
					'actions'    => 'edit',
					'show_forum' => false,
				)
			);

			FMWP()->get_template_part( 'forum', $args );

			$content = ob_get_clean();

			remove_filter( 'the_content', array( $this, 'cpt_content' ) );

			return $content;
		}

		/**
		 * @param $args
		 *
		 * @return string
		 */
		public function topic( $args ) {
			$default_args = apply_filters(
				'fmwp_topic_shortcode_default_args',
				array(
					'id'          => '',
					'order'       => 'date_asc',
					'show_forum'  => ( FMWP()->options()->get( 'show_forum' ) ) ? 'yes' : 'no',
					'show_header' => 'no',
				)
			);

			$args = shortcode_atts( $default_args, $args );

			if ( empty( $args['id'] ) ) {
				return '';
			}

			if ( is_user_logged_in() ) {
				wp_enqueue_script( 'fmwp-topic-logged' );
			} else {
				wp_enqueue_script( 'fmwp-topic' );
				wp_enqueue_script( 'fmwp-unlogged-user' );
			}
			wp_enqueue_style( 'fmwp-topic' );

			do_action( 'fmwp_on_topic_shortcode_init' );

			$topic = get_post( $args['id'] );
			if ( is_user_logged_in() ) {
				if ( FMWP()->user()->can_reply( $topic->ID ) ) {
					// pre-init ajax loader styles
					FMWP()->ajax_loader_styles( 25 );

					add_action(
						'wp_footer',
						function () use ( $topic ) {
							$this->reply_popup( $topic );
						},
						-1
					);
				}

				if ( FMWP()->user()->can_edit_topic( get_current_user_id(), $topic ) ) {
					// pre-init ajax loader styles
					FMWP()->ajax_loader_styles( 25 );
					add_action( 'wp_footer', array( &$this, 'topic_popup' ), -1 );
				}
			} else {
				add_action( 'wp_footer', array( &$this, 'login_popup' ), -1 );
			}

			ob_start();

			FMWP()->get_template_part( 'topic', $args );

			$content = ob_get_clean();

			remove_filter( 'the_content', array( $this, 'cpt_content' ) );

			return $content;
		}

		/**
		 *
		 */

		public function reply_popup( $topic = null ) {
			if ( $this->reply_popup_loaded ) {
				return;
			}

			if ( ! $topic ) {
				$topic_id = get_the_ID();
			} else {
				$topic_id = $topic->ID;
			}

			do_action( 'fmwp_on_reply_popup_loading' );

			$this->reply_popup_loaded = true;

			wp_enqueue_script( 'fmwp-reply-popup' );
			wp_enqueue_style( 'fmwp-reply-popup' );

			ob_start();

			FMWP()->get_template_part( 'reply-popup', array( 'topic_id' => $topic_id ) );

			ob_get_flush();
		}

		/**
		 *
		 */
		public function topic_popup() {
			if ( $this->topic_popup_loaded ) {
				return;
			}

			do_action( 'fmwp_on_topic_popup_loading' );

			$this->topic_popup_loaded = true;

			wp_enqueue_script( 'fmwp-topic-popup' );
			wp_enqueue_style( 'fmwp-topic-popup' );

			ob_start();

			FMWP()->get_template_part(
				'js/single-topic',
				array(
					'show_forum' => ! FMWP()->is_forum_page(),
				)
			);

			FMWP()->get_template_part( 'topic-popup' );

			ob_get_flush();
		}

		/**
		 *
		 */
		public function login_popup() {
			if ( $this->login_popup_loaded ) {
				return;
			}

			$this->login_popup_loaded = true;

			wp_enqueue_style( 'fmwp-login-popup' );

			ob_start();

			FMWP()->get_template_part( 'login-popup' );

			ob_get_flush();
		}

		/**
		 * @param array $args
		 *
		 * @return string
		 *
		 * @version 2.0
		 */
		public function login( $args ) {
			wp_enqueue_script( 'fmwp-front-global' );
			wp_enqueue_style( 'fmwp-login' );

			if ( is_user_logged_in() ) {
				return __( 'You are already logged in', 'forumwp' );
			}

			$self_redirect    = FMWP()->get_current_url();
			$default_redirect = $self_redirect;
			$login_redirect   = FMWP()->options()->get( 'login_redirect' );
			if ( ! empty( $login_redirect ) ) {
				$default_redirect = $login_redirect;
			}

			$default_args = apply_filters(
				'fmwp_login_shortcode_default_args',
				array(
					'redirect' => $default_redirect,
					'is_popup' => false,
				)
			);

			$args = shortcode_atts( $default_args, $args );

			if ( ! empty( $args['is_popup'] ) ) {
				$args['redirect'] = $self_redirect;
			} else {
				$args['redirect'] = ! empty( $_GET['redirect_to'] ) ? urldecode( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) ) : $args['redirect']; // phpcs:ignore WordPress.Security.NonceVerification
			}

			ob_start();

			FMWP()->get_template_part( 'login', $args );

			return ob_get_clean();
		}

		/**
		 * @param array $args
		 *
		 * @return string
		 *
		 * @version 2.0
		 */
		public function registration( $args ) {
			wp_enqueue_script( 'fmwp-front-global' );
			wp_enqueue_style( 'fmwp-forms' );

			if ( is_user_logged_in() ) {
				return __( 'You are already logged in', 'forumwp' );
			}

			$default_redirect  = '';
			$register_redirect = FMWP()->options()->get( 'register_redirect' );
			if ( ! empty( $register_redirect ) ) {
				$default_redirect = $register_redirect;
			}

			$default_args = apply_filters(
				'fmwp_registration_shortcode_default_args',
				array(
					'first_name' => 'show',
					'last_name'  => 'show',
					'redirect'   => $default_redirect,
				)
			);

			$args = shortcode_atts( $default_args, $args );

			ob_start();

			FMWP()->get_template_part( 'registration', $args );

			return ob_get_clean();
		}

		/**
		 * @param $args
		 *
		 * @return string
		 *
		 * @version 2.0
		 */
		public function forum_form( $args ) {
			if ( ! is_user_logged_in() ) {
				return '';
			}

			if ( ! FMWP()->user()->can_create_forum() ) {
				return '';
			}

			wp_enqueue_script( 'fmwp-new-forum' );
			wp_enqueue_style( 'fmwp-forms' );

			$default_args = apply_filters( 'fmwp_new_forum_shortcode_default_args', array() );

			$args = shortcode_atts( $default_args, $args );

			// handle $_GET['msg'] via Form's notices
			$new_forum = FMWP()->frontend()->forms( array( 'id' => 'fmwp-create-forum' ) );
			if ( ! empty( $_GET['fmwp-msg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				switch ( sanitize_key( $_GET['fmwp-msg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					case 'forum-created':
						if ( ! $new_forum->has_errors() ) {

							$new_forum->add_notice(
								__( 'Forum created successfully', 'forumwp' ),
								'forum-created'
							);

						}

						break;
				}
			}

			ob_start();

			FMWP()->get_template_part( 'new-forum', $args );

			return ob_get_clean();
		}

		/**
		 * @param $args
		 *
		 * @return string
		 */
		public function user_profile( $args ) {
			$default_args = apply_filters( 'fmwp_user_profile_shortcode_default_args', array() );

			$args = shortcode_atts( $default_args, $args );

			if ( ! is_user_logged_in() && ! FMWP()->is_core_page( 'profile' ) ) {
				$user_login = get_query_var( 'fmwp_user' );
				if ( empty( $user_login ) ) {
					return $this->login( array( 'redirect' => FMWP()->get_current_url() ) );
				}
			}

			wp_enqueue_script( 'fmwp-profile' );
			wp_enqueue_style( 'fmwp-profile' );

			do_action( 'fmwp_on_profile_shortcode_init' );

			ob_start();

			FMWP()->get_template_part( 'profile/main', $args );

			return ob_get_clean();
		}

		/**
		 * @param $args
		 *
		 * @return string
		 */
		public function user_topics( $args ) {
			wp_enqueue_script( 'fmwp-user-topics' );
			wp_enqueue_style( 'fmwp-user-topics' );

			$default_args = apply_filters(
				'fmwp_user_topics_default_args',
				array(
					'user_id' => get_current_user_id(),
				)
			);

			$args = shortcode_atts( $default_args, $args );

			ob_start();

			FMWP()->get_template_part( 'user/topics', $args );

			return ob_get_clean();
		}

		/**
		 * @param $args
		 *
		 * @return string
		 */
		public function user_replies( $args ) {
			wp_enqueue_script( 'fmwp-user-replies' );
			wp_enqueue_style( 'fmwp-user-replies' );

			$default_args = apply_filters(
				'fmwp_user_replies_default_args',
				array(
					'user_id' => get_current_user_id(),
				)
			);

			do_action( 'fmwp_on_user_reply_shortcode_init' );

			$args = shortcode_atts( $default_args, $args );

			ob_start();

			FMWP()->get_template_part( 'user/replies', $args );

			return ob_get_clean();
		}

		/**
		 * @param $args
		 *
		 * @return string
		 */
		public function user_edit() {
			wp_enqueue_script( 'fmwp-new-forum' );
			wp_enqueue_style( 'fmwp-forms' );

			if ( ! is_user_logged_in() ) {
				return '';
			}

			ob_start();

			FMWP()->get_template_part( 'profile/edit' );

			return ob_get_clean();
		}

		/**
		 * New menu
		 *
		 * @param string $element
		 * @param string $trigger
		 * @param array $items
		 */
		public function dropdown_menu( $element, $trigger, $items = array() ) {
			?>
			<div class="fmwp-dropdown" data-element="<?php echo esc_attr( $element ); ?>" data-trigger="<?php echo esc_attr( $trigger ); ?>">
				<ul>
					<?php foreach ( $items as $v ) { ?>
						<li><?php echo wp_kses( $v, FMWP()->get_allowed_html( 'templates' ) ); ?></li>
					<?php } ?>
				</ul>
			</div>
			<?php
		}

		/**
		 * @param $args
		 *
		 * @return string
		 */
		public function forum_categories_list( $args ) {
			$default_args = apply_filters(
				'fmwp_forum_categories_list_shortcode_default_args',
				array(
					'search' => 'yes',
					'order'  => 'date_desc',
				)
			);

			$args = shortcode_atts( $default_args, $args );

			wp_enqueue_script( 'fmwp-forum-categories-list' );
			wp_enqueue_style( 'fmwp-forum-categories-list' );

			ob_start();

			FMWP()->get_template_part( 'archive-forum-category', $args );

			return ob_get_clean();
		}
	}
}
