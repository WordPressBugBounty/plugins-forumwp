<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_post_status(
	'fmwp_solved',
	array(
		'label'                     => _x( 'Solved', 'Solved status', 'forumwp' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		// translators: %s is a count.
		'label_count'               => _n_noop( 'Solved <span class="count">(%s)</span>', 'Solved <span class="count">(%s)</span>', 'forumwp' ),
	)
);

$solved_topics = get_posts(
	array(
		'post_type'      => 'fmwp_topic',
		'posts_per_page' => -1,
		'post_status'    => 'fmwp_solved',
		'fields'         => 'ids',
	)
);

add_filter( 'fmwp_topic_upgrade_last_update', 'fmwp_topic_stop_update_last_date' );
add_filter( 'fmwp_disable_email_notification_by_hook', '__return_true' );

if ( ! empty( $solved_topics ) && ! is_wp_error( $solved_topics ) ) {
	foreach ( $solved_topics as $topic_id ) {
		wp_update_post(
			array(
				'ID'          => $topic_id,
				'post_status' => 'publish',
			)
		);

		update_post_meta( $topic_id, 'fmwp_solved', true );
	}
}

remove_filter( 'fmwp_topic_upgrade_last_update', 'fmwp_topic_stop_update_last_date' );
