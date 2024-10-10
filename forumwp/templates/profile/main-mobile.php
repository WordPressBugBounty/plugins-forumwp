<?php
/**
 * Template for the main mobile
 *
 * This template can be overridden by copying it to your-theme/forumwp/profile/main-mobile.php
 *
 * @version 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$username = get_query_var( 'fmwp_user' );
if ( empty( $username ) ) {
	$user_id = get_current_user_id();
} else {
	$user_id = FMWP()->user()->get_user_by_permalink( $username );
}

$user = get_user_by( 'ID', $user_id );

if ( empty( $user ) ) {
	return '';
}

$active_tab = get_query_var( 'fmwp_profiletab' );
$active_tab = empty( $active_tab ) ? 'topics' : $active_tab;

$menu_items = FMWP()->frontend()->profile()->get_profile_tabs( $user );
?>
<div class="fmwp-profile-mobile fmwp-responsive fmwp-ui-xs">

	<div class="fmwp-profile-general-info">

		<div class="fmwp-profile-info-line">
			<div class="fmwp-profile-avatar">
				<a href="<?php echo esc_url( FMWP()->user()->get_profile_link( $user->ID ) ); ?>">
					<?php echo wp_kses( FMWP()->user()->get_avatar( $user->ID, 'inline', 300 ), FMWP()->get_allowed_html( 'templates' ) ); ?>
				</a>
			</div>
			<div class="fmwp-profile-info-wrapper">
					<span class="fmwp-profile-info-subline">
						<span class="fmwp-profile-username"><?php echo esc_html( FMWP()->user()->display_name( $user ) ); ?></span>
						<span class="fmwp-profile-user-stats fmwp-profile-user-top-info">
							<span>
								<?php
								$topics = FMWP()->user()->get_topics_count( $user->ID );
								// translators: %s is a topics number
								echo esc_html( sprintf( _n( '%s topic', '%s topics', $topics, 'forumwp' ), $topics ) );
								?>
							</span>
							<span>
								<?php
								$replies = FMWP()->user()->get_replies_count( $user->ID );
								// translators: %s is a replies number
								echo esc_html( sprintf( _n( '%s reply', '%s replies', $replies, 'forumwp' ), $replies ) );
								?>
							</span>
						</span>
					</span>
					<span class="fmwp-profile-info-subline fmwp-profile-user-top-info">
						<?php
						// translators: %s is a user registration date
						echo esc_html( sprintf( __( 'Joined: %s', 'forumwp' ), date_i18n( FMWP()->datetime_format( 'date' ), strtotime( $user->user_registered ) ) ) );
						?>
					</span>
			</div>
		</div>

		<?php if ( ! empty( $user->user_url ) ) { ?>
			<div class="fmwp-profile-info-line fmwp-profile-user-top-info">
				<?php esc_html_e( 'Website:', 'forumwp' ); ?>&nbsp;<a href="<?php echo esc_url( $user->user_url ); ?>"><?php echo esc_html( $user->user_url ); ?></a>
			</div>
		<?php } ?>

		<?php if ( ! empty( $user->description ) ) { ?>
			<div class="fmwp-profile-info-line fmwp-profile-user-top-info">
				<?php echo wp_kses_post( nl2br( $user->description ) ); ?>
			</div>
		<?php } ?>
	</div>

	<nav>
		<ul class="fmwp-profile-menu">
			<?php
			foreach ( $menu_items as $menu_tab => $item ) {
				$tab_classes = array();
				if ( $menu_tab === $active_tab ) {
					$tab_classes[] = 'fmwp-active-tab';
				}
				?>
				<li class="<?php echo esc_attr( implode( ' ', $tab_classes ) ); ?>">
					<a href="<?php echo esc_attr( $item['link'] ); ?>" class="fmwp-profile-mobile-tab-link" data-tab="<?php echo esc_attr( $menu_tab ); ?>" data-ajax="<?php echo (int) $item['ajax']; ?>" title="<?php echo esc_attr( $item['title'] ); ?>">
						<?php echo esc_html( $item['title'] ); ?>
					</a>
				</li>
				<?php
			}
			?>
			<li class="fmwp-profile-menu-indicator"></li>
		</ul>
	</nav>

	<div class="fmwp-profile-scroll-content">
		<?php
		foreach ( $menu_items as $menu_tab => $item ) {

			$module = isset( $item['module'] ) ? $item['module'] : '';

			$active_subtab = false;
			$submenu_items = FMWP()->frontend()->profile()->get_profile_subtabs( $user, $menu_tab );
			if ( ! empty( $submenu_items ) ) {
				if ( $menu_tab === $active_tab ) {
					$slug_array    = array_keys( $submenu_items );
					$active_subtab = get_query_var( 'fmwp_profilesubtab' );
					$active_subtab = empty( $active_subtab ) ? $slug_array[0] : $active_subtab;
				}
			}
			?>
			<?php //phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace -- inline add classes, rewrite for future templates ?>
			<div class="fmwp-profile-tab-content fmwp-profile-<?php echo esc_attr( $menu_tab ); ?>-content <?php echo ( $item['ajax'] && $menu_tab !== $active_tab ) ? 'fmwp-profile-blank-content' : ''; ?>" data-tab="<?php echo esc_attr( $menu_tab ); ?>" data-ajax="<?php echo (int) $item['ajax']; ?>" <?php if ( ! empty( $active_subtab ) ) { ?>data-active_subtab="<?php echo esc_attr( $active_subtab ); ?>"<?php } ?>>
				<?php if ( ! empty( $submenu_items ) ) { ?>
					<ul class="fmwp-profile-submenu">
						<?php
						foreach ( $submenu_items as $subtab => $sub_item ) {
							$subtab_classes = array();
							if ( $menu_tab === $active_tab ) {
								$subtab_classes[] = 'fmwp-active-tab';
							}
							?>
							<li class="<?php echo esc_attr( implode( ' ', $subtab_classes ) ); ?>">
								<a href="<?php echo esc_attr( $sub_item['link'] ); ?>" class="fmwp-profile-load-content-link fmwp-profile-subtab-link" data-tab="<?php echo esc_attr( $subtab ); ?>" title="<?php echo esc_attr( $sub_item['title'] ); ?>">
									<?php echo esc_html( $sub_item['title'] ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>

					<?php foreach ( $submenu_items as $subtab => $sub_item ) { ?>

						<div class="fmwp-profile-subtab-content fmwp-profile-<?php echo esc_attr( $subtab ); ?>-<?php echo esc_attr( $menu_tab ); ?>-wrapper">

							<?php
							FMWP()->get_template_part( 'profile/' . $menu_tab . '/' . $subtab, array(), $module );

							FMWP()->ajax_loader( 50 );
							?>

						</div>

						<?php
					}
				} else {
					FMWP()->get_template_part( 'profile/' . $menu_tab, array(), $module );
				}

				if ( empty( $submenu_items ) ) {
					FMWP()->ajax_loader( 50 );
				}
				?>
			</div>
		<?php } ?>
	</div>
</div>