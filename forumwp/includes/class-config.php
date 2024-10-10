<?php
namespace fmwp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\Config' ) ) {

	/**
	 * Class Config
	 *
	 * @package fmwp
	 */
	class Config {

		/**
		 * @var array
		 */
		private $defaults = array();

		/**
		 * @var array
		 */
		private $custom_roles = array();

		/**
		 * @var array
		 */
		private $all_caps = array();

		/**
		 * @var array
		 */
		private $capabilities_map = array();

		/**
		 * @var array
		 */
		private $core_pages = array();

		/**
		 * @var array
		 */
		private $variables = array();

		/**
		 * @var array
		 */
		private $email_notifications = array();

		/**
		 * @param string $key
		 *
		 * @return array
		 */
		public function get( $key ) {
			$method_name = 'init_' . $key;
			/**
			 * @use self::defaults
			 * @use self::custom_roles
			 * @use self::capabilities_map
			 * @use self::all_caps
			 * @use self::core_pages
			 * @use self::variables
			 * @use self::email_notifications
			 */
			if ( empty( $this->$key ) && method_exists( $this, $method_name ) ) {
				/**
				 * @use self::init_defaults
				 * @use self::init_custom_roles
				 * @use self::init_capabilities_map
				 * @use self::init_all_caps
				 * @use self::init_core_pages
				 * @use self::init_variables
				 * @use self::init_email_notifications
				 */
				$this->$method_name();
			}
			/**
			 * Filters the variable before getting it from the config.
			 *
			 * @since 2.1.0
			 * @hook fmwp_config_get
			 *
			 * @param {mixed}  $data The predefined data in config.
			 * @param {string} $key  The predefined data key. E.g. 'predefined_pages'.
			 *
			 * @return {mixed} Prepared config data.
			 */
			return apply_filters( 'fmwp_config_get', $this->$key, $key );
		}

		/**
		 * @return void
		 */
		private function init_defaults() {
			$defaults = array(
				'default_role'              => 'fmwp_participant',
				'login_redirect'            => '',
				'register_redirect'         => '',
				'logout_redirect'           => '',
				'forum_categories'          => true,
				'default_forum'             => '',
				'default_forums_order'      => 'date_desc',
				'default_forums_template'   => '',
				'topic_tags'                => true,
				'raw_html_enabled'          => false,
				'breadcrumb_enabled'        => false,
				'topic_throttle'            => 30,
				'show_forum'                => true,
				'default_topics_order'      => 'date_desc',
				'default_topics_template'   => '',
				'ajax_increment_views'      => false,
				'reply_throttle'            => 10,
				'reply_delete'              => 'sub_delete',
				'reply_user_role'           => false,
				'forum_slug'                => 'forum',
				'topic_slug'                => 'topic',
				'topic_tag_slug'            => 'topic-tag',
				'forum_category_slug'       => 'forum-category',
				'admin_email'               => get_bloginfo( 'admin_email' ),
				'mail_from'                 => get_bloginfo( 'name' ),
				'mail_from_addr'            => get_bloginfo( 'admin_email' ),
				'mention_on'                => true,
				'mention_sub'               => '{author_name} has mentioned you in {topic_title}',
				'disable-fa-styles'         => false,
				'uninstall-delete-settings' => false,
			);

			$this->defaults = apply_filters( 'fmwp_settings_defaults', $defaults );
		}

		/**
		 * @return void
		 */
		private function init_custom_roles() {
			$custom_roles = array(
				'fmwp_manager'     => __( 'Forum Manager', 'forumwp' ),
				'fmwp_moderator'   => __( 'Moderator', 'forumwp' ),
				'fmwp_participant' => __( 'Participant', 'forumwp' ),
				'fmwp_spectator'   => __( 'Spectator', 'forumwp' ),
			);

			$this->custom_roles = apply_filters( 'fmwp_custom_roles_list', $custom_roles );
		}

		/**
		 * @return void
		 */
		private function init_capabilities_map() {
			$capabilities_map = array(
				'administrator'    => array(
					'manage_fmwp_forums',
					'manage_fmwp_forums_all',
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',
					'fmwp_see_admin_menu',
					'fmwp_see_reports',
					'fmwp_remove_reports',

					'edit_fmwp_forums',
					'edit_others_fmwp_forums',
					'publish_fmwp_forums',
					'read_private_fmwp_forums',
					'delete_fmwp_forums',
					'delete_private_fmwp_forums',
					'delete_published_fmwp_forums',
					'delete_others_fmwp_forums',
					'edit_private_fmwp_forums',
					'edit_published_fmwp_forums',
					'create_fmwp_forums',

					'edit_fmwp_topics',
					'edit_others_fmwp_topics',
					'publish_fmwp_topics',
					'read_private_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'create_fmwp_topics',

					'edit_fmwp_replies',
					'edit_others_fmwp_replies',
					'publish_fmwp_replies',
					'read_private_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',

					'manage_fmwp_topic_tags',
					'edit_fmwp_topic_tags',
					'delete_fmwp_topic_tags',

					'manage_fmwp_forum_categories',
					'edit_fmwp_forum_categories',
					'delete_fmwp_forum_categories',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
					'fmwp_post_forum',

					'read',
				),
				'editor'           => array(
					'fmwp_see_admin_menu',

					'manage_fmwp_forums',
					'manage_fmwp_forums_all',
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',

					'create_fmwp_forums',
					'create_fmwp_topics',

					'publish_fmwp_forums',
					'edit_fmwp_forums',
					'edit_private_fmwp_forums',
					'edit_published_fmwp_forums',
					'edit_others_fmwp_forums',
					'delete_fmwp_forums',
					'delete_private_fmwp_forums',
					'delete_published_fmwp_forums',
					'delete_others_fmwp_forums',
					'read_private_fmwp_forums',

					'publish_fmwp_topics',
					'edit_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'edit_others_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'read_private_fmwp_topics',

					'publish_fmwp_replies',
					'edit_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',
					'edit_others_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'read_private_fmwp_replies',

					'manage_fmwp_topic_tags',
					'manage_fmwp_forum_categories',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',

					'read',
				),
				'author'           => array(
					'fmwp_see_admin_menu',

					'manage_fmwp_forums',
					'manage_fmwp_forums_own',
					'manage_fmwp_topics',
					'manage_fmwp_topics_own',
					'manage_fmwp_replies',
					'manage_fmwp_replies_own',

					'create_fmwp_forums',
					'create_fmwp_topics',

					'publish_fmwp_forums',
					'edit_fmwp_forums',
					'edit_published_fmwp_forums',
					'delete_fmwp_forums',
					'delete_published_fmwp_forums',

					'publish_fmwp_topics',
					'edit_fmwp_topics',
					'edit_published_fmwp_topics',
					'delete_fmwp_topics',
					'delete_published_fmwp_topics',

					'publish_fmwp_replies',
					'edit_fmwp_replies',
					'edit_published_fmwp_replies',
					'delete_fmwp_replies',
					'delete_published_fmwp_replies',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
					'read',
				),
				'contributor'      => array(
					'fmwp_see_admin_menu',

					'manage_fmwp_forums',
					'manage_fmwp_forums_own',
					'manage_fmwp_topics',
					'manage_fmwp_topics_own',
					'manage_fmwp_replies',
					'manage_fmwp_replies_own',

					'create_fmwp_forums',
					'create_fmwp_topics',

					'edit_fmwp_topics',
					'delete_fmwp_topics',
					'edit_fmwp_replies',
					'delete_fmwp_replies',
					'edit_fmwp_forums',
					'delete_fmwp_forums',
					'read',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
				),
				'subscriber'       => array(
					'read',

					'edit_fmwp_topics',
					'edit_fmwp_replies',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
				),

				'fmwp_manager'     => array(
					'manage_fmwp_forums',
					'manage_fmwp_forums_all',
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',
					'fmwp_see_admin_menu',
					'fmwp_see_reports',
					'fmwp_remove_reports',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
					'fmwp_post_forum',

					'edit_fmwp_forums',
					'edit_others_fmwp_forums',
					'publish_fmwp_forums',
					'read_private_fmwp_forums',
					'delete_fmwp_forums',
					'delete_private_fmwp_forums',
					'delete_published_fmwp_forums',
					'delete_others_fmwp_forums',
					'edit_private_fmwp_forums',
					'edit_published_fmwp_forums',
					'create_fmwp_forums',

					'edit_fmwp_topics',
					'edit_others_fmwp_topics',
					'publish_fmwp_topics',
					'read_private_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'create_fmwp_topics',

					'edit_fmwp_replies',
					'edit_others_fmwp_replies',
					//'create_fmwp_replies', lock the creation of replies via wp-admin
					'publish_fmwp_replies',
					'read_private_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',

					'manage_fmwp_topic_tags',
					'edit_fmwp_topic_tags',
					'delete_fmwp_topic_tags',

					'manage_fmwp_forum_categories',
					'edit_fmwp_forum_categories',
					'delete_fmwp_forum_categories',

					'edit_posts',
					'read',
				),
				'fmwp_moderator'   => array(
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',

					'fmwp_see_admin_menu',
					'fmwp_see_reports',
					'fmwp_remove_reports',

					'edit_fmwp_topics',
					'edit_others_fmwp_topics',
					'publish_fmwp_topics',
					'read_private_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'create_fmwp_topics',

					'edit_fmwp_replies',
					'edit_others_fmwp_replies',
					//'create_fmwp_replies', lock the creation of replies via wp-admin
					'publish_fmwp_replies',
					'read_private_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',

					'manage_fmwp_topic_tags',
					'edit_fmwp_topic_tags',
					'delete_fmwp_topic_tags',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',

					'edit_posts',
					'read',
				),
				'fmwp_participant' => array(
					'read',
					'edit_fmwp_topics',
					'edit_fmwp_replies',
					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
				),
				'fmwp_spectator'   => array(
					'read',
				),
			);

			$this->capabilities_map = apply_filters( 'fmwp_roles_capabilities_list', $capabilities_map );
		}

		/**
		 * @return void
		 */
		private function init_all_caps() {
			$all_caps = array(
				'manage_fmwp_forums',
				'manage_fmwp_forums_all',
				'manage_fmwp_forums_own',
				'manage_fmwp_topics',
				'manage_fmwp_topics_all',
				'manage_fmwp_topics_own',
				'manage_fmwp_replies',
				'manage_fmwp_replies_all',
				'manage_fmwp_replies_own',

				'fmwp_see_admin_menu',
				'fmwp_see_reports',
				'fmwp_remove_reports',

				'edit_fmwp_forums',
				'edit_others_fmwp_forums',
				'publish_fmwp_forums',
				'read_private_fmwp_forums',
				'delete_fmwp_forums',
				'delete_private_fmwp_forums',
				'delete_published_fmwp_forums',
				'delete_others_fmwp_forums',
				'edit_private_fmwp_forums',
				'edit_published_fmwp_forums',
				'create_fmwp_forums',

				'edit_fmwp_topics',
				'edit_others_fmwp_topics',
				'publish_fmwp_topics',
				'read_private_fmwp_topics',
				'delete_fmwp_topics',
				'delete_private_fmwp_topics',
				'delete_published_fmwp_topics',
				'delete_others_fmwp_topics',
				'edit_private_fmwp_topics',
				'edit_published_fmwp_topics',
				'create_fmwp_topics',

				'edit_fmwp_replies',
				'edit_others_fmwp_replies',
				'create_fmwp_replies',
				'publish_fmwp_replies',
				'read_private_fmwp_replies',
				'delete_fmwp_replies',
				'delete_private_fmwp_replies',
				'delete_published_fmwp_replies',
				'delete_others_fmwp_replies',
				'edit_private_fmwp_replies',
				'edit_published_fmwp_replies',

				'manage_fmwp_topic_tags',
				'edit_fmwp_topic_tags',
				'delete_fmwp_topic_tags',

				'manage_fmwp_forum_categories',
				'edit_fmwp_forum_categories',
				'delete_fmwp_forum_categories',

				'fmwp_post_reply',
				'fmwp_edit_own_reply',
				'fmwp_post_topic',
				'fmwp_edit_own_topic',
				'fmwp_post_forum',

				'read',
			);

			$this->all_caps = apply_filters( 'fmwp_all_caps_list', $all_caps );
		}

		/**
		 * @return void
		 */
		private function init_core_pages() {
			$core_pages = array(
				'login'    => array(
					'title' => __( 'Login', 'forumwp' ),
				),
				'register' => array(
					'title' => __( 'Registration', 'forumwp' ),
				),
				'profile'  => array(
					'title' => __( 'User Profile', 'forumwp' ),
				),
				'forums'   => array(
					'title' => __( 'Forums', 'forumwp' ),
				),
				'topics'   => array(
					'title' => __( 'Topics', 'forumwp' ),
				),
			);

			$this->core_pages = apply_filters( 'fmwp_core_pages', $core_pages );
		}

		/**
		 * @return void
		 */
		private function init_variables() {
			$variables = array(
				'forums_per_page'           => 20,
				'forum_categories_per_page' => 20,
				'topics_per_page'           => 20,
				'replies_per_page'          => 20,
			);

			$this->variables = apply_filters( 'fmwp_static_variables', $variables );
		}

		/**
		 * @return void
		 */
		private function init_email_notifications() {
			$email_notifications = array(
				'mention' => array(
					'key'            => 'mention',
					'title'          => __( 'Enable notification for mentions', 'forumwp' ),
					'description'    => __( 'Whether to send the user an email when he/she was mentioned', 'forumwp' ),
					'recipient'      => 'user',
					'default_active' => false,
				),
			);

			$this->email_notifications = apply_filters( 'fmwp_email_notifications', $email_notifications );
		}
	}
}
