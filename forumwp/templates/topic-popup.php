<?php
/**
 * Template for the topic popup
 *
 * This template can be overridden by copying it to your-theme/forumwp/topic-popup.php
 *
 * @version 2.1.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ob_start();

do_action( 'fmwp_topic_popup_actions' );
?>

<input type="button" class="fmwp-topic-popup-discard" title="<?php esc_attr_e( 'Back to forum', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Discard', 'forumwp' ); ?>" />
<span style="position: relative;">
	<input type="button" class="fmwp-topic-popup-submit" title="<?php esc_attr_e( 'Submit topic', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Submit topic', 'forumwp' ); ?>" />
	<?php FMWP()->ajax_loader( 25 ); ?>
</span>

<?php $buttons = ob_get_clean(); ?>

<div id="fmwp-topic-popup-wrapper" class="fmwp fmwp-post-popup-wrapper">
	<span class="fmwp-post-popup-toolbar">
		<span class="fmwp-post-popup-action-fullsize">
			<i class="fas fa-expand-arrows-alt"></i>
			<i class="fas fa-compress-arrows-alt"></i>
		</span>
	</span>

	<form action="" method="post" name="fmwp-create-topic">
		<span id="fmwp-topic-popup-head" class="fmwp-post-popup-header">
			<span class="fmwp-post-popup-header-section">
				<label class="fmwp-topic-popup-label fmwp-topic-title-label">
					<input type="text" id="fmwp-topic-title" name="fmwp-topic[title]" placeholder="<?php esc_attr_e( 'Topic title', 'forumwp' ); ?>" />
				</label>
				<?php if ( FMWP()->options()->get( 'topic_tags' ) ) { ?>
					<label class="fmwp-topic-popup-label fmwp-topic-tags-label">
						<input type="text" id="fmwp-topic-tags" name="fmwp-topic[tags]" placeholder="<?php esc_attr_e( 'Topic tags', 'forumwp' ); ?>" />
					</label>
				<?php } ?>
			</span>

			<span class="fmwp-post-popup-header-section fmwp-post-popup-actions fmwp-responsive fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
				<?php echo wp_kses( $buttons, FMWP()->get_allowed_html( 'templates' ) ); ?>
			</span>
		</span>
		<div class="clear"></div>

		<input type="hidden" name="fmwp-action" value="create-topic" />

		<?php
		if ( FMWP()->is_forum_page() ) {
			$forum_id = get_the_ID();
		} elseif ( FMWP()->is_topic_page() ) {
			$forum_id = FMWP()->common()->topic()->get_forum_id( get_the_ID() );
		} else {
			$forum_id = FMWP()->options()->get( 'default_forum' );
		}
		?>

		<input type="hidden" name="fmwp-topic[topic_id]" value="" />
		<input type="hidden" name="fmwp-topic[forum_id]" value="<?php echo esc_attr( $forum_id ); ?>" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'fmwp-create-topic' ) ); ?>" />

		<div id="fmwp-topic-popup-editors">
			<div id="fmwp-topic-popup-editor" data-editor-id="fmwptopiccontent">
				<label>
					<?php FMWP()->common()->render_editor( 'topic' ); ?>
				</label>
			</div>
			<span id="fmwp-topic-popup-preview-action" data-show_label="<?php esc_attr_e( 'Show preview', 'forumwp' ); ?>" data-hide_label="<?php esc_attr_e( 'Hide preview', 'forumwp' ); ?>">
				<?php esc_html_e( 'Hide preview', 'forumwp' ); ?>
			</span>
			<div id="fmwp-topic-popup-editor-preview">
				<div id="fmwptopiccontent-preview"></div>
			</div>
		</div>

		<span class="fmwp-post-popup-actions-bottom fmwp-responsive fmwp-ui-xs fmwp-ui-s">
			<?php echo wp_kses( $buttons, FMWP()->get_allowed_html( 'templates' ) ); ?>
		</span>
	</form>
	<div class="clear"></div>
</div>
