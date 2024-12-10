<?php
/**
 * Template for the mention email template
 *
 * This template can be overridden by copying it to your-theme/forumwp/emails/mention.php
 *
 * @version 2.1.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<a href="{author_url}">{author_name}</a> has mentioned you in <a href="{post_url}">{post_title}</a>:<br /><br />
{post_content}<br /><br />
<a href="{login_url}">Login</a> to reply.
