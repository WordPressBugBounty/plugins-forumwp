<?php
/**
 * Template for the replies
 *
 * This template can be overridden by copying it to your-theme/forumwp/profile/replies.php
 *
 * @version 2.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

FMWP()->get_template_part(
	'js/replies-list',
	array(
		'show_footer'      => false,
		'show_reply_title' => true,
	)
);
?>

<div class="fmwp-replies-wrapper"></div>
