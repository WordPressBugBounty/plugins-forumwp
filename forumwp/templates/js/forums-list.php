<?php
/**
 * Template for the forum list JS template
 *
 * This template can be overridden by copying it to your-theme/forumwp/js/forum-list.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_js_forums_list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$forum_type = isset( $fmwp_js_forums_list['type'] ) ? $fmwp_js_forums_list['type'] : '';
$actions    = isset( $fmwp_js_forums_list['actions'] ) ? $fmwp_js_forums_list['actions'] : ''; ?>

<script type="text/html" id="tmpl-fmwp-forums-<?php echo ! empty( $forum_type ) ? esc_attr( $forum_type ) . '-' : ''; ?>list">
	<# if ( data.forums.length > 0 ) { #>
		<# _.each( data.forums, function( forum, key, list ) { #>
			<?php
				FMWP()->get_template_part(
					'js/forum-row',
					array(
						'item'    => 'forum',
						'actions' => $actions,
					)
				);
				?>
		<# }); #>
	<# } #>
</script>
