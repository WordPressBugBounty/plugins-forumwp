<?php
/**
 * Template for the forum archive page
 *
 * This template can be overridden by copying it to your-theme/forumwp/archive-forum.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_archive_forum
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

$align = '';
if ( isset( $fmwp_archive_forum['search'] ) && 'yes' === $fmwp_archive_forum['search'] ) {
	$align = ' fmwp-align-right';
}
$fmwp_order = ! empty( $fmwp_archive_forum['order'] ) ? $fmwp_archive_forum['order'] : 'date_desc';

$props = array();
if ( FMWP()->options()->get( 'forum_categories' ) ) {
	if ( ! empty( $fmwp_archive_forum['category'] ) ) {
		$props[] = 'data-category_id="' . esc_attr( $fmwp_archive_forum['category'] ) . '"';
		$props[] = 'data-with_subcategories="' . ( isset( $fmwp_archive_forum['with_sub'] ) ? esc_attr( $fmwp_archive_forum['with_sub'] ) : 0 ) . '"';
	}
}

$wrapper_classes = array(
	'fmwp',
	'fmwp-archive-forums-wrapper',
);
if ( ! empty( $unlogged_class ) ) {
	$wrapper_classes[] = ' fmwp-unlogged-data';
}

do_action( 'fmwp_before_forums_list' );
?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-order="<?php echo esc_attr( $fmwp_order ); ?>">

	<div class="fmwp-forums-list-head<?php echo esc_attr( $align ); ?>">
		<?php if ( isset( $fmwp_archive_forum['search'] ) && 'yes' === $fmwp_archive_forum['search'] ) { ?>
			<div class="fmwp-forums-search">
				<label><input type="text" value="" class="fmwp-forums-search-line" /></label>
				<input type="button" class="fmwp-search-forum"
					title="<?php esc_attr_e( 'Search Forums', 'forumwp' ); ?>"
					value="<?php esc_attr_e( 'Search', 'forumwp' ); ?>" />
			</div>
		<?php } ?>
	</div>

	<?php FMWP()->get_template_part( 'archive-forum-header', array() ); ?>

	<div class="fmwp-forums-wrapper" data-no-forums-text="<?php esc_attr_e( 'No forums have been created.', 'forumwp' ); ?>"
		<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped above per data attribute ?>
		<?php echo ' ' . implode( ' ', $props ); ?>></div>

	<div class="fmwp-forums-list-footer"></div>
</div>
<div class="clear"></div>

<?php
//Forums dropdown actions wrapper
if ( empty( $unlogged_class ) ) {
	FMWP()->frontend()->shortcodes()->dropdown_menu( '.fmwp-forum-actions-dropdown', 'click' );
}
