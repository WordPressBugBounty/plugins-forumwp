<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $post_id;

$templates = array(
	''                        => __( 'Global settings', 'forumwp' ),
	'fmwp_individual_default' => __( 'Default template', 'forumwp' ),
);

$custom_templates = FMWP()->common()->forum()->get_templates( $post );

$fields = array();
if ( count( $custom_templates ) ) {
	$fields[] = array(
		'id'      => 'fmwp_template',
		'type'    => 'select',
		'label'   => __( 'Template', 'forumwp' ),
		'value'   => get_post_meta( $post_id, 'fmwp_template', true ),
		'options' => array_merge( $templates, $custom_templates ),
	);
} else {
	$fields[] = array(
		'id'    => 'fmwp_template',
		'type'  => 'hidden',
		'value' => '',
	);
}

$fields = array_merge(
	$fields,
	array(
		array(
			'id'    => 'fmwp_icon',
			'type'  => 'icon_select',
			'label' => __( 'Icon', 'forumwp' ),
			'value' => get_post_meta( $post_id, 'fmwp_icon', true ),
		),
		array(
			'id'    => 'fmwp_icon_bgcolor',
			'type'  => 'color',
			'label' => __( 'Icon Background Color', 'forumwp' ),
			'value' => get_post_meta( $post_id, 'fmwp_icon_bgcolor', true ),
		),
		array(
			'id'    => 'fmwp_icon_color',
			'type'  => 'color',
			'label' => __( 'Icon Color', 'forumwp' ),
			'value' => get_post_meta( $post_id, 'fmwp_icon_color', true ),
		),
	)
);
?>

<div class="fmwp-admin-metabox fmwp">
	<?php
	FMWP()->admin()->forms(
		array(
			'class'     => 'fmwp-forum-styling fmwp-top-label',
			'prefix_id' => 'fmwp_metadata',
			'fields'    => $fields,
		)
	)->display();
	?>

	<a href="https://docs.forumwpplugin.com/article/1475-creating-a-new-forum-in-wp-admin#forum-styling" target="_blank">
		<?php esc_html_e( 'Learn more about forum styling', 'forumwp' ); ?>
	</a>

	<div class="clear"></div>
</div>
