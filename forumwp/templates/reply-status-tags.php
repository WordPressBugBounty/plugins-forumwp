<?php
/**
 * Template for the reply status tags
 *
 * This template can be overridden by copying it to your-theme/forumwp/reply-status-tags.php
 *
 * @version 2.1.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<span class="fmwp-reply-tags-wrapper">
	<?php
	foreach ( FMWP()->common()->reply()->status_tags() as $class => $data ) {
		$attr_title = '';
		if ( ! empty( $data['label'] ) ) {
			$attr_title = $data['label'];
		}
		?>
		<span class="fmwp-reply-tag fmwp-reply-tag-<?php echo esc_attr( $class ); ?> fmwp-tip-n" <?php echo '' !== $attr_title ? 'title="' . esc_attr( $attr_title ) . '"' : ''; ?>>
			<?php echo esc_html( $data['title'] ); ?>
		</span>
		<?php
	}
	?>
</span>
