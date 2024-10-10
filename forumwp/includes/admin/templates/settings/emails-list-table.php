<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new fmwp\admin\Emails_List_Table(
	array(
		'singular' => __( 'Email Notification', 'forumwp' ),
		'plural'   => __( 'Email Notifications', 'forumwp' ),
		'ajax'     => false,
	)
);

/**
 * Filters the columns of the ListTable on the ForumWP > Settings > Email screen.
 *
 * @since 1.1.0
 * @hook fmwp_email_templates_columns
 *
 * @param {array} $columns Email ListTable columns.
 *
 * @return {array} Email ListTable columns.
 */
$columns = apply_filters(
	'fmwp_email_templates_columns',
	array(
		'email'      => __( 'Email', 'forumwp' ),
		'recipients' => __( 'Recipient(s)', 'forumwp' ),
		'configure'  => '',
	)
);

$list_table->set_columns( $columns );
$list_table->prepare_items();
?>

<form action="" method="get" name="fmwp-settings-emails" id="fmwp-settings-emails">
	<input type="hidden" name="page" value="forumwp-settings" />
	<input type="hidden" name="tab" value="email" />
	<?php $list_table->display(); ?>
</form>
