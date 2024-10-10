<?php
/**
 * Template for the new forum
 *
 * This template can be overridden by copying it to your-theme/forumwp/new-forum.php
 *
 * @version 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="fmwp-new-forum-wrapper" class="fmwp">
	<?php
	$new_forum = FMWP()->frontend()->forms(
		array(
			'id' => 'fmwp-create-forum',
		)
	);

	$fields = array(
		array(
			'type'     => 'text',
			'label'    => __( 'Name', 'forumwp' ),
			'id'       => 'title',
			'required' => true,
		),
		array(
			'type'     => 'textarea',
			'label'    => __( 'Description', 'forumwp' ),
			'id'       => 'content',
			'required' => true,
		),
		array(
			'type'    => 'select',
			'label'   => __( 'Visibility', 'forumwp' ),
			'id'      => 'visibility',
			'options' => FMWP()->common()->forum()->visibilities,
		),
	);

	if ( FMWP()->options()->get( 'forum_categories' ) ) {
		$fields[] = array(
			'type'  => 'text',
			'label' => __( 'Categories', 'forumwp' ),
			'id'    => 'categories',
		);
	}

	$fields = apply_filters( 'fmwp_new_forum_fields', $fields, $new_forum );

	$new_forum->set_data(
		array(
			'id'        => 'fmwp-create-forum',
			'class'     => '',
			'prefix_id' => 'fmwp-forum',
			'fields'    => $fields,
			'hiddens'   => array(
				'fmwp-action' => 'create-forum',
				'nonce'       => wp_create_nonce( 'fmwp-create-forum' ),
			),
			'buttons'   => array(
				'create' => array(
					'type'  => 'submit',
					'label' => __( 'Create', 'forumwp' ),
				),
			),
		)
	);

	$new_forum->display();
	?>
</div>
