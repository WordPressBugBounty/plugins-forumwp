<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_post_status(
	'fmwp_spam',
	array(
		'label'                     => _x( 'Spam', 'Spam status', 'forumwp' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		// translators: %s is a count.
		'label_count'               => _n_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'forumwp' ),
	)
);

add_filter( 'fmwp_topic_upgrade_last_update', 'fmwp_topic_stop_update_last_date201' );
add_filter( 'fmwp_forum_upgrade_last_update', 'fmwp_forum_stop_update_last_date201' );

$spam_topics = get_posts(
	array(
		'post_type'      => 'fmwp_topic',
		'posts_per_page' => -1,
		'post_status'    => 'fmwp_spam',
		'fields'         => 'ids',
	)
);

if ( ! empty( $spam_topics ) && ! is_wp_error( $spam_topics ) ) {
	foreach ( $spam_topics as $topic_id ) {
		$prev_status = get_post_meta( $topic_id, 'fmwp_prev_status', true );
		if ( empty( $prev_status ) ) {
			$prev_status = 'publish';
		}

		wp_update_post(
			array(
				'ID'          => $topic_id,
				'post_status' => $prev_status,
			)
		);

		update_post_meta( $topic_id, 'fmwp_spam', true );
	}
}

$spam_replies = get_posts(
	array(
		'post_type'      => 'fmwp_reply',
		'posts_per_page' => -1,
		'post_status'    => 'fmwp_spam',
		'fields'         => 'ids',
	)
);

if ( ! empty( $spam_replies ) && ! is_wp_error( $spam_replies ) ) {
	foreach ( $spam_replies as $reply_id ) {
		$prev_status = get_post_meta( $reply_id, 'fmwp_prev_status', true );
		if ( empty( $prev_status ) ) {
			$prev_status = 'publish';
		}

		wp_update_post(
			array(
				'ID'          => $reply_id,
				'post_status' => $prev_status,
			)
		);

		update_post_meta( $reply_id, 'fmwp_spam', true );
	}
}

remove_filter( 'fmwp_topic_upgrade_last_update', 'fmwp_topic_stop_update_last_date201' );
remove_filter( 'fmwp_forum_upgrade_last_update', 'fmwp_forum_stop_update_last_date201' );
