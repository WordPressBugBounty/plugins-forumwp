<?php
/**
 * Template for the forum category list JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/forum-category-list.php
 *
 * @version 2.1.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/html" id="tmpl-fmwp-forum-categories-list">

	<# if ( data.categories.length > 0 ) { #>
		<div class="fmwp-forum-category-row">
			<div class="fmwp-forum-category-row-lines">
				<div class="fmwp-forum-category-row-line fmwp-forum-category-primary-data">	</div>
				<div class="fmwp-forum-category-row-line fmwp-forum-category-statistics-data">
					<div class="fmwp-forum-category-forums fmwp-tip-n" title=""><?php esc_html_e( 'Forums', 'forumwp' ); ?></div>
					<div class="fmwp-forum-category-topics fmwp-tip-n" title=""><?php esc_html_e( 'Topics', 'forumwp' ); ?></div>
					<div class="fmwp-forum-category-replies fmwp-tip-n" title=""><?php esc_html_e( 'Replies', 'forumwp' ); ?></div>
				</div>
			</div>
			<div class="fmwp-forum-category-actions"></div>
		</div>
		<div class="clear"></div>
		<# _.each( data.categories, function( category, key, list ) { #>
			<div class="fmwp-forum-category-row <# if ( category.disabled ) { #>fmwp-forum-category-disabled<# } #>" data-category_id="{{{category.id}}}">
				<div class="fmwp-forum-category-row-lines">

					<div class="fmwp-forum-category-row-line fmwp-forum-category-primary-data">

						<div class="fmwp-forum-category-data">
							<span class="fmwp-forum-category-first-line">
								<span class="fmwp-forum-category-title"><a href="{{{category.permalink}}}"><# if ( category.child ) { #>— <# } #>{{{category.title}}}</a></span>
							</span>
							<div class="fmwp-forum-category-description">{{{category.content}}}</div>
						</div>

					</div>

					<div class="fmwp-forum-category-row-line fmwp-forum-category-statistics-data">

						<div class="fmwp-forum-category-forums fmwp-tip-n" title="<?php esc_attr_e( '{{{category.forums}}} forums', 'forumwp' ); ?>">
							{{{category.forums}}}
						</div>
						<div class="fmwp-forum-category-topics fmwp-tip-n" title="<?php esc_attr_e( '{{{category.topics}}} topics', 'forumwp' ); ?>">
							{{{category.topics}}}
						</div>
						<div class="fmwp-forum-category-replies fmwp-tip-n" title="<?php esc_attr_e( '{{{category.replies}}} people have replied', 'forumwp' ); ?>">
							{{{category.replies}}}
						</div>

					</div>
				</div>

				<div class="fmwp-forum-category-actions">
					<?php if ( is_user_logged_in() ) { ?>
						<# if ( Object.keys( category.dropdown_actions ).length > 0 ) { #>
							<span class="fmwp-forum-category-actions-dropdown" title="<?php esc_attr_e( 'More Actions', 'forumwp' ); ?>">
								<i class="fas fa-angle-down"></i>
								<div class="fmwp-dropdown" data-element=".fmwp-forum-category-actions-dropdown" data-trigger="click">
									<ul>
										<# _.each( category.dropdown_actions, function( actionData, key, list ) { #>
											<li><a href="#" class="{{{key}}}" data-entity_id="{{{actionData.entity_id}}}" data-nonce="{{{actionData.nonce}}}">{{{actionData.title}}}</a></li>
										<# }); #>
									</ul>
								</div>
							</span>
						<# } #>
					<?php } ?>
				</div>
			</div>
			<div class="clear"></div>
		<# }); #>
	<# } #>
</script>
