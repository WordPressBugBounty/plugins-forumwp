<?php
/**
 * Template for the single reply subreplies JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/single-reply-subreplies.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_js_single_reply_subreplies
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/html" id="tmpl-fmwp-subreplies-list">
	<?php FMWP()->get_template_part( 'js/reply-row-answers', $fmwp_js_single_reply_subreplies ); ?>
</script>
