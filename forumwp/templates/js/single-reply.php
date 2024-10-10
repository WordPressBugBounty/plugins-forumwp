<?php
/**
 * Template for the single reply JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/single-reply.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_js_forum_row
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/html" id="tmpl-fmwp-parent-reply">
	<?php
	FMWP()->get_template_part(
		'js/reply-row',
		array(
			'item'        => 'data',
			'actions'     => 'edit',
			'show_footer' => true,
			'topic_id'    => ! empty( $fmwp_js_single_reply['topic_id'] ) ? $fmwp_js_single_reply['topic_id'] : false,
		)
	);
	?>
</script>
