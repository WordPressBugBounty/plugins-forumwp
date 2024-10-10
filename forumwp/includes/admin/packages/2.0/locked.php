<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_post_status(
	'fmwp_locked',
	array(
		'label'                     => _x( 'Locked', 'Locked status', 'forumwp' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		// translators: %s is a count
		'label_count'               => _n_noop( 'Locked <span class="count">(%s)</span>', 'Locked <span class="count">(%s)</span>', 'forumwp' ),
	)
);

$locked_forums = get_posts(
	array(
		'post_type'      => 'fmwp_forum',
		'posts_per_page' => -1,
		'post_status'    => 'fmwp_locked',
		'fields'         => 'ids',
	)
);

add_filter( 'fmwp_forum_upgrade_last_update', 'fmwp_forum_stop_update_last_date' );
add_filter( 'fmwp_disable_email_notification_by_hook', '__return_true' );

if ( ! empty( $locked_forums ) && ! is_wp_error( $locked_forums ) ) {
	foreach ( $locked_forums as $forum_id ) {
		wp_update_post(
			array(
				'ID'          => $forum_id,
				'post_status' => 'publish',
			)
		);

		update_post_meta( $forum_id, 'fmwp_locked', true );
	}
}

remove_filter( 'fmwp_forum_upgrade_last_update', 'fmwp_forum_stop_update_last_date' );

$locked_topics = get_posts(
	array(
		'post_type'      => 'fmwp_topic',
		'posts_per_page' => -1,
		'post_status'    => 'fmwp_locked',
		'fields'         => 'ids',
	)
);

add_filter( 'fmwp_topic_upgrade_last_update', 'fmwp_topic_stop_update_last_date' );

if ( ! empty( $locked_topics ) && ! is_wp_error( $locked_topics ) ) {
	foreach ( $locked_topics as $topic_id ) {
		wp_update_post(
			array(
				'ID'          => $topic_id,
				'post_status' => 'publish',
			)
		);

		update_post_meta( $topic_id, 'fmwp_locked', true );
	}
}

remove_filter( 'fmwp_topic_upgrade_last_update', 'fmwp_topic_stop_update_last_date' );
