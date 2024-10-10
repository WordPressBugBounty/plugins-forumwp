<?php
/**
 * Template for the reply row answers JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/reply-row-answers.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_js_reply_row_answers
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$item   = isset( $fmwp_js_reply_row_answers['item'] ) ? $fmwp_js_reply_row_answers['item'] : 'reply';
$active = isset( $fmwp_js_reply_row_answers['active'] ) ? $fmwp_js_reply_row_answers['active'] : false;

$classes = array( 'fmwp-show-child-replies' );
if ( $active ) {
	$classes[] = 'fmwp-replies-loaded';
}
?>
<# if ( <?php echo esc_js( $item ); ?>.has_children ) { #>
	<# if ( <?php echo esc_js( $item ); ?>.answers.length > 0 ) { #>
		<span class="fmwp-reply-avatars">
			<a href="#" title="<?php esc_attr_e( 'Show all replies', 'forumwp' ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<span class="fmwp-replies-count">{{{<?php echo esc_js( $item ); ?>.total_replies}}}</span>
				<# if ( <?php echo esc_js( $item ); ?>.total_replies == 1 ) { #>
					<?php esc_html_e( 'reply', 'forumwp' ); ?>
				<# } else { #>
					<?php esc_html_e( 'replies', 'forumwp' ); ?>
				<# } #>
			</a>&nbsp;&nbsp;
			<# _.each( <?php echo esc_js( $item ); ?>.answers, function( user, key, list ) { #>
				{{{user.avatar}}}
			<# }); #>
			<# if ( <?php echo esc_js( $item ); ?>.more_answers ) { #>
				<span class="fmwp-reply-more-answers">
					<i class="fas fa-ellipsis-h"></i>
				</span>
			<# } #>
		</span>
	<# } #>
<# } #>
