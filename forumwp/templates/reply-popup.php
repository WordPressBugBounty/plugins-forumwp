<?php
/**
 * Template for the reply popup
 *
 * This template can be overridden by copying it to your-theme/forumwp/reply-popup.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_reply_popup
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topic_id = ! empty( $fmwp_reply_popup['topic_id'] ) ? $fmwp_reply_popup['topic_id'] : false;

FMWP()->get_template_part( 'js/single-reply', array( 'topic_id' => $topic_id ) );

ob_start();

do_action( 'fmwp_reply_popup_actions' );
?>

<input type="button" class="fmwp-reply-popup-discard" title="<?php esc_attr_e( 'Back to topic', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Discard', 'forumwp' ); ?>" />
<span style="position: relative;">
	<input type="button" class="fmwp-reply-popup-submit" title="<?php esc_attr_e( 'Submit Reply', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Submit Reply', 'forumwp' ); ?>" />
	<?php FMWP()->ajax_loader( 25 ); ?>
</span>

<?php $buttons = ob_get_clean(); ?>

<div id="fmwp-reply-popup-wrapper" class="fmwp fmwp-post-popup-wrapper">
	<span class="fmwp-post-popup-toolbar">
		<span class="fmwp-post-popup-action-fullsize">
			<i class="fas fa-expand-arrows-alt"></i>
			<i class="fas fa-compress-arrows-alt"></i>
		</span>
	</span>

	<form action="" method="post" name="fmwp-create-reply">
		<span id="fmwp-reply-popup-head" class="fmwp-post-popup-header">
			<span class="fmwp-post-popup-header-section">
				<span id="fmwp-reply-popup-avatar">
					<?php echo wp_kses( FMWP()->user()->get_avatar( get_current_user_id() ), FMWP()->get_allowed_html( 'templates' ) ); ?>
				</span>

				<span id="fmwp-reply-popup-quote">
					<i class="fas fa-reply"></i>
					<span>
						<?php
						// translators: %s is a topic title
						echo esc_html( sprintf( __( 'Replying to: "%s"', 'forumwp' ), get_the_title( absint( $topic_id ) ) ) );
						?>
					</span>
				</span>
			</span>
			<span class="fmwp-post-popup-header-section fmwp-post-popup-actions fmwp-responsive fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
				<?php echo wp_kses( $buttons, FMWP()->get_allowed_html( 'templates' ) ); ?>
			</span>
		</span>
		<div class="clear"></div>

		<input type="hidden" name="fmwp-action" value="create-reply" />
		<input type="hidden" name="fmwp-reply[reply_id]" value="" />
		<input type="hidden" name="fmwp-reply[topic_id]" value="<?php echo esc_attr( $topic_id ); ?>" />
		<input type="hidden" name="fmwp-reply[parent_id]" value="" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'fmwp-create-reply' ) ); ?>" />

		<div id="fmwp-reply-popup-editors">
			<div id="fmwp-reply-popup-editor" data-editor-id="fmwpreplycontent">
				<label>
					<?php FMWP()->common()->render_editor( 'reply' ); ?>
				</label>
			</div>
			<span id="fmwp-reply-popup-preview-action" data-show_label="<?php esc_attr_e( 'Show preview', 'forumwp' ); ?>" data-hide_label="<?php esc_attr_e( 'Hide preview', 'forumwp' ); ?>">
				<?php esc_html_e( 'Hide preview', 'forumwp' ); ?>
			</span>
			<div id="fmwp-reply-popup-editor-preview">
				<div id="fmwpreplycontent-preview"></div>
			</div>
		</div>

		<span class="fmwp-post-popup-actions-bottom fmwp-responsive fmwp-ui-xs fmwp-ui-s">
			<?php echo wp_kses( $buttons, FMWP()->get_allowed_html( 'templates' ) ); ?>
		</span>
	</form>
	<div class="clear"></div>
</div>
