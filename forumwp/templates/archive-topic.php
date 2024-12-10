<?php
/**
 * Template for the archive topic
 *
 * This template can be overridden by copying it to your-theme/forumwp/archive-topic.php
 *
 * @version 2.1.3
 *
 * @var array $fmwp_archive_topic
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

$align = '';

ob_start();

if ( isset( $fmwp_archive_topic['new_topic'] ) && 'yes' === $fmwp_archive_topic['new_topic'] ) {
	if ( ! empty( $fmwp_archive_topic['default_forum'] ) ) {
		if ( is_user_logged_in() ) {
			if ( FMWP()->user()->can_create_topic( $fmwp_archive_topic['default_forum'] ) ) {
				?>
				<input type="button" class="fmwp-create-topic" title="<?php esc_attr_e( 'New topic', 'forumwp' ); ?>" value="<?php esc_attr_e( 'New topic', 'forumwp' ); ?>" />
				<?php
			} else {
				echo wp_kses(
					apply_filters( 'fmwp_create_topic_disabled_text', ' ', $fmwp_archive_topic['default_forum'] ),
					FMWP()->get_allowed_html( 'templates' )
				);
			}
		} else {
			?>
			<input type="button" class="<?php echo esc_attr( $unlogged_class ); ?>" title="<?php esc_attr_e( 'New topic', 'forumwp' ); ?>" value="<?php esc_attr_e( 'New topic', 'forumwp' ); ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to create a topic', 'forumwp' ); ?>" />
			<?php
		}
	} elseif ( isset( $fmwp_archive_topic['search'] ) && 'yes' === $fmwp_archive_topic['search'] ) {
		$align = ' fmwp-align-right';
	}
} elseif ( isset( $fmwp_archive_topic['search'] ) && 'yes' === $fmwp_archive_topic['search'] ) {
	$align = ' fmwp-align-right';
}

$new_topic_btn = ob_get_clean();

$props = array();
if ( ! empty( $fmwp_archive_topic['forum_id'] ) ) {
	$props[] = 'data-fmwp_forum_id="' . esc_attr( $fmwp_archive_topic['forum_id'] ) . '"';
}
if ( ! empty( $fmwp_archive_topic['status'] ) ) {
	$props[] = 'data-status="' . esc_attr( $fmwp_archive_topic['status'] ) . '"';
}

if ( ! empty( $fmwp_archive_topic['type'] ) ) {
	$props[] = 'data-type="' . esc_attr( $fmwp_archive_topic['type'] ) . '"';
}

if ( ! empty( $fmwp_archive_topic['tag'] ) && FMWP()->options()->get( 'topic_tags' ) ) {
	$props[] = 'data-topic_tag_id="' . esc_attr( $fmwp_archive_topic['tag'] ) . '"';
}

do_action( 'fmwp_before_topics_list' );

$wrapper_classes = array(
	'fmwp',
	'fmwp-archive-topics-wrapper',
);

if ( ! empty( $unlogged_class ) ) {
	$wrapper_classes[] = 'fmwp-unlogged-data';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<div class="fmwp-topics-list-head fmwp-responsive fmwp-ui-m fmwp-ui-l fmwp-ui-xl<?php echo esc_attr( $align ); ?>">

		<?php echo wp_kses( $new_topic_btn, FMWP()->get_allowed_html( 'templates' ) ); ?>

		<div class="fmwp-topics-list-head-line">
			<span class="fmwp-sort-wrapper">
				<label>
					<span><?php esc_html_e( 'Sort:', 'forumwp' ); ?>&nbsp;</span>
					<select class="fmwp-topics-sort" autocomplete="off">
						<?php foreach ( FMWP()->common()->topic()->sort_by as $key => $sort_title ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $fmwp_archive_topic['order'], $key ); ?>><?php echo esc_html( $sort_title ); ?></option>
						<?php } ?>
					</select>
				</label>
			</span>

			<?php if ( isset( $fmwp_archive_topic['search'] ) && 'yes' === $fmwp_archive_topic['search'] ) { ?>
				<div class="fmwp-topics-search">
					<label><input type="text" value="" class="fmwp-topics-search-line" placeholder="<?php esc_attr_e( 'Search Topics', 'forumwp' ); ?>" /></label>
					<input type="button" class="fmwp-search-topic" title="<?php esc_attr_e( 'Search Topics', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Search', 'forumwp' ); ?>" />
				</div>
			<?php } ?>
		</div>
	</div>

	<div class="fmwp-topics-list-head-mobile fmwp-responsive fmwp-ui-xs fmwp-ui-s<?php echo esc_attr( $align ); ?>">
		<div class="fmwp-topics-list-head-line-mobile">

			<?php echo wp_kses( $new_topic_btn, FMWP()->get_allowed_html( 'templates' ) ); ?>

			<div class="fmwp-topics-list-head-subline-mobile">
				<span class="fmwp-sort-wrapper">
					<label>
						<span><?php esc_html_e( 'Sort:', 'forumwp' ); ?>&nbsp;</span>
						<select class="fmwp-topics-sort" autocomplete="off">
							<?php foreach ( FMWP()->common()->topic()->sort_by as $key => $sort_title ) { ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $fmwp_archive_topic['order'], $key ); ?>>
									<?php echo esc_html( $sort_title ); ?>
								</option>
							<?php } ?>
						</select>
					</label>
				</span>

				<?php if ( isset( $fmwp_archive_topic['search'] ) && 'yes' === $fmwp_archive_topic['search'] ) { ?>
					<span class="fmwp-search-toggle" title="<?php esc_attr_e( 'Search', 'forumwp' ); ?>">
						<i class="fas fa-search"></i>
					</span>
				<?php } ?>
			</div>
		</div>

		<?php if ( isset( $fmwp_archive_topic['search'] ) && 'yes' === $fmwp_archive_topic['search'] ) { ?>
			<div class="fmwp-topics-list-head-line-mobile fmwp-search-wrapper">
				<div class="fmwp-topics-search">
					<label>
						<input type="text" value="" class="fmwp-topics-search-line" placeholder="<?php esc_attr_e( 'Search Topics', 'forumwp' ); ?>"/>
					</label>
					<input type="button" class="fmwp-search-topic" title="<?php esc_attr_e( 'Search Topics', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Search', 'forumwp' ); ?>" />
				</div>
			</div>
		<?php } ?>
	</div>

	<?php
	FMWP()->get_template_part( 'archive-topic-header' );

	$classes = apply_filters( 'fmwp_topics_wrapper_classes', '' );
	?>

	<div class="fmwp-topics-wrapper<?php echo esc_attr( $classes ); ?>" data-show_forum="<?php echo esc_attr( $fmwp_archive_topic['show_forum'] ); ?>"
		data-order="<?php echo ( ! empty( $fmwp_archive_topic['order'] ) ) ? esc_attr( $fmwp_archive_topic['order'] ) : ''; ?>"
		<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped above per data attribute ?>
		<?php echo ' ' . implode( ' ', $props ); ?>>
	</div>

	<div class="fmwp-topics-list-footer">
		<?php echo wp_kses( $new_topic_btn, FMWP()->get_allowed_html( 'templates' ) ); ?>
	</div>
</div>
<div class="clear"></div>

<?php
// Topics' dropdown actions.
FMWP()->frontend()->shortcodes()->dropdown_menu( '.fmwp-topic-actions-dropdown', 'click' );
