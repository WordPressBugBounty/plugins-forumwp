<?php
/**
 * Template for the user card
 *
 * This template can be overridden by copying it to your-theme/forumwp/user_card.php
 *
 * @version 2.1.0
 *
 * @var object $fmwp_user_card
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="fmwp-user-card-wrapper">
	<div class="fmwp-user-card-avatar">
		<a href="<?php echo esc_url( FMWP()->user()->get_profile_link( $fmwp_user_card->ID ) ); ?>">
			<?php echo wp_kses( FMWP()->user()->get_avatar( $fmwp_user_card->ID, 'inline', 60 ), FMWP()->get_allowed_html( 'templates' ) ); ?>
		</a>
	</div>
	<div class="fmwp-user-card-content">
		<div class="fmwp-user-card-name">
			<a href="<?php echo esc_url( FMWP()->user()->get_profile_link( $fmwp_user_card->ID ) ); ?>" title="<?php esc_attr_e( 'User Profile', 'forumwp' ); ?>">
				<?php echo esc_html( FMWP()->user()->display_name( $fmwp_user_card ) ); ?>
			</a>
		</div>
		<?php if ( FMWP()->options()->get( 'reply_user_role' ) ) { ?>
			<div class="fmwp-user-card-role">
				<?php
				global $wp_roles;
				$user_roles = FMWP()->user()->get_roles( $fmwp_user_card );

				$tags = array();
				if ( ! empty( $user_roles ) ) {
					foreach ( $user_roles as $user_role ) {
						$name   = translate_user_role( $wp_roles->roles[ $user_role ]['name'] );
						$tags[] = array(
							'title' => $name,
						);
					}
				}
				if ( count( $tags ) ) {
					?>
					<span class="fmwp-user-card-tags">
						<?php foreach ( $tags as $user_tag ) { ?>
							<span class="fmwp-user-card-tag <?php echo ! empty( $user_tag['class'] ) ? esc_attr( $user_tag['class'] ) : ''; ?>">
								<?php echo esc_html( $user_tag['title'] ); ?>
							</span>
						<?php } ?>
					</span>
				<?php } ?>
			</div>
		<?php } ?>

		<div class="fmwp-user-card-description">
			<?php echo wp_kses_post( nl2br( $fmwp_user_card->description ) ); ?>
		</div>

		<span class="fmwp-user-card-stats">
			<span>
				<a href="<?php echo esc_url( FMWP()->user()->get_profile_link( $fmwp_user_card->ID, 'topics' ) ); ?>" title="<?php esc_attr_e( 'User Topics', 'forumwp' ); ?>">
					<?php
					$topics = FMWP()->user()->get_topics_count( $fmwp_user_card->ID );
					// translators: %s is a topics number
					echo esc_html( sprintf( _n( '%s topic', '%s topics', $topics, 'forumwp' ), $topics ) );
					?>
				</a>
			</span>
			<span>
				<a href="<?php echo esc_url( FMWP()->user()->get_profile_link( $fmwp_user_card->ID, 'replies' ) ); ?>" title="<?php esc_attr_e( 'User Replies', 'forumwp' ); ?>">
					<?php
					$replies = FMWP()->user()->get_replies_count( $fmwp_user_card->ID );
					// translators: %s is a replies number
					echo esc_html( sprintf( _n( '%s reply', '%s replies', $replies, 'forumwp' ), $replies ) );
					?>
				</a>
			</span>
		</span>
	</div>
</div>
