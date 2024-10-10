<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post_id, $post;

$forums = get_posts(
	array(
		'post_type'      => 'fmwp_forum',
		'post_status'    => array( 'any', 'trash' ),
		'posts_per_page' => -1,
		'fields'         => array( 'ID', 'post_title' ),
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => 'fmwp_locked',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'fmwp_locked',
				'value'   => true,
				'compare' => '!=',
			),
		),
		'orderby'        => 'post_title',
		'order'          => 'ASC',
	)
);

$forum_options = array();
$forums        = ( ! empty( $forums ) && ! is_wp_error( $forums ) ) ? $forums : array();
foreach ( $forums as $forum ) {
	$forum_options[ $forum->ID ] = $forum->post_title;
}

$types = array();
foreach ( FMWP()->common()->topic()->types as $value => $topic_type ) {
	$types[ $value ] = $topic_type['title'];
}

$fields = array(
	array(
		'id'    => 'status_changed',
		'type'  => 'hidden',
		'value' => '',
		'data'  => array(
			'post-status' => $post->post_status,
		),
	),
	array(
		'id'      => 'fmwp_type',
		'type'    => 'select',
		'label'   => __( 'Type', 'forumwp' ),
		'options' => $types,
		'value'   => get_post_meta( $post->ID, 'fmwp_type', true ),
	),
	array(
		'id'      => 'fmwp_locked',
		'type'    => 'select',
		'label'   => __( 'Is Locked?', 'forumwp' ),
		'options' => array(
			'0' => __( 'No', 'forumwp' ),
			'1' => __( 'Yes', 'forumwp' ),
		),
		'value'   => get_post_meta( $post->ID, 'fmwp_locked', true ),
	),
	array(
		'id'      => 'fmwp_spam',
		'type'    => 'select',
		'label'   => __( 'Is Spam?', 'forumwp' ),
		'options' => array(
			'0' => __( 'No', 'forumwp' ),
			'1' => __( 'Yes', 'forumwp' ),
		),
		'value'   => get_post_meta( $post->ID, 'fmwp_spam', true ),
	),
	array(
		'id'      => 'fmwp_forum',
		'type'    => 'select',
		'label'   => __( 'Forum', 'forumwp' ),
		'options' => $forum_options,
		'value'   => get_post_meta( $post->ID, 'fmwp_forum', true ),
	),
);

$fields = apply_filters( 'fmwp_topic_admin_settings_fields', $fields, $post->ID );
?>

<div class="fmwp-admin-metabox fmwp">
	<?php
	FMWP()->admin()->forms(
		array(
			'class'     => 'fmwp-topic-attributes fmwp-top-label',
			'prefix_id' => 'fmwp_metadata',
			'fields'    => $fields,
		)
	)->display();
	?>

	<div class="clear"></div>
</div>
