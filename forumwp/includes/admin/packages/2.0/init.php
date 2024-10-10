<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="text/javascript">
	jQuery( document ).ready( function() {
		//upgrade styles
		fmwp_add_upgrade_log( '<?php echo esc_js( __( 'Upgrade Solved Topics...', 'forumwp' ) ); ?>' );

		wp.ajax.send( 'fmwp_solved20', {
			data:{
				nonce: fmwp_admin_data.nonce
			},
			success: function( response ) {
				fmwp_add_upgrade_log( response.message );

				setTimeout( function () {
					//upgrade_subscriptions();
					upgrade_locked();
				}, fmwp_request_throttle );
			},
			error: function() {
				fmwp_wrong_ajax();
			}
		} );


		function upgrade_locked() {
			fmwp_add_upgrade_log( '<?php echo esc_js( __( 'Upgrade Locked Forums...', 'forumwp' ) ); ?>' );

			wp.ajax.send( 'fmwp_locked20', {
				data:{
					nonce: fmwp_admin_data.nonce
				},
				success: function( response ) {
					fmwp_add_upgrade_log( response.message );

					setTimeout( function () {
						upgrade_subscriptions();
					}, fmwp_request_throttle );
				},
				error: function() {
					fmwp_wrong_ajax();
				}
			} );
		}


		function upgrade_subscriptions() {
			fmwp_add_upgrade_log( '<?php echo esc_js( __( 'Upgrade Users Subscriptions...', 'forumwp' ) ); ?>' );

			wp.ajax.send( 'fmwp_subscriptions20', {
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
		}
	});
</script>
