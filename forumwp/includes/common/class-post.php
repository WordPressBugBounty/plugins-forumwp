<?php
namespace fmwp\common;

use WP_Filesystem_Base;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Post' ) ) {

	/**
	 * Class Post
	 *
	 * @package fmwp\common
	 */
	class Post {

		/**
		 * @param string $content
		 * @param string $post_type
		 *
		 * @return array
		 */
		public function prepare_content( $content, $post_type ) {
			$post_content   = $content;
			$origin_content = $post_content;

			if ( $post_content ) {
				$safe_content = FMWP()->common()->mention_links( $post_content, array( 'post_type' => $post_type ) );

				// shared a link
				$post_content = FMWP()->parse_embed( $safe_content );
			}

			return apply_filters( 'fmwp_prepare_content', array( $origin_content, $post_content ), $post_type );
		}

		/**
		 * Check if post exists
		 *
		 * @param int $post_id
		 *
		 * @return bool
		 */
		public function exists( $post_id ) {
			if ( empty( $post_id ) ) {
				return false;
			}

			$post = get_post( $post_id );

			return ! ( empty( $post ) || is_wp_error( $post ) );
		}

		/**
		 * @param $post_id
		 *
		 * @return bool
		 */
		public function is_trashed( $post_id ) {
			$post = get_post( $post_id );
			if ( empty( $post ) || is_wp_error( $post ) ) {
				return true;
			}

			if ( 'trash' === $post->post_status ) {
				return true;
			}

			return false;
		}

		/**
		 * @param int|WP_Post $post
		 *
		 * @return bool|null
		 */
		public function is_locked( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- used in the child methods.
			return false;
		}

		/**
		 * @param int|WP_Post|array $post
		 *
		 * @return bool|null
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
				$spam = FMWP()->common()->reply()->is_spam( $post );
			} elseif ( 'fmwp_topic' === $post->post_type ) {
				$spam = FMWP()->common()->topic()->is_spam( $post );
			}

			return $spam;
		}

		/**
		 * Get File Name without path and extension
		 *
		 * @param $file
		 *
		 * @return string
		 */
		public function get_template_name( $file ) {
			$file = basename( $file );
			return preg_replace( '/\\.[^.\\s]{3,4}$/', '', $file );
		}

		/**
		 * Get Templates
		 *
		 * @param string|WP_Post $post
		 *
		 * @return array
		 */
		public function get_templates( $post ) {
			// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
			if ( in_array( $post, array( 'fmwp_topic', 'fmwp_forum' ), true ) ) {
				if ( 'fmwp_forum' === $post ) {
					$prefix = 'Forum';
				} else {
					$prefix = 'Topic';
				}
			} elseif ( is_object( $post ) ) {
				if ( isset( $post->post_type ) && 'fmwp_forum' === $post->post_type ) {
					$prefix = 'Forum';
				} elseif ( isset( $post->post_type ) && 'fmwp_topic' === $post->post_type ) {
					$prefix = 'Topic';
				}
			}

			if ( ! isset( $prefix ) ) {
				return array();
			}

			global $wp_filesystem;

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( site_url() );
				WP_Filesystem( $credentials );
			}

			$dir = FMWP()->theme_templates;

			$templates = array();
			if ( is_dir( $dir ) ) {
				$handle = @opendir( $dir );
				// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- reading folder's content here
				while ( false !== ( $filename = readdir( $handle ) ) ) {
					if ( '.' === $filename || '..' === $filename ) {
						continue;
					}

					// show only root *.php files inside templates dir for getting Job templates
					if ( is_dir( wp_normalize_path( $dir . DIRECTORY_SEPARATOR . $filename ) ) ) {
						continue;
					}

					$clean_filename = $this->get_template_name( $filename );

					$source  = $wp_filesystem->get_contents( wp_normalize_path( $dir . DIRECTORY_SEPARATOR . $filename ) );
					$tokens  = @\token_get_all( $source );
					$comment = array(
						T_COMMENT, // All comments since PHP5
						T_DOC_COMMENT, // PHPDoc comments
					);
					foreach ( $tokens as $token ) {
						if ( in_array( $token[0], $comment, true ) && false !== strpos( $token[1], '/* ' . $prefix . ' Template:' ) ) {
							$txt                          = $token[1];
							$txt                          = str_replace( array( '/* ' . $prefix . ' Template: ', ' */' ), '', $txt );
							$templates[ $clean_filename ] = $txt;
						}
					}
				}
				closedir( $handle );

				asort( $templates );
			}

			return $templates;
			// phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
		}

		/**
		 * @param int $post_id
		 *
		 * @return bool
		 */
		public function is_pending( $post_id ) {
			$post = get_post( $post_id );
			if ( empty( $post ) || is_wp_error( $post ) ) {
				return true;
			}

			if ( 'pending' === $post->post_status ) {
				return true;
			}

			return false;
		}

		/**
		 * @param int|WP_Post|array $post
		 *
		 * @return int
		 */
		public function get_author_id( $post ) {
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );
			}

			if ( ! empty( $post ) && ! is_wp_error( $post ) ) {
				return absint( $post->post_author );
			}

			return false;
		}
	}
}
