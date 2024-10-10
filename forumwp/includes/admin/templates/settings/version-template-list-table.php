<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new fmwp\admin\Version_Template_List_Table(
	array(
		'singular' => __( 'Template', 'forumwp' ),
		'plural'   => __( 'Templates', 'forumwp' ),
		'ajax'     => false,
	)
);

/**
 * Filters the columns of the ListTable on the ForumWP > Settings > Override Templates screen.
 *
 * @since 2.0.3
 * @hook fmwp_versions_templates_columns
 *
 * @param {array} $columns Version Templates ListTable columns.
 *
 * @return {array} Version Templates ListTable columns.
 */
$columns = apply_filters(
	'fmwp_versions_templates_columns',
	array(
		'template'      => __( 'Template', 'forumwp' ),
		'core_version'  => __( 'Core version', 'forumwp' ),
		'theme_version' => __( 'Theme version', 'forumwp' ),
		'status'        => __( 'Status', 'forumwp' ),
	)
);

$list_table->set_columns( $columns );
$list_table->prepare_items();
?>

<form action="" method="get" name="fmwp-settings-template-versions" id="fmwp-settings-template-versions">
	<input type="hidden" name="page" value="fmwp_options" />
	<input type="hidden" name="tab" value="override_templates" />
	<?php $list_table->display(); ?>
</form>
