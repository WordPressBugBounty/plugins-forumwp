<?php
/**
 * Template for the forum archive category
 *
 * This template can be overridden by copying it to your-theme/forumwp/archive-forum-category.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_archive_forum_category
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
FMWP()->get_template_part( 'js/forum-category-list' ); ?>

<div class="fmwp fmwp-archive-forum-categories-wrapper">
	<div class="fmwp-forum-categories-list-head">
		<?php if ( isset( $fmwp_archive_forum_category['search'] ) && 'yes' === $fmwp_archive_forum_category['search'] ) { ?>
			<div class="fmwp-forum-categories-search">
				<label><input type="text" value="" class="fmwp-forum-categories-search-line" /></label>
				<input type="button" class="fmwp-search-forum-category" title="<?php esc_attr_e( 'Search Categories', 'forumwp' ); ?>" value="<?php esc_attr_e( 'Search', 'forumwp' ); ?>" />
			</div>
		<?php } ?>
	</div>

	<div class="fmwp-forum-categories-wrapper"></div>

	<div class="fmwp-forum-categories-list-footer"></div>
</div>
<div class="clear"></div>
