<?php
/**
 * Template for the registration
 *
 * This template can be overridden by copying it to your-theme/forumwp/registration.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification
$login      = ! empty( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';
$email      = ! empty( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
$first_name = ! empty( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
$last_name  = ! empty( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification
?>

<div id="fmwp-register-form-wrapper" class="fmwp">

	<?php
	$registration = FMWP()->frontend()->forms(
		array(
			'id' => 'fmwp-register',
		)
	);

	$fields = array(
		array(
			'type'     => 'text',
			'label'    => __( 'Username', 'forumwp' ),
			'id'       => 'user_login',
			'required' => true,
			'value'    => $login,
		),
		array(
			'type'     => 'email',
			'label'    => __( 'Email', 'forumwp' ),
			'id'       => 'user_email',
			'required' => true,
			'value'    => $email,
		),
	);

	if ( 'hide' !== $fmwp_registration['first_name'] ) {
		$fields[] = array(
			'type'  => 'text',
			'label' => __( 'First Name', 'forumwp' ),
			'id'    => 'first_name',
			'value' => $first_name,
		);
	}

	if ( 'hide' !== $fmwp_registration['last_name'] ) {
		$fields[] = array(
			'type'  => 'text',
			'label' => __( 'Last Name', 'forumwp' ),
			'id'    => 'last_name',
			'value' => $last_name,
		);
	}

	$fields = array_merge(
		$fields,
		array(
			array(
				'type'     => 'password',
				'label'    => __( 'Password', 'forumwp' ),
				'id'       => 'user_pass',
				'required' => true,
			),
			array(
				'type'     => 'password',
				'label'    => __( 'Confirm Password', 'forumwp' ),
				'id'       => 'user_pass2',
				'required' => true,
			),
		)
	);

	$registration->set_data(
		array(
			'id'        => 'fmwp-register',
			'class'     => '',
			'prefix_id' => '',
			'fields'    => $fields,
			'hiddens'   => array(
				'fmwp-action' => 'registration',
				'redirect_to' => ! empty( $fmwp_registration['redirect'] ) ? $fmwp_registration['redirect'] : '',
				'nonce'       => wp_create_nonce( 'fmwp-registration' ),
			),
			'buttons'   => array(
				'signup' => array(
					'type'  => 'submit',
					'label' => __( 'Sign Up', 'forumwp' ),
				),
			),
		)
	);

	$registration->display();
	?>
</div>
