<?php
/**
 * Template for the header in the forum archive category
 *
 * This template can be overridden by copying it to your-theme/forumwp/archive-forum-header.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_archive_forum_header
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$stats_cols = array(
	'topics'  => __( 'Topics', 'forumwp' ),
	'replies' => __( 'Replies', 'forumwp' ),
	'updated' => __( 'Updated', 'forumwp' ),
);

$stats_cols               = apply_filters( 'fmwp_forums_header_columns', $stats_cols, $fmwp_archive_forum_header );
$header_forum_column_name = apply_filters( 'fmwp_forums_header_forum_column_name', esc_html__( 'Forum', 'forumwp' ), $fmwp_archive_forum_header );
?>
<div class="fmwp-forums-wrapper-heading">
	<span class="fmwp-forum-head-line fmwp-forum-col-forum">
		<?php echo wp_kses_post( $header_forum_column_name ); ?>
	</span>
	<span class="fmwp-forum-head-line">
		<?php foreach ( $stats_cols as $key => $stats_title ) { ?>
			<span class="fmwp-forum-col-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $stats_title ); ?></span>
		<?php } ?>
	</span>
</div>
