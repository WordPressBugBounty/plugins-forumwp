<?php
namespace fmwp\common;

use WP_Filesystem_Base;
use function WP_Filesystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\common\Mail' ) ) {

	/**
	 * Class Mail
	 *
	 * @package fmwp\common
	 */
	class Mail {

		/**
		 * @var array
		 */
		public $paths = array();

		public function get_public_emails() {
			$globally_enabled_emails = array();
			$email_notifications     = FMWP()->config()->get( 'email_notifications' );
			if ( ! empty( $email_notifications ) && is_array( $email_notifications ) ) {
				foreach ( $email_notifications as $email_key => $email_notification ) {
					if ( ! array_key_exists( 'recipient', $email_notification ) || 'user' !== $email_notification['recipient'] ) {
						continue;
					}
					$email_enabled_globally = FMWP()->options()->get( $email_key . '_on' );
					if ( ! $email_enabled_globally ) {
						continue;
					}

					$globally_enabled_emails[ $email_key ] = array(
						'title'       => $email_notification['title'],
						'description' => $email_notification['description'],
					);
				}
			}

			return $globally_enabled_emails;
		}

		/**
		 * Check blog ID on multisite, return '' if single site
		 *
		 * @return string
		 */
		public function get_blog_id() {
			$blog_id = '';
			if ( is_multisite() ) {
				$blog_id = DIRECTORY_SEPARATOR . get_current_blog_id();
			}

			return $blog_id;
		}

		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @param string $template_name
		 * @return string
		 */
		public function locate_template( $template_name ) {
			// check if there is template at theme folder
			$blog_id = $this->get_blog_id();

			// get template file from the current theme
			$template = locate_template(
				array(
					trailingslashit( 'forumwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ) . $template_name . '.php',
					trailingslashit( 'forumwp' . DIRECTORY_SEPARATOR . 'emails' ) . $template_name . '.php',
				)
			);

			// if there isn't template at theme folder get template file from plugin dir
			if ( ! $template ) {
				$path     = ! empty( $this->paths[ $template_name ] ) ? $this->paths[ $template_name ] : FMWP_PATH . 'templates' . DIRECTORY_SEPARATOR . 'emails';
				$template = trailingslashit( $path ) . $template_name . '.php';
			}

			// Return what we found.
			return apply_filters( 'fmwp_locate_email_template', $template, $template_name );
		}

		/**
		 * @param $slug
		 * @param $args
		 *
		 * @return bool|string
		 */
		public function get_template( $slug, $args = array() ) {
			$located = wp_normalize_path( $this->locate_template( $slug ) );

			$located = apply_filters( 'fmwp_email_template_path', $located, $slug, $args );

			if ( ! file_exists( $located ) ) {
				// translators: %s template
				_doing_it_wrong( __FUNCTION__, wp_kses( sprintf( __( '<code>%s</code> does not exist.', 'forumwp' ), $located ), FMWP()->get_allowed_html( 'templates' ) ), esc_html( FMWP_VERSION ) );
				return false;
			}

			ob_start();

			do_action( 'fmwp_before_email_template_part', $slug, $located, $args );

			include $located;

			do_action( 'fmwp_after_email_template_part', $slug, $located, $args );

			return ob_get_clean();
		}

		/**
		 * Method returns expected path for template
		 *
		 * @access public
		 *
		 * @param string $location
		 * @param string $template_name
		 *
		 * @return string
		 */
		public function get_template_file( $location, $template_name ) {
			$template_name_file = $this->get_template_filename( $template_name );

			$template_path = '';
			switch ( $location ) {
				case 'theme':
					//save email template in blog ID folder if we use multisite
					$blog_id = $this->get_blog_id();

					$template_path = trailingslashit( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'forumwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ) . $template_name_file . '.php';
					break;
				case 'plugin':
					$path          = ! empty( $this->paths[ $template_name ] ) ? $this->paths[ $template_name ] : FMWP_PATH . 'templates' . DIRECTORY_SEPARATOR . 'emails';
					$template_path = trailingslashit( $path ) . $template_name . '.php';
					break;
			}

			return wp_normalize_path( $template_path );
		}

		/**
		 * @param string $template_name
		 *
		 * @return string
		 */
		public function get_template_filename( $template_name ) {
			return apply_filters( 'fmwp_change_email_template_file', $template_name );
		}

		/**
		 * Ajax copy template to the theme
		 *
		 * @param string $template
		 * @return bool
		 */
		public function copy_template( $template ) {
			$in_theme = $this->template_in_theme( $template );
			if ( $in_theme ) {
				return false;
			}
			global $wp_filesystem;

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';

				$credentials = request_filesystem_credentials( self_admin_url() );
				WP_Filesystem( $credentials );
			}

			$plugin_template_path = $this->get_template_file( 'plugin', $template );
			$theme_template_path  = $this->get_template_file( 'theme', $template );

			$theme_dir_path = dirname( $theme_template_path );
			if ( ! is_dir( $theme_dir_path ) ) {
				wp_mkdir_p( $theme_dir_path ); // third argument enables recursive mode
			}

			return file_exists( $plugin_template_path ) && copy( $plugin_template_path, $theme_template_path );
		}

		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @access public
		 * @param string $template_name
		 * @return bool
		 */
		public function template_in_theme( $template_name ) {
			$template_name_file = $this->get_template_filename( $template_name );

			$blog_id = $this->get_blog_id();

			// check if there is template at theme blog ID folder
			$template = locate_template(
				array(
					trailingslashit( 'forumwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ) . $template_name_file . '.php',
				)
			);

			// Return what we found.
			return (bool) $template;
		}

		/**
		 * @param $slug
		 * @param $args
		 * @return bool|string
		 */
		public function get_email_template( $slug, $args = array() ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- can be used in template.
			$located = $this->locate_template( $slug );

			if ( ! file_exists( $located ) ) {
				// translators: %s template
				_doing_it_wrong( __FUNCTION__, wp_kses( sprintf( __( '<code>%s</code> does not exist.', 'forumwp' ), $located ), FMWP()->get_allowed_html( 'templates' ) ), esc_html( FMWP_VERSION ) );
				return false;
			}

			ob_start();

			include $located;

			return ob_get_clean();
		}

		/**
		 * Prepare email template to send
		 *
		 * @param string $slug
		 * @param array  $args
		 * @return string
		 */
		public function prepare_template( $slug, $args = array() ) {
			$args['slug'] = $slug;

			ob_start();

			FMWP()->get_template_part( 'emails/base-wrapper', $args );

			$message = ob_get_clean();

			$message = apply_filters( 'fmwp_email_template_content', $message, $slug, $args );

			// Convert tags in email template
			return $this->replace_placeholders( $message, $args );
		}

		/**
		 * Send Email function
		 *
		 * @param string $email
		 * @param null $template
		 * @param array $args
		 */
		public function send( $email, $template, $args = array() ) {
			if ( ! is_email( $email ) ) {
				return;
			}

			$email_notifications = FMWP()->config()->get( 'email_notifications' );
			if ( ! array_key_exists( $template, $email_notifications ) ) {
				return;
			}

			if ( ! FMWP()->options()->get( $template . '_on' ) ) {
				return;
			}

			$disable = apply_filters( 'fmwp_disable_email_notification_by_hook', false, $email, $template, $args );
			if ( $disable ) {
				return;
			}

			if ( array_key_exists( 'recipient', $email_notifications[ $template ] ) && 'user' === $email_notifications[ $template ]['recipient'] ) {
				if ( empty( $args['recipient_id'] ) ) {
					return;
				}

				$usermeta = get_user_meta( $args['recipient_id'], 'fmwp_enabled_' . $template . '_notification', true );
				if ( empty( $usermeta ) ) {
					return;
				}
			}

			$attachments  = null;
			$content_type = apply_filters( 'fmwp_email_template_content_type', 'text/html', $template, $args, $email );

			$headers  = 'From: ' . FMWP()->options()->get( 'mail_from' ) . ' <' . FMWP()->options()->get( 'mail_from_addr' ) . '>' . "\r\n";
			$headers .= "Content-Type: {$content_type}\r\n";

			$subject = apply_filters( 'fmwp_email_send_subject', FMWP()->options()->get( $template . '_sub' ), $template, $email );
			$subject = $this->replace_placeholders( $subject, $args );

			$message = $this->prepare_template( $template, $args );

			// Send mail
			wp_mail( $email, $subject, $message, $headers, $attachments );

			do_action( 'fmwp_after_email_sending', $email, $template, $args );
		}

		/**
		 * Replace placeholders
		 *
		 * @param string $content
		 * @param array  $args
		 *
		 * @return string
		 */
		public function replace_placeholders( $content, $args ) {
			$tags         = array_map(
				function ( $item ) {
					return '{' . $item . '}';
				},
				array_keys( $args )
			);
			$tags_replace = array_values( $args );

			return str_replace( $tags, $tags_replace, $content );
		}
	}
}
