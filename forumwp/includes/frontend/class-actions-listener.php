<?php
namespace fmwp\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\frontend\Actions_Listener' ) ) {

	/**
	 * Class Actions_Listener
	 *
	 * @package fmwp\frontend
	 */
	class Actions_Listener {

		/**
		 * Actions_Listener constructor.
		 */
		public function __construct() {
			//filter edit/show profile handler
			add_action( 'init', array( &$this, 'submit_form_handler' ) );
		}

		/**
		 * Password strength test
		 *
		 * @param string $candidate
		 *
		 * @return bool
		 */
		public function strong_pass( $candidate ) {
			if ( strlen( $candidate ) < 8 ) {
				return false;
			}

			// are used Unicode Regular Expressions
			$regexps = array(
				'/[\p{Lu}]/u', // any Letter Uppercase symbol
				'/[\p{Ll}]/u', // any Letter Lowercase symbol
				'/[\p{N}]/u', // any Number symbol
			);
			foreach ( $regexps as $regexp ) {
				if ( preg_match_all( $regexp, $candidate, $o ) < 1 ) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Handler $_POST forms to avoid headers already sent after wp_redirect function
		 *
		 * @since 2.0
		 */
		public function submit_form_handler() {
			if ( empty( $_POST['fmwp-action'] ) ) {
				return;
			}

			$action = sanitize_key( $_POST['fmwp-action'] );
			if ( empty( $action ) ) {
				return;
			}

			switch ( $action ) {
				case 'registration':
					global $registration;

					$registration = FMWP()->frontend()->forms( array( 'id' => 'fmwp-register' ) );

					$registration->flush_errors();

					if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fmwp-registration' ) ) {
						$registration->add_error( 'global', __( 'Security issue, Please try again', 'forumwp' ) );
					}

					if ( empty( $_POST['user_login'] ) ) {
						$registration->add_error( 'user_login', __( 'Login is empty', 'forumwp' ) );
					}
					$user_login = sanitize_user( wp_unslash( $_POST['user_login'] ) );
					if ( username_exists( $user_login ) ) {
						$registration->add_error( 'user_login', __( 'A user with this username already exists', 'forumwp' ) );
					}

					if ( empty( $_POST['user_email'] ) ) {
						$registration->add_error( 'user_email', __( 'Email is empty', 'forumwp' ) );
					}
					$user_email = sanitize_email( wp_unslash( $_POST['user_email'] ) );
					if ( ! is_email( $user_email ) ) {
						$registration->add_error( 'user_email', __( 'Email is invalid', 'forumwp' ) );
					}
					if ( email_exists( $user_email ) ) {
						$registration->add_error( 'user_email', __( 'A user with this email already exists', 'forumwp' ) );
					}

					if ( empty( $_POST['user_pass'] ) ) {
						$registration->add_error( 'user_pass', __( 'Password cannot be an empty', 'forumwp' ) );
					}
					if ( empty( $_POST['user_pass2'] ) ) {
						$registration->add_error( 'user_pass2', __( 'Please confirm the password', 'forumwp' ) );
					}
					$user_pass  = trim( $_POST['user_pass'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- don't sanitize pass
					$user_pass2 = trim( $_POST['user_pass2'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- don't sanitize pass

					if ( ! $this->strong_pass( $user_pass ) ) {
						$registration->add_error( 'user_pass', __( 'Your password must contain at least one lowercase letter, one capital letter and one number and be at least 8 characters', 'forumwp' ) );
					}

					if ( $user_pass !== $user_pass2 ) {
						$registration->add_error( 'user_pass2', __( 'Sorry, passwords do not match!', 'forumwp' ) );
					}

					do_action( 'fmwp_before_submit_registration', $registration );

					if ( ! $registration->has_errors() ) {
						$userdata = array(
							'user_login' => $user_login,
							'user_pass'  => $user_pass,
							'user_email' => $user_email,
							'first_name' => ! empty( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
							'last_name'  => ! empty( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
							'role'       => FMWP()->options()->get( 'default_role' ),
						);
						$userdata = apply_filters( 'fmwp_onsubmit_registration_args', $userdata, $registration );

						$user_id = wp_insert_user( $userdata );

						do_action( 'fmwp_user_register', $user_id );

						// auto-login after registration
						$user = wp_signon(
							array(
								'user_login'    => $user_login,
								'user_password' => $user_pass,
							)
						);

						if ( is_wp_error( $user ) ) {
							$redirect = FMWP()->common()->get_preset_page_link( 'register' );
						} else {
							//redirect to profile page
							$redirect = FMWP()->user()->get_profile_link( $user->ID );
						}

						if ( ! empty( $_POST['redirect_to'] ) ) {
							$redirect = esc_url_raw( wp_unslash( $_POST['redirect_to'] ) );
						}

						add_filter(
							'wp_safe_redirect_fallback',
							static function () {
								return FMWP()->common()->get_preset_page_link( 'profile' );
							}
						);

						wp_safe_redirect( $redirect );
						exit;
					}
					break;

				case 'edit-profile':
					global $edit_profile;

					$edit_profile = FMWP()->frontend()->forms( array( 'id' => 'fmwp-edit-profile' ) );

					$edit_profile->flush_errors();
					$edit_profile->flush_notices();

					if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fmwp-edit-profile' ) ) {
						$edit_profile->add_error( 'global', __( 'Security issue, Please try again', 'forumwp' ) );
					}

					if ( ! is_user_logged_in() ) {
						$edit_profile->add_error( 'global', __( 'Empty User', 'forumwp' ) );
					}

					$user_id       = get_current_user_id();
					$previous_data = get_userdata( $user_id );

					if ( empty( $_POST['user_email'] ) ) {
						$edit_profile->add_error( 'user_email', __( 'Empty Email', 'forumwp' ) );
					}
					$user_email = sanitize_email( wp_unslash( $_POST['user_email'] ) );
					if ( ! is_email( $user_email ) ) {
						$edit_profile->add_error( 'user_email', __( 'Invalid email', 'forumwp' ) );
					}
					if ( $previous_data->user_email !== $user_email && email_exists( $user_email ) ) {
						$edit_profile->add_error( 'user_email', __( 'Email already exists', 'forumwp' ) );
					}

					$filter_url = false;
					if ( ! empty( $_POST['user_url'] ) ) {
						$filter_url = filter_var( wp_unslash( $_POST['user_url'] ), FILTER_VALIDATE_URL );
					}

					if ( ! empty( $_POST['user_url'] ) && false === $filter_url ) {
						$edit_profile->add_error( 'user_url', __( 'Invalid user URL', 'forumwp' ) );
					}

					do_action( 'fmwp_before_submit_profile', $edit_profile );

					if ( ! $edit_profile->has_errors() ) {
						$first_name   = ! empty( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
						$last_name    = ! empty( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
						$display_name = $first_name . ' ' . $last_name;
						$display_name = empty( $display_name ) ? $previous_data->user_login : $display_name;

						$userdata = array(
							'ID'           => $user_id,
							'user_email'   => $user_email,
							'first_name'   => $first_name,
							'last_name'    => $last_name,
							'user_url'     => $filter_url ? $filter_url : '',
							'display_name' => $display_name,
							'description'  => ! empty( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
						);

						$userdata = apply_filters( 'fmwp_onsubmit_profile_args', $userdata, $edit_profile );

						wp_update_user( $userdata );

						do_action( 'fmwp_user_update_profile', $user_id );

						//redirect to profile page
						$profile_link = FMWP()->user()->get_profile_link( get_current_user_id(), 'edit' );

						wp_safe_redirect( $profile_link );
						exit;
					}

					break;

				case 'profile-notifications':
					global $notifications_profile;

					$notifications_profile = FMWP()->frontend()->forms( array( 'id' => 'fmwp-notifications-profile' ) );

					$notifications_profile->flush_errors();
					$notifications_profile->flush_notices();

					if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fmwp-profile-notifications' ) ) {
						$notifications_profile->add_error( 'global', __( 'Security issue, Please try again', 'forumwp' ) );
					}

					if ( empty( $_POST['user_id'] ) ) {
						$notifications_profile->add_error( 'global', __( 'Empty User', 'forumwp' ) );
					}

					$user_id = get_current_user_id();

					if ( ! $notifications_profile->has_errors() ) {
						$globally_enabled_emails = FMWP()->common()->mail()->get_public_emails();

						foreach ( $globally_enabled_emails as $email_key => $email_data ) {
							$field_key = 'enabled_' . $email_key . '_notification';

							if ( ! empty( $_POST[ $field_key ] ) ) {
								update_user_meta( $user_id, 'fmwp_' . $field_key, true );
							} else {
								update_user_meta( $user_id, 'fmwp_' . $field_key, false );
							}
						}

						//redirect to profile page
						$profile_link = FMWP()->user()->get_profile_link( get_current_user_id(), 'notifications' );

						wp_safe_redirect( $profile_link );
						exit;
					}
					break;

				case 'create-forum':
					global $new_forum;

					$new_forum = FMWP()->frontend()->forms( array( 'id' => 'fmwp-create-forum' ) );

					$new_forum->flush_errors();
					$new_forum->flush_notices();

					if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fmwp-create-forum' ) ) {
						$new_forum->add_error( 'global', __( 'Security issue, Please try again', 'forumwp' ) );
					}

					if ( ! FMWP()->user()->can_create_forum() ) {
						$new_forum->add_error( 'global', __( 'You do not have capability to create forums', 'forumwp' ) );
					}

					if ( empty( $_POST['fmwp-forum'] ) ) {
						$new_forum->add_error( 'global', __( 'Invalid data', 'forumwp' ) );
					}

					$request = $_POST['fmwp-forum']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below in sanitize.

					if ( empty( $request['title'] ) ) {
						$new_forum->add_error( 'title', __( 'Name is required', 'forumwp' ) );
					}

					if ( empty( $request['content'] ) ) {
						$new_forum->add_error( 'content', __( 'Description is required', 'forumwp' ) );
					}

					if ( ! array_key_exists( $request['visibility'], FMWP()->common()->forum()->visibilities ) ) {
						$new_forum->add_error( 'visibility', __( 'Invalid visibility', 'forumwp' ) );
					}

					do_action( 'fmwp_before_submit_create_forum', $new_forum );

					if ( ! $new_forum->has_errors() ) {
						$data = array(
							'title'      => sanitize_text_field( wp_unslash( $request['title'] ) ),
							'content'    => wp_kses_post( wp_unslash( $request['content'] ) ),
							'categories' => sanitize_text_field( wp_unslash( $request['categories'] ) ),
							'visibility' => sanitize_key( wp_unslash( $request['visibility'] ) ),
						);

						$data = apply_filters( 'fmwp_onsubmit_create_forum_args', $data, $new_forum );

						if ( ! FMWP()->options()->get( 'forum_categories' ) ) {
							unset( $data['categories'] );
						}

						FMWP()->common()->forum()->create( $data );

						wp_safe_redirect( add_query_arg( array( 'fmwp-msg' => 'forum-created' ), FMWP()->get_current_url() ) );
						exit;
					}
					break;

			}
		}
	}
}
