<?php
/**
 * Template for the header in the archive topic
 *
 * This template can be overridden by copying it to your-theme/forumwp/archive-topic.php
 *
 * @version 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats_cols = array(
	'people'  => __( 'People', 'forumwp' ),
	'replies' => __( 'Replies', 'forumwp' ),
	'views'   => __( 'Views', 'forumwp' ),
	'updated' => __( 'Updated', 'forumwp' ),
);
$stats_cols = apply_filters( 'fmwp_topics_header_columns', $stats_cols );

$classes = apply_filters( 'fmwp_topics_header_classes', '' );
?>

<div class="fmwp-topics-wrapper-heading<?php echo esc_attr( $classes ); ?>">
	<span class="fmwp-topic-head-line fmwp-topic-col-topic">
		<?php esc_html_e( 'Topic', 'forumwp' ); ?>
	</span>
	<span class="fmwp-topic-head-line">
		<?php foreach ( $stats_cols as $key => $stats_title ) { ?>
			<span class="fmwp-topic-col-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $stats_title ); ?></span>
		<?php } ?>
	</span>
</div>
