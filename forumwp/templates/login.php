<?php
/**
 * Template for the login
 *
 * This template can be overridden by copying it to your-theme/forumwp/login.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$redirect = ! empty( $fmwp_login['redirect'] ) ? $fmwp_login['redirect'] : '';
?>

<div id="fmwp-login-form-wrapper" class="fmwp">

	<?php if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) { // phpcs:ignore WordPress.Security.NonceVerification ?>
		<span id="fmwp-login-form-notice">
			<?php esc_html_e( 'Invalid username, email address or incorrect password.', 'forumwp' ); ?>
		</span>
		<?php
	}

	$login_args = array(
		'echo'           => false,
		'remember'       => true,
		'redirect'       => $redirect,
		'form_id'        => 'fmwp-loginform',
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'label_username' => __( 'Username or Email Address', 'forumwp' ),
		'label_password' => __( 'Password', 'forumwp' ),
		'label_remember' => __( 'Remember Me', 'forumwp' ),
		'label_log_in'   => __( 'Log In', 'forumwp' ),
		'value_username' => '',
		'value_remember' => false,
	);

	echo wp_login_form( $login_args );
	?>

	<span id="fmwp-rp-link">
		<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>" title="<?php esc_attr_e( 'Forgot Password?', 'forumwp' ); ?>">
			<?php esc_html_e( 'Forgot Password?', 'forumwp' ); ?>
		</a>
	</span>
	<div class="clear"></div>
</div>
