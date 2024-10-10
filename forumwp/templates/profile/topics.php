<?php
/**
 * Template for the topics
 *
 * This template can be overridden by copying it to your-theme/forumwp/profile/topics.php
 *
 * @version 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

FMWP()->get_template_part(
	'js/topics-list',
	array(
		'show_forum' => ! empty( FMWP()->options()->get( 'show_forum' ) ) ? true : false,
	)
);

FMWP()->get_template_part( 'archive-topic-header' );

$classes = apply_filters( 'fmwp_topics_wrapper_classes', '' ); ?>

<div class="fmwp-topics-wrapper<?php echo esc_attr( $classes ); ?>"></div>
