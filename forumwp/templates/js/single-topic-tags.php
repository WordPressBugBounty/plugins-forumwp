<?php
/**
 * Template for the single topic tags JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/single-topic-tags.php
 *
 * @version 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/html" id="tmpl-fmwp-topic-tags-line">
	<# if ( data.length ) { #>
		<?php esc_html_e( 'Tags:' ); ?>&nbsp;
		<# _.each( data, function( tag, key, list ) { #>
			<span class="fmwp-topic-tags-list">
				<a href="{{{tag.permalink}}}">{{{tag.name}}}</a><# if ( ( key + 1 ) < data.length ) { #>,&nbsp;<# } #>
			</span>
		<# }); #>
	<# } #>
</script>
