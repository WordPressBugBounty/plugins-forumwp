<?php
namespace fmwp\frontend;

use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\frontend\Profile' ) ) {

	/**
	 * Class Profile
	 *
	 * @package fmwp\frontend
	 */
	class Profile {

		/**
		 * @param WP_User $user
		 *
		 * @return array
		 */
		public function get_edit_tab_data( $user ) {
			$data = array(
				'id'          => $user->ID,
				'login'       => $user->user_login,
				'email'       => $user->user_email,
				'first_name'  => $user->first_name,
				'last_name'   => $user->last_name,
				'url'         => $user->user_url,
				'description' => $user->description,
			);

			return $data;
		}

		public function get_notifications_tab_data( $user ) {
			$globally_enabled_emails = FMWP()->common()->mail()->get_public_emails();

			$data = array(
				'id' => $user->ID,
			);

			foreach ( $globally_enabled_emails as $email_key => $enabled_email ) {
				$field_key          = 'enabled_' . $email_key . '_notification';
				$usermeta           = get_user_meta( $user->ID, 'fmwp_' . $field_key, true );
				$data[ $field_key ] = ! empty( $usermeta ) ? 'checked' : '';
			}

			return $data;
		}

		/**
		 * Profile Tabs
		 *
		 * @return array
		 */
		public function tabs_list() {
			$tabs = array(
				'topics'        => array(
					'title' => __( 'Topics', 'forumwp' ),
					'ajax'  => false,
				),
				'replies'       => array(
					'title' => __( 'Replies', 'forumwp' ),
					'ajax'  => false,
				),
				'notifications' => array(
					'title' => __( 'Email Notifications', 'forumwp' ),
					'ajax'  => true,
				),
				'edit'          => array(
					'title' => __( 'Edit Profile', 'forumwp' ),
					'ajax'  => true,
				),
			);

			$globally_enabled_emails = FMWP()->common()->mail()->get_public_emails();
			if ( empty( $globally_enabled_emails ) ) {
				unset( $tabs['notifications'] );
			}

			return apply_filters( 'fmwp_profile_tabs', $tabs );
		}

		/**
		 * Profile Tabs
		 *
		 * @return array
		 */
		public function subtabs_list() {
			return apply_filters( 'fmwp_profile_subtabs', array() );
		}

		/**
		 * @param string $slug
		 * @param WP_User $user
		 *
		 * @return bool
		 */
		public function tab_visibility( $slug, $user ) {
			$visible = true;

			if ( in_array( $slug, array( 'edit', 'notifications' ), true ) ) {
				if ( ! is_user_logged_in() || absint( $user->ID ) !== get_current_user_id() ) {
					$visible = false;
				}
			} elseif ( 'replies' === $slug ) {
				if ( ! user_can( $user->ID, 'fmwp_post_reply' ) ) {
					$visible = false;
				}
			} elseif ( 'topics' === $slug ) {
				if ( ! user_can( $user->ID, 'fmwp_post_topic' ) ) {
					$visible = false;
				}
			}

			return apply_filters( 'fmwp_profile_tab_visible', $visible, $slug, $user );
		}

		/**
		 * @param string $slug
		 * @param string $tab
		 * @param WP_User $user
		 *
		 * @return bool
		 */
		public function subtab_visibility( $slug, $tab, $user ) {
			return apply_filters( 'fmwp_profile_subtab_visible', true, $slug, $tab, $user );
		}

		/**
		 * Get Profile Tabs for User
		 *
		 * @param WP_User $user
		 *
		 * @return array
		 */
		public function get_profile_tabs( $user ) {
			$menu_items = array();

			$tabs = $this->tabs_list();
			foreach ( $tabs as $slug => $data ) {

				$visible = $this->tab_visibility( $slug, $user );
				if ( ! $visible ) {
					continue;
				}

				$menu_items[ $slug ] = array(
					'title' => $data['title'],
					'link'  => FMWP()->user()->get_profile_link( $user->ID, $slug ),
					'ajax'  => $data['ajax'],
				);

				if ( ! empty( $data['module'] ) ) {
					$menu_items[ $slug ]['module'] = $data['module'];
				}
			}

			return $menu_items;
		}

		/**
		 * Get Profile Tabs for User
		 *
		 * @param WP_User $user
		 * @param string $tab Profile Tab's slug
		 *
		 * @return array
		 */
		public function get_profile_subtabs( $user, $tab ) {
			$menu_items = array();

			$subtabs = $this->subtabs_list();
			if ( empty( $subtabs[ $tab ] ) ) {
				return $menu_items;
			}

			foreach ( $subtabs[ $tab ] as $slug => $title ) {
				$visible = $this->subtab_visibility( $slug, $tab, $user );
				if ( ! $visible ) {
					continue;
				}

				$menu_items[ $slug ] = array(
					'title' => $title,
					'link'  => FMWP()->user()->get_profile_link( $user->ID, $tab, $slug ),
				);
			}

			return $menu_items;
		}
	}
}
