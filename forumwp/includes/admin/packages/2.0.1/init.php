<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="text/javascript">
	jQuery( document ).ready( function() {
		//upgrade styles
		fmwp_add_upgrade_log( '<?php echo esc_js( __( 'Upgrade Spam Replies and Topics...', 'forumwp' ) ); ?>' );

		wp.ajax.send( 'fmwp_spam201', {
			data:{
				nonce: fmwp_admin_data.nonce
			},
			success: function( response ) {
				fmwp_add_upgrade_log( response.message );

				//switch to the next package
				fmwp_run_upgrade();
			},
			error: function() {
				fmwp_wrong_ajax();
			}
		} );
	});
</script>
