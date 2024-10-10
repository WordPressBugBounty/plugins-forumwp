<?php
/**
 * @var array $fmwp_emails_base_wrapper Template args.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses( FMWP()->common()->mail()->get_email_template( $fmwp_emails_base_wrapper['slug'], $fmwp_emails_base_wrapper ), FMWP()->get_allowed_html( 'templates' ) );
