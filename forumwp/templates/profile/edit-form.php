<?php
/**
 * Template for the edit form
 *
 * This template can be overridden by copying it to your-theme/forumwp/profile/edit-form.php
 *
 * @version 2.1.0
 *
 * @var array $fmwp_profile_edit_form
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id     = isset( $fmwp_profile_edit_form['user_id'] ) ? $fmwp_profile_edit_form['user_id'] : '{{{data.id}}}';
$login       = isset( $fmwp_profile_edit_form['user_login'] ) ? $fmwp_profile_edit_form['user_login'] : '{{{data.login}}}';
$email       = isset( $fmwp_profile_edit_form['user_email'] ) ? $fmwp_profile_edit_form['user_email'] : '{{{data.email}}}';
$first_name  = isset( $fmwp_profile_edit_form['first_name'] ) ? $fmwp_profile_edit_form['first_name'] : '{{{data.first_name}}}';
$last_name   = isset( $fmwp_profile_edit_form['last_name'] ) ? $fmwp_profile_edit_form['last_name'] : '{{{data.last_name}}}';
$url         = isset( $fmwp_profile_edit_form['user_url'] ) ? $fmwp_profile_edit_form['user_url'] : '{{{data.url}}}';
$description = isset( $fmwp_profile_edit_form['description'] ) ? $fmwp_profile_edit_form['description'] : '{{{data.description}}}';
?>
<div id="fmwp-edit-profile-form-wrapper" class="fmwp">

	<?php
	$edit_profile = FMWP()->frontend()->forms(
		array(
			'id' => 'fmwp-edit-profile',
		)
	);

	$fields = array(
		array(
			'type'     => 'text',
			'label'    => __( 'Username', 'forumwp' ),
			'id'       => 'user_login',
			'value'    => $login,
			'disabled' => true,
			'readonly' => true,
		),
		array(
			'type'     => 'email',
			'label'    => __( 'Email', 'forumwp' ),
			'id'       => 'user_email',
			'required' => true,
			'value'    => $email,
		),
		array(
			'type'  => 'text',
			'label' => __( 'First Name', 'forumwp' ),
			'id'    => 'first_name',
			'value' => $first_name,
		),
		array(
			'type'  => 'text',
			'label' => __( 'Last Name', 'forumwp' ),
			'id'    => 'last_name',
			'value' => $last_name,
		),
		array(
			'type'  => 'url',
			'label' => __( 'Website', 'forumwp' ),
			'id'    => 'user_url',
			'value' => $url,
		),
		array(
			'type'  => 'textarea',
			'label' => __( 'Description', 'forumwp' ),
			'id'    => 'description',
			'value' => $description,
		),
	);

	$edit_profile->set_data(
		array(
			'id'        => 'fmwp-edit-profile',
			'class'     => '',
			'prefix_id' => '',
			'fields'    => $fields,
			'hiddens'   => array(
				'fmwp-action' => 'edit-profile',
				'user_id'     => ! empty( $user_id ) ? $user_id : '',
				'nonce'       => wp_create_nonce( 'fmwp-edit-profile' ),
			),
			'buttons'   => array(
				'update' => array(
					'type'  => 'submit',
					'label' => __( 'Update', 'forumwp' ),
				),
			),
		)
	);

	$edit_profile->display();
	?>
</div>
