<?php
/**
 * Template for the user topics
 *
 * This template can be overridden by copying it to your-theme/forumwp/user/topics.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_user_topics
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

FMWP()->get_template_part(
	'js/topics-list',
	array(
		'show_forum' => ! empty( FMWP()->options()->get( 'show_forum' ) ),
	)
);

$user_id = ! empty( $fmwp_user_topics['user_id'] ) ? $fmwp_user_topics['user_id'] : get_current_user_id(); ?>

<div class="fmwp-user-topics fmwp" data-user_id="<?php echo esc_attr( $user_id ); ?>">
	<?php
	FMWP()->get_template_part( 'archive-topic-header' );

	$classes = apply_filters( 'fmwp_topics_wrapper_classes', '' );
	?>

	<div class="fmwp-topics-wrapper<?php echo esc_attr( $classes ); ?>"></div>
</div>
