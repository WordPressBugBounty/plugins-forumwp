<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$globally_enabled_emails = FMWP()->common()->mail()->get_public_emails();

$template_args = array(
	'email_templates' => $globally_enabled_emails,
);
?>

<script type="text/html" id="tmpl-fmwp-profile-notifications">
	<?php FMWP()->get_template_part( 'profile/notifications-form', $template_args ); ?>
</script>
