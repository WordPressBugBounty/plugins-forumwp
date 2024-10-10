<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab    = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( sanitize_key( $_GET['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
?>

<div id="fmwp-settings-wrap" class="wrap">
	<?php // translators: %s is a plugin name. ?>
	<h2><?php echo esc_html( sprintf( __( '%s - Settings', 'forumwp' ), FMWP_PLUGIN_NAME ) ); ?></h2>

	<?php
	echo wp_kses( FMWP()->admin()->settings()->tabs_menu() . FMWP()->admin()->settings()->subtabs_menu( $current_tab ), FMWP()->get_allowed_html( 'wp-admin' ) );

	/**
	 * Fires before displaying ForumWP settings $current_tab and $current_subtab content.
	 * Note: Internal hook that ForumWP uses for dynamic displaying content before the main settings tab/subtab content.
	 *
	 * @since 1.1.0
	 * @hook fmwp_before_settings_{$current_tab}_{$current_subtab}_content
	 */
	do_action( "fmwp_before_settings_{$current_tab}_{$current_subtab}_content" );

	$settings_section = FMWP()->admin()->settings()->display_section( $current_tab, $current_subtab );
	/**
	 * Filters the settings section content.
	 *
	 * @since 1.0
	 * @hook fmwp_settings_section_{$current_tab}_{$current_subtab}_content
	 *
	 * @param {string} $settings_section Setting section content.
	 *
	 * @return {string} Setting section content.
	 */
	$settings_section = apply_filters( "fmwp_settings_section_{$current_tab}_{$current_subtab}_content", $settings_section );

	if ( FMWP()->admin()->settings()->section_is_custom( $current_tab, $current_subtab ) ) {
		do_action( "fmwp_settings_page_{$current_tab}_{$current_subtab}_before_section" );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- early escape per handler above.
		echo $settings_section;
	} else {
		?>
		<form method="post" action="" name="fmwp-settings-form" id="fmwp-settings-form">
			<input type="hidden" value="save" name="fmwp-settings-action" />
			<?php
			do_action( "fmwp_settings_page_{$current_tab}_{$current_subtab}_before_section" );

			echo wp_kses( $settings_section, FMWP()->get_allowed_html( 'wp-admin' ) );
			?>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'forumwp' ); ?>" />
				<?php wp_nonce_field( 'fmwp-settings-nonce' ); ?>
			</p>
		</form>
	<?php } ?>
</div>
