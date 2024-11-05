<?php
/**
 * Template for the forum row JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/forum-row.php
 *
 * @version 2.1.1
 *
 * @var array $fmwp_js_forum_row
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$item    = isset( $fmwp_js_forum_row['item'] ) ? $fmwp_js_forum_row['item'] : 'forum';
$actions = isset( $fmwp_js_forum_row['actions'] ) ? $fmwp_js_forum_row['actions'] : '';
?>

<div class="fmwp-forum-row<# if ( <?php echo esc_js( $item ); ?>.is_locked ) { #> fmwp-forum-locked<# } #><# if ( Object.keys( <?php echo esc_js( $item ); ?>.dropdown_actions ).length === 0 ) { #> fmwp-forum-no-actions<# } #>" data-forum_id="{{{<?php echo esc_js( $item ); ?>.forum_id}}}">
	<div class="fmwp-forum-row-lines">

		<div class="fmwp-forum-row-line fmwp-forum-primary-data">

			<# if ( <?php echo esc_js( $item ); ?>.thumbnail ) { #>
				<a href="{{{<?php echo esc_js( $item ); ?>.permalink}}}" title="{{{<?php echo esc_js( $item ); ?>.title}}}" class="fmwp-forum-avatar-link">
					<span class="fmwp-forum-avatar">{{{<?php echo esc_js( $item ); ?>.thumbnail}}}</span>
				</a>
			<# } else if ( <?php echo esc_js( $item ); ?>.icon ) { #>
				<a href="{{{<?php echo esc_js( $item ); ?>.permalink}}}" title="{{{<?php echo esc_js( $item ); ?>.title}}}" class="fmwp-forum-avatar-link">
					<span class="fmwp-forum-avatar fmwp-forum-icon" style="color: {{{<?php echo esc_js( $item ); ?>.icon_color}}}; background-color: {{{<?php echo esc_js( $item ); ?>.icon_bgcolor}}};">
						<i class="{{{<?php echo esc_js( $item ); ?>.icon}}}"></i>
					</span>
				</a>
			<# } #>

			<div class="fmwp-forum-data<# if ( ! <?php echo esc_js( $item ); ?>.thumbnail ) { #><# if ( ! <?php echo esc_js( $item ); ?>.icon ) { #> fmwp-forum-fullwidth-data<# } #><# } #>">

				<span class="fmwp-forum-first-line">
					<span class="fmwp-forum-title-line">
						<a href="{{{<?php echo esc_js( $item ); ?>.permalink}}}">
							<span class="fmwp-forum-status-marker fmwp-forum-locked-marker fmwp-tip-n" title="<?php esc_attr_e( 'Locked', 'forumwp' ); ?>">
								<i class="fas fa-lock"></i>
							</span>
							<span class="fmwp-forum-title">
								{{{<?php echo esc_js( $item ); ?>.title}}}
							</span>
						</a>
					</span>
					<span class="fmwp-forum-categories-wrapper">
						<?php if ( FMWP()->options()->get( 'forum_categories' ) ) { ?>
							<# if ( <?php echo esc_js( $item ); ?>.categories.length > 0 ) { #>
								<# _.each( <?php echo esc_js( $item ); ?>.categories, function( category, key, list ) { #>
									<span class="fmwp-forum-category"><a href="{{{category.href}}}">{{{category.name}}}</a></span>
								<# }); #>
							<# } #>
						<?php } ?>
					</span>
				</span>

				<div class="fmwp-forum-description">{{{<?php echo esc_js( $item ); ?>.strip_content}}}</div>

				<# if ( <?php echo esc_js( $item ); ?>.latest_topic ) { #>
					<div class="fmwp-forum-latest-topic">
						<strong><?php esc_html_e( 'Latest topic:', 'forumwp' ); ?></strong> <a href="{{{<?php echo esc_js( $item ); ?>.latest_topic_url}}}">{{{<?php echo esc_js( $item ); ?>.latest_topic}}}</a>
					</div>
				<# } #>
			</div>
		</div>

		<div class="fmwp-forum-row-line fmwp-forum-statistics-data<# if ( ! <?php echo esc_js( $item ); ?>.thumbnail ) { #><# if ( ! <?php echo esc_js( $item ); ?>.icon ) { #> fmwp-forum-fullwidth-data<# } #><# } #>">
			<div class="fmwp-forum-topics" title="{{{<?php echo esc_js( $item ); ?>.topics}}} <?php esc_attr_e( 'topics', 'forumwp' ); ?>">
				<span class="fmwp-responsive fmwp-ui-xs">{{{<?php echo esc_js( $item ); ?>.topics}}} <?php esc_attr_e( 'topics', 'forumwp' ); ?></span>
				<span class="fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">{{{<?php echo esc_js( $item ); ?>.topics}}}</span>
			</div>
			<div class="fmwp-forum-replies-count" title="{{{<?php echo esc_js( $item ); ?>.replies}}} <?php esc_attr_e( 'people have replied', 'forumwp' ); ?>">
				<span class="fmwp-responsive fmwp-ui-xs">{{{<?php echo esc_js( $item ); ?>.replies}}} <?php esc_attr_e( 'replies', 'forumwp' ); ?></span>
				<span class="fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">{{{<?php echo esc_js( $item ); ?>.replies}}}</span>
			</div>

			<div class="fmwp-forum-last-upgrade" title="<?php esc_attr_e( 'Last Updated', 'forumwp' ); ?>">
				{{{<?php echo esc_js( $item ); ?>.last_upgrade}}}
			</div>
		</div>
	</div>

	<div class="fmwp-forum-actions">
		<?php
		if ( is_user_logged_in() ) {
			if ( 'edit' === $actions ) {
				?>
				<# if ( Object.keys( <?php echo esc_js( $item ); ?>.dropdown_actions ).length > 0 ) { #>
					<div class="fmwp-forum-actions-dropdown" title="<?php esc_attr_e( 'More Actions', 'forumwp' ); ?>">
						<i class="fas fa-angle-down"></i>
						<div class="fmwp-dropdown" data-element=".fmwp-forum-actions-dropdown" data-trigger="click">
							<ul>
								<# _.each( <?php echo esc_js( $item ); ?>.dropdown_actions, function( actionData, key, list ) { #>
									<li><a href="#" class="{{{key}}}" data-entity_id="{{{actionData.entity_id}}}" data-nonce="{{{actionData.nonce}}}">{{{actionData.title}}}</a></li>
								<# }); #>
							</ul>
						</div>
					</div>
				<# } #>
				<?php
			}

			do_action( 'fmwp_forum_row_actions', $item, $actions );
		}
		?>
	</div>
</div>
