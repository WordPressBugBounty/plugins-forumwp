<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $fmwp_profile_notifications_form['email_templates'] ) ) {
	return;
}

$email_templates = $fmwp_profile_notifications_form['email_templates'];
$user_id         = isset( $fmwp_profile_notifications_form['user_id'] ) ? $fmwp_profile_notifications_form['user_id'] : '{{{data.id}}}';
?>


<div id="fmwp-email-notifications-form-wrapper" class="fmwp">
	<?php
	$notifications_profile = FMWP()->frontend()->forms(
		array(
			'id' => 'fmwp-notifications-profile',
		)
	);

	$fields = array();
	foreach ( $email_templates as $email_key => $email_data ) {
		$field_key = 'enabled_' . $email_key . '_notification';

		$fields[] = array(
			'type'        => 'checkbox',
			'label'       => $email_data['title'],
			'description' => $email_data['description'],
			'id'          => $field_key,
			'checked'     => isset( $fmwp_profile_notifications_form[ $field_key ] ) ? (bool) $fmwp_profile_notifications_form[ $field_key ] : '{{{data.' . $field_key . '}}}',
		);
	}

	$notifications_profile->set_data(
		array(
			'id'        => 'fmwp-profile-notifications',
			'class'     => '',
			'prefix_id' => '',
			'fields'    => $fields,
			'hiddens'   => array(
				'fmwp-action' => 'profile-notifications',
				'user_id'     => ! empty( $user_id ) ? $user_id : '',
				'nonce'       => wp_create_nonce( 'fmwp-profile-notifications' ),
			),
			'buttons'   => array(
				'update' => array(
					'type'  => 'submit',
					'label' => __( 'Update', 'forumwp' ),
				),
			),
		)
	);

	$notifications_profile->display();
	?>
</div>
