<?php
/**
 * Template for the user replies
 *
 * This template can be overridden by copying it to your-theme/forumwp/user/replies.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_user_replies
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = ! empty( $fmwp_user_replies['user_id'] ) ? $fmwp_user_replies['user_id'] : get_current_user_id();

FMWP()->get_template_part(
	'js/replies-list',
	array(
		'show_footer'      => false,
		'show_reply_title' => true,
	)
);
?>

<div class="fmwp-user-replies fmwp" data-user_id="<?php echo esc_attr( $user_id ); ?>">
	<div class="fmwp-replies-wrapper"></div>
</div>
