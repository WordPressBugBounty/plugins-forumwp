<?php
/**
 * Template for the reply list JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/replies-list.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_js_replies_list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topic_id = ! empty( $fmwp_js_replies_list['topic_id'] ) ? $fmwp_js_replies_list['topic_id'] : false;

$reply_type       = isset( $fmwp_js_replies_list['type'] ) ? $fmwp_js_replies_list['type'] : '';
$actions          = isset( $fmwp_js_replies_list['actions'] ) ? $fmwp_js_replies_list['actions'] : '';
$show_footer      = isset( $fmwp_js_replies_list['show_footer'] ) ? $fmwp_js_replies_list['show_footer'] : true;
$show_reply_title = isset( $fmwp_js_replies_list['show_reply_title'] ) ? $fmwp_js_replies_list['show_reply_title'] : false; ?>

<script type="text/html" id="tmpl-fmwp-replies-<?php echo ! empty( $reply_type ) ? esc_attr( $reply_type ) . '-' : ''; ?>list">
	<# if ( data.replies.length > 0 ) { #>
		<# _.each( data.replies, function( reply, key, list ) { #>
			<?php
			FMWP()->get_template_part(
				'js/reply-row',
				array(
					'item'        => 'reply',
					'actions'     => $actions,
					'show_footer' => $show_footer,
					'topic_id'    => $topic_id,
					'show_title'  => $show_reply_title,
				)
			);
			?>
		<# }); #>
	<# } #>
</script>
