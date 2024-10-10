<?php
namespace fmwp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\Modules' ) ) {

	/**
	 * Class Modules
	 *
	 * @package fmwp
	 */
	class Modules {

		/**
		 * Modules list
		 *
		 * @var array
		 */
		private $list = array();

		/**
		 * Modules constructor.
		 */
		public function __construct() {
			add_action( 'forumwp_loaded', array( &$this, 'predefined_modules' ), 0 );
		}

		/**
		 * Set modules list
		 *
		 */
		public function predefined_modules() {
			$modules = array(
				'basic' => array(
					'title'   => __( 'Basic', 'forumwp' ),
					'modules' => array(
						'migration' => array(
							'title'       => __( 'Migration', 'forumwp' ),
							'description' => __( 'Ability to migration forums, topics and replies from bbPress', 'forumwp' ),
							'path'        => FMWP_PATH . 'modules' . DIRECTORY_SEPARATOR . 'migration',
							'url'         => FMWP_URL . 'modules/migration/',
						),
					),
				),
			);

			$this->list = apply_filters( 'fmwp_predefined_modules', $modules );
		}

		/**
		 * Get list of modules
		 *
		 * @return array
		 */
		public function get_list() {
			return $this->list;
		}

		/**
		 * @return array
		 */
		private function get_raw_list() {
			$modules = $this->get_list();

			if ( empty( $modules ) ) {
				return array();
			}

			$raw_list = array();
			foreach ( $modules as $plan_data ) {
				if ( empty( $plan_data['modules'] ) ) {
					continue;
				}

				$raw_list[] = $plan_data['modules'];
			}

			return array_merge( ...$raw_list );
		}

		/**
		 * Get module data
		 *
		 * @param string $slug
		 *
		 * @return array
		 */
		public function get_data( $slug ) {
			$list = $this->get_raw_list();
			return $list[ $slug ];
		}

		/**
		 * Check if module is active
		 *
		 * @param string $slug Module slug
		 *
		 * @return bool
		 */
		public function is_active( $slug ) {
			$modules = $this->get_raw_list();

			if ( ! array_key_exists( $slug, $modules ) ) {
				return false;
			}

			$slug      = FMWP()->undash( $slug );
			$is_active = FMWP()->options()->get( "module_{$slug}_on" );

			return ! empty( $is_active );
		}

		/**
		 * Run main class of module
		 *
		 * @param string $slug Module slug
		 * @param array $data Module data
		 */
		private function run( $slug, $data ) {
			if ( ! empty( $data['path'] ) ) {
				$slug = FMWP()->undash( $slug );
				FMWP()->call_class( "fmwpm\\$slug\\Init" );
			}
		}

		/**
		 * @param string $slug
		 *
		 * @return mixed
		 */
		public function install( $slug ) {
			$slug = FMWP()->undash( $slug );
			return FMWP()->call_class( "fmwpm\\{$slug}\\Install" );
		}

		/**
		 * Load all modules
		 */
		public function load_modules() {
			$modules = $this->get_raw_list();
			if ( empty( $modules ) ) {
				return;
			}

			foreach ( $modules as $slug => $data ) {
				if ( ! $this->is_active( $slug ) ) {
					continue;
				}

				$this->run( $slug, $data );
			}
		}

		/**
		 * First install for all modules
		 */
		public function install_modules() {
			$modules = $this->get_raw_list();
			if ( empty( $modules ) ) {
				return;
			}

			foreach ( $modules as $slug => $data ) {
				if ( ! $this->is_active( $slug ) ) {
					continue;
				}

				if ( ! empty( $data['path'] ) ) {
					$this->install( $slug )->start();
				}
			}
		}
	}
}
