<?php
/**
 * Template for the login popup
 *
 * This template can be overridden by copying it to your-theme/forumwp/login-popup.php
 *
 * @version 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fmwp-popup-overlay"></div>
<div id="fmwp-login-popup-wrapper" class="fmwp-popup fmwp">
	<div class="fmwp-popup-topbar">
		<div class="fmwp-popup-header" data-default="<?php esc_attr_e( 'Login', 'forumwp' ); ?>"></div>
		<span class="fmwp-popup-close fmwp-tip-n" title="<?php esc_attr_e( 'Close', 'forumwp' ); ?>">
			<i class="fas fa-times"></i>
		</span>
	</div>

	<?php echo wp_kses( apply_shortcodes( '[fmwp_login_form is_popup="1" /]' ), FMWP()->get_allowed_html( 'templates' ) ); ?>

	<span>
		<?php esc_html_e( 'Don\'t have an account?', 'forumwp' ); ?>
		<a href="<?php echo esc_attr( FMWP()->common()->get_preset_page_link( 'register' ) ); ?>">
			<?php esc_html_e( 'Sign up', 'forumwp' ); ?>
		</a>
	</span>

	<div class="clear"></div>
</div>
