<?php
/**
 * Template for the topic status tags
 *
 * This template can be overridden by copying it to your-theme/forumwp/topic-status-tags.php
 *
 * @version 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

foreach ( FMWP()->common()->topic()->status_tags() as $class => $label ) {
	?>
	<span class="fmwp-topic-tag fmwp-topic-status-tag fmwp-topic-tag-<?php echo esc_attr( $class ); ?>">
		<?php echo esc_html( $label ); ?>
	</span>
	<?php
}
