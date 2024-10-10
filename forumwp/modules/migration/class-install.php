<?php
namespace fmwpm\migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwpm\migration\Install' ) ) {

	/**
	 * Class Install
	 *
	 * @package fmwpm\migration
	 */
	class Install {

		/**
		 * Settings defaults
		 *
		 * @var array
		 */
		private $settings_defaults = array();

		/**
		 * Install constructor.
		 */
		public function __construct() {
		}

		/**
		 *
		 */
		public function start() {
			FMWP()->options()->set_defaults( $this->settings_defaults );
		}
	}
}
