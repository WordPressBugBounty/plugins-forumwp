<?php
/**
 * Template for the reply row JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/reply-row.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_js_reply_row
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topic_id    = isset( $fmwp_js_reply_row['topic_id'] ) ? $fmwp_js_reply_row['topic_id'] : false;
$item        = isset( $fmwp_js_reply_row['item'] ) ? $fmwp_js_reply_row['item'] : 'reply';
$actions     = isset( $fmwp_js_reply_row['actions'] ) ? $fmwp_js_reply_row['actions'] : '';
$show_footer = isset( $fmwp_js_reply_row['show_footer'] ) ? $fmwp_js_reply_row['show_footer'] : true;
$show_title  = isset( $fmwp_js_reply_row['show_title'] ) ? $fmwp_js_reply_row['show_title'] : false;
?>

<div id="fmwp-reply-{{{<?php echo esc_js( $item ); ?>.reply_id}}}" class="fmwp-reply-row<# if ( data.sub_template ) { #> fmwp-child-reply<# } #><# if ( <?php echo esc_js( $item ); ?>.is_spam ) { #> fmwp-reply-spam<# } #><# if ( <?php echo esc_js( $item ); ?>.is_trashed ) { #> fmwp-reply-trashed<# } #><# if ( <?php echo esc_js( $item ); ?>.is_reported ) { #> fmwp-reply-reported<# } #><# if ( <?php echo esc_js( $item ); ?>.is_pending ) { #> fmwp-reply-pending<# } #><?php do_action( 'fmwp_js_template_reply_row_classes', $item ); ?>"
	data-reply_id="{{{<?php echo esc_js( $item ); ?>.reply_id}}}"
	data-editable="<# if ( <?php echo esc_js( $item ); ?>.can_edit ) { #>1<# } #>"
	data-is_author="<# if ( <?php echo esc_js( $item ); ?>.is_author ) { #>1<# } #>"
	data-trashed="<# if ( <?php echo esc_js( $item ); ?>.is_trashed ) { #>1<# } #>">

	<# if ( data.sub_template ) { #>
		<# if ( ! <?php echo esc_js( $item ); ?>.is_subsub ) { #>
			<div class="fmwp-reply-child-connect"></div>
		<# } #>
	<# } #>

	<div class="fmwp-reply-base">
		<div class="fmwp-reply-avatar">
			<a href="{{{<?php echo esc_js( $item ); ?>.author_url}}}" title="{{{<?php echo esc_js( $item ); ?>.author}}} <?php esc_attr_e( 'Profile', 'forumwp' ); ?>" data-fmwp_tooltip="{{<?php echo esc_js( $item ); ?>.author_card}}" data-fmwp_tooltip_id="fmwp-user-card-tooltip">
				{{{<?php echo esc_js( $item ); ?>.author_avatar}}}
			</a>
		</div>
		<div class="fmwp-reply-data">
			<div class="fmwp-reply-top">
				<span class="fmwp-reply-head">
					<span class="fmwp-reply-head-data">
						<?php if ( ! $show_title ) { ?>
							<a href="{{{<?php echo esc_js( $item ); ?>.author_url}}}" title="{{{<?php echo esc_js( $item ); ?>.author}}} <?php esc_attr_e( 'Profile', 'forumwp' ); ?>">
								{{{<?php echo esc_js( $item ); ?>.author}}}
							</a>

							<# if ( <?php echo esc_js( $item ); ?>.author_tags.length ) { #>
								<span class="fmwp-reply-author-tags-wrapper fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
									<# _.each( <?php echo esc_js( $item ); ?>.author_tags, function( tag, key, list ) { #>
										<span class="fmwp-reply-tag <# if ( typeof tag.class !== 'undefined' ) { #>{{{tag.class}}}<# } #>">
											{{{tag.title}}}
										</span>
									<# }); #>
								</span>
							<# } #>
						<?php } else { ?>
							<a href="{{{<?php echo esc_js( $item ); ?>.permalink}}}" title="<?php esc_attr_e( 'Show in topic', 'forumwp' ); ?>" class="fmwp-reply-title">
								{{{<?php echo esc_js( $item ); ?>.title}}}
							</a>
						<?php } ?>

						<?php FMWP()->get_template_part( 'reply-status-tags' ); ?>
					</span>
					<span class="fmwp-reply-subdata fmwp-reply-date">
						<?php if ( ! $show_title ) { ?>
							<a href="{{{<?php echo esc_js( $item ); ?>.permalink}}}" title="<?php esc_attr_e( 'Reply link', 'forumwp' ); ?>">
								{{{<?php echo esc_js( $item ); ?>.beauty_date}}}
							</a>
						<?php } else { ?>
							{{{<?php echo esc_js( $item ); ?>.beauty_date}}}
						<?php } ?>
					</span>
				</span>
				<span class="fmwp-reply-top-actions">
					<?php
					if ( is_user_logged_in() ) {
						if ( 'edit' === $actions ) {
							?>
							<# if ( Object.keys( <?php echo esc_js( $item ); ?>.dropdown_actions ).length > 0 ) { #>
								<div class="fmwp-reply-top-actions-dropdown" title="<?php esc_attr_e( 'More Actions', 'forumwp' ); ?>">
									<i class="fas fa-angle-down"></i>
									<div class="fmwp-dropdown" data-element=".fmwp-reply-top-actions-dropdown" data-trigger="click">
										<ul>
											<# _.each( <?php echo esc_js( $item ); ?>.dropdown_actions, function( title, key, list ) { #>
												<li><a href="#" class="{{{key}}}">{{{title}}}</a></li>
											<# }); #>
										</ul>
									</div>
								</div>
							<# } #>
							<?php
						}

						do_action( 'fmwp_reply_row_actions', $item, $actions );
					}
					?>
				</span>
			</div>
			<div class="fmwp-reply-content">
				{{{<?php echo esc_js( $item ); ?>.content}}}
			</div>
			<?php if ( $show_footer ) { ?>
				<div class="fmwp-reply-bottom">
					<div class="fmwp-reply-left-panel">
						<?php FMWP()->get_template_part( 'js/reply-row-answers', $fmwp_js_reply_row ); ?>
					</div>
					<div class="fmwp-reply-right-panel">
						<?php
						if ( is_user_logged_in() ) {
							if ( ! empty( $topic_id ) && FMWP()->user()->can_reply( $topic_id ) ) {
								?>
								<# if ( ! data.sub_template || ! <?php echo esc_js( $item ); ?>.is_subsub ) { #>
									<# if ( ! <?php echo esc_js( $item ); ?>.is_locked ) { #>
										<span class="fmwp-write-reply fmwp-reply-action-link fmwp-tip-n"
											title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>"
											data-reply_id="{{{<?php echo esc_js( $item ); ?>.reply_id}}}"
											data-mention="{{<?php echo esc_js( $item ); ?>.mention}}"
											data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ); ?>">
											<i class="fas fa-reply"></i>
										</span>
									<# } #>
								<# } #>
								<?php
							}
						} else {
							?>
							<# if ( ! data.sub_template || ! <?php echo esc_js( $item ); ?>.is_subsub ) { #>
								<# if ( ! <?php echo esc_js( $item ); ?>.is_locked ) { #>
									<span class="fmwp-write-reply fmwp-reply-action-link fmwp-tip-n <?php echo esc_attr( FMWP()->frontend()->shortcodes()->unlogged_class() ); ?>"
										title="<?php esc_attr_e( 'Reply', 'forumwp' ); ?>"
										data-reply_id="{{{<?php echo esc_js( $item ); ?>.reply_id}}}"
										data-mention="{{<?php echo esc_js( $item ); ?>.mention}}"
										data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ); ?>">
												<i class="fas fa-reply"></i>
									</span>
								<# } #>
							<# } #>
							<?php
						}

						do_action( 'fmwp_reply_footer', $item );
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
	<?php if ( $show_footer ) { ?>
		<div class="fmwp-reply-children"></div>
	<?php } ?>
	<div class="clear"></div>
</div>
