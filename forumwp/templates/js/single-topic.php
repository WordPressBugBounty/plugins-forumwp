<?php
/**
 * Template for the single topic JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/single-topic.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_js_single_topic
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/html" id="tmpl-fmwp-topic">
	<?php
	FMWP()->get_template_part(
		'js/topic-row',
		array(
			'item'       => 'data',
			'actions'    => 'edit',
			'show_forum' => isset( $fmwp_js_single_topic['show_forum'] ) ? $fmwp_js_single_topic['show_forum'] : true,
		)
	);
	?>
</script>
