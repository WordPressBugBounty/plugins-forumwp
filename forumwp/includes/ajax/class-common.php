<?php
namespace fmwp\ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\ajax\Common' ) ) {

	/**
	 * Class Common
	 *
	 * @package fmwp\ajax
	 */
	class Common {

		/**
		 * Common constructor.
		 */
		public function __construct() {

			//wp-admin
			add_action( 'wp_ajax_fmwp_get_icons', array( $this->forms(), 'get_icons' ) );
			add_action( 'wp_ajax_fmwp_dismiss_notice', array( $this->notices(), 'dismiss_notice' ) );

			//front-end

			// default user profile actions
			add_action( 'wp_ajax_fmwp_profile_get_content', array( $this->profile(), 'get_tab_content' ) );
			add_action( 'wp_ajax_nopriv_fmwp_profile_get_content', array( $this->profile(), 'get_tab_content' ) );

			add_action( 'wp_ajax_fmwp_profile_topics', array( $this->profile(), 'get_profile_topics' ) );
			add_action( 'wp_ajax_nopriv_fmwp_profile_topics', array( $this->profile(), 'get_profile_topics' ) );
			add_action( 'wp_ajax_fmwp_profile_replies', array( $this->profile(), 'get_profile_replies' ) );
			add_action( 'wp_ajax_nopriv_fmwp_profile_replies', array( $this->profile(), 'get_profile_replies' ) );

			// forums list actions
			add_action( 'wp_ajax_fmwp_get_forums', array( $this->forum(), 'get_forums' ) );
			add_action( 'wp_ajax_nopriv_fmwp_get_forums', array( $this->forum(), 'get_forums' ) );
			add_action( 'wp_ajax_fmwp_lock_forum', array( $this->forum(), 'lock' ) );
			add_action( 'wp_ajax_fmwp_unlock_forum', array( $this->forum(), 'unlock' ) );
			add_action( 'wp_ajax_fmwp_trash_forum', array( $this->forum(), 'trash' ) );
			add_action( 'wp_ajax_fmwp_restore_forum', array( $this->forum(), 'restore' ) );
			add_action( 'wp_ajax_fmwp_remove_forum', array( $this->forum(), 'delete' ) );

			// topics list actions
			add_action( 'wp_ajax_fmwp_get_topics', array( $this->topic(), 'get_topics' ) );
			add_action( 'wp_ajax_nopriv_fmwp_get_topics', array( $this->topic(), 'get_topics' ) );

			add_action( 'wp_ajax_fmwp_create_topic', array( $this->topic(), 'create' ) );
			add_action( 'wp_ajax_fmwp_edit_topic', array( $this->topic(), 'edit' ) );

			add_action( 'wp_ajax_fmwp_get_topic', array( $this->topic(), 'get_topic' ) );
			add_action( 'wp_ajax_fmwp_pin_topic', array( $this->topic(), 'pin' ) );
			add_action( 'wp_ajax_fmwp_unpin_topic', array( $this->topic(), 'unpin' ) );
			add_action( 'wp_ajax_fmwp_lock_topic', array( $this->topic(), 'lock' ) );
			add_action( 'wp_ajax_fmwp_unlock_topic', array( $this->topic(), 'unlock' ) );
			add_action( 'wp_ajax_fmwp_trash_topic', array( $this->topic(), 'trash' ) );
			add_action( 'wp_ajax_fmwp_restore_topic', array( $this->topic(), 'restore' ) );
			add_action( 'wp_ajax_fmwp_delete_topic', array( $this->topic(), 'delete' ) );

			add_action( 'wp_ajax_fmwp_mark_spam_topic', array( $this->topic(), 'spam' ) );
			add_action( 'wp_ajax_fmwp_restore_spam_topic', array( $this->topic(), 'restore_spam' ) );
			add_action( 'wp_ajax_fmwp_report_topic', array( $this->topic(), 'report' ) );
			add_action( 'wp_ajax_fmwp_unreport_topic', array( $this->topic(), 'unreport' ) );
			add_action( 'wp_ajax_fmwp_clear_reports_topic', array( $this->topic(), 'clear_reports' ) );
			add_action( 'wp_ajax_fmwp_topic_build_preview', array( $this->topic(), 'build_preview' ) );

			### Function: Increment Topic Views
			if ( defined( 'WP_CACHE' ) && WP_CACHE && FMWP()->options()->get( 'ajax_increment_views' ) ) {
				add_action( 'wp_ajax_fmwp_topic_views', array( $this->topic(), 'increment_views' ) );
				add_action( 'wp_ajax_nopriv_fmwp_topic_views', array( $this->topic(), 'increment_views' ) );
			}

			// replies AJAX actions
			add_action( 'wp_ajax_fmwp_get_replies', array( $this->reply(), 'get_replies' ) );
			add_action( 'wp_ajax_nopriv_fmwp_get_replies', array( $this->reply(), 'get_replies' ) );
			add_action( 'wp_ajax_fmwp_get_child_replies', array( $this->reply(), 'get_child_replies' ) );
			add_action( 'wp_ajax_nopriv_fmwp_get_child_replies', array( $this->reply(), 'get_child_replies' ) );
			add_action( 'wp_ajax_fmwp_create_reply', array( $this->reply(), 'create' ) );
			add_action( 'wp_ajax_fmwp_get_reply', array( $this->reply(), 'get_reply' ) );
			add_action( 'wp_ajax_fmwp_edit_reply', array( $this->reply(), 'edit' ) );
			add_action( 'wp_ajax_fmwp_trash_reply', array( $this->reply(), 'trash' ) );
			add_action( 'wp_ajax_fmwp_restore_reply', array( $this->reply(), 'restore' ) );
			add_action( 'wp_ajax_fmwp_delete_reply', array( $this->reply(), 'delete' ) );
			add_action( 'wp_ajax_fmwp_mark_spam_reply', array( $this->reply(), 'spam' ) );
			add_action( 'wp_ajax_fmwp_restore_spam_reply', array( $this->reply(), 'restore_spam' ) );
			add_action( 'wp_ajax_fmwp_report_reply', array( $this->reply(), 'report' ) );
			add_action( 'wp_ajax_fmwp_unreport_reply', array( $this->reply(), 'unreport' ) );
			add_action( 'wp_ajax_fmwp_clear_reports_reply', array( $this->reply(), 'clear_reports' ) );
			add_action( 'wp_ajax_fmwp_reply_build_preview', array( $this->reply(), 'build_preview' ) );

			// user suggestions
			add_action( 'wp_ajax_fmwp_get_user_suggestions', array( $this->user(), 'get_suggestions' ) );

			// forum categories AJAX actions
			if ( FMWP()->options()->get( 'forum_categories' ) ) {
				add_action( 'wp_ajax_fmwp_get_forum_categories', array( $this->forum_category(), 'get_list' ) );
				add_action( 'wp_ajax_nopriv_fmwp_get_forum_categories', array( $this->forum_category(), 'get_list' ) );
			}
		}

		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \FMWP::includes()
		 */
		public function includes() {
			FMWP()->admin()->metabox();
			FMWP()->admin()->columns();

			FMWP()->admin()->upgrade()->init_packages_ajax_handlers();
		}

		/**
		 * Check nonce
		 *
		 * @param bool|string $action
		 *
		 * @since 1.0
		 */
		public function check_nonce( $action = false ) {
			$nonce  = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
			$action = empty( $action ) ? 'fmwp-common-nonce' : $action;

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_send_json_error( __( 'Wrong AJAX Nonce', 'forumwp' ) );
			}
		}

		/**
		 * @return Notices
		 *
		 * @since 1.0
		 */
		public function notices() {
			if ( empty( FMWP()->classes['fmwp\ajax\notices'] ) ) {
				FMWP()->classes['fmwp\ajax\notices'] = new Notices();
			}
			return FMWP()->classes['fmwp\ajax\notices'];
		}

		/**
		 * @return Forms
		 *
		 * @since 1.0
		 */
		public function forms() {
			if ( empty( FMWP()->classes['fmwp\ajax\forms'] ) ) {
				FMWP()->classes['fmwp\ajax\forms'] = new Forms();
			}
			return FMWP()->classes['fmwp\ajax\forms'];
		}

		/**
		 * @return Reply
		 *
		 * @since 1.0
		 */
		public function reply() {
			if ( empty( FMWP()->classes['fmwp\ajax\reply'] ) ) {
				FMWP()->classes['fmwp\ajax\reply'] = new Reply();
			}
			return FMWP()->classes['fmwp\ajax\reply'];
		}

		/**
		 * @return Topic
		 *
		 * @since 1.0
		 */
		public function topic() {
			if ( empty( FMWP()->classes['fmwp\ajax\topic'] ) ) {
				FMWP()->classes['fmwp\ajax\topic'] = new Topic();
			}
			return FMWP()->classes['fmwp\ajax\topic'];
		}

		/**
		 * @return Forum
		 *
		 * @since 1.0
		 */
		public function forum() {
			if ( empty( FMWP()->classes['fmwp\ajax\forum'] ) ) {
				FMWP()->classes['fmwp\ajax\forum'] = new Forum();
			}
			return FMWP()->classes['fmwp\ajax\forum'];
		}

		/**
		 * @return Profile
		 *
		 * @since 1.0
		 */
		public function profile() {
			if ( empty( FMWP()->classes['fmwp\ajax\profile'] ) ) {
				FMWP()->classes['fmwp\ajax\profile'] = new Profile();
			}
			return FMWP()->classes['fmwp\ajax\profile'];
		}

		/**
		 * @return User
		 *
		 * @since 1.0
		 */
		public function user() {
			if ( empty( FMWP()->classes['fmwp\ajax\user'] ) ) {
				FMWP()->classes['fmwp\ajax\user'] = new User();
			}
			return FMWP()->classes['fmwp\ajax\user'];
		}

		/**
		 * @return Forum_Category
		 *
		 * @since 1.0
		 */
		public function forum_category() {
			if ( empty( FMWP()->classes['fmwp\ajax\forum_category'] ) ) {
				FMWP()->classes['fmwp\ajax\forum_category'] = new Forum_Category();
			}
			return FMWP()->classes['fmwp\ajax\forum_category'];
		}
	}
}
