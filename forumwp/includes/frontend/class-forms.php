<?php
namespace fmwp\frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'fmwp\frontend\Forms' ) ) {

	/**
	 * Class Forms
	 *
	 * @package fmwp\frontend
	 */
	class Forms {

		/**
		 * @var bool
		 *
		 * @since 1.0
		 */
		private $form_data;

		/**
		 * @var string
		 *
		 * @since 1.0
		 */
		private $error_class = 'fmwp-form-error-row';

		/**
		 * @var array
		 *
		 * @since 1.0
		 */
		private $types = array(
			'text',
			'email',
			'url',
			'password',
			'hidden',
			'select',
			'wp_editor',
			'label',
			'textarea',
			'checkbox',
		);

		/**
		 * @var array
		 */
		private $errors = array();

		/**
		 * @var array
		 */
		private $notices = array();

		/**
		 * Forms constructor.
		 *
		 * @param bool|array $form_data
		 */
		public function __construct( $form_data = false ) {
			if ( $form_data ) {
				$this->form_data = $form_data;
			}
		}

		/**
		 * Set Form Data
		 *
		 * @param array $data
		 *
		 * @return $this
		 *
		 * @since 1.0
		 */
		public function set_data( $data ) {
			$this->form_data = $data;
			return $this;
		}

		/**
		 * Render form
		 *
		 * @param bool $display
		 * @return string
		 *
		 * @since 1.0
		 */
		public function display( $display = true ) {
			if ( empty( $this->form_data['fields'] ) && empty( $this->form_data['sections'] ) && empty( $this->form_data['hiddens'] ) ) {
				return '';
			}

			$id     = isset( $this->form_data['id'] ) ? $this->form_data['id'] : 'fmwp-frontend-form-' . uniqid();
			$name   = isset( $this->form_data['name'] ) ? $this->form_data['name'] : $id;
			$action = isset( $this->form_data['action'] ) ? $this->form_data['action'] : '';
			$method = isset( $this->form_data['method'] ) ? $this->form_data['method'] : 'post';

			$class      = 'form-table fmwp-form-table ' . ( ! empty( $this->form_data['class'] ) ? $this->form_data['class'] : '' );
			$class_attr = ' class="' . $class . '" ';

			$data_attrs = isset( $this->form_data['data'] ) ? $this->form_data['data'] : array();
			$data_attr  = '';
			foreach ( $data_attrs as $key => $val ) {
				$data_attr .= " data-{$key}=\"{$val}\" ";
			}

			$hidden = '';
			if ( ! empty( $this->form_data['hiddens'] ) ) {
				foreach ( $this->form_data['hiddens'] as $field_id => $value ) {
					$hidden .= $this->render_hidden( $field_id, $value );
				}
			}

			$fields = '';
			if ( ! empty( $this->form_data['fields'] ) ) {
				foreach ( $this->form_data['fields'] as $data ) {
					if ( ! $this->validate_type( $data ) ) {
						continue;
					}

					$fields .= $this->render_form_row( $data );
				}
			} elseif ( ! empty( $this->form_data['sections'] ) ) {
				foreach ( $this->form_data['sections'] as $section_key => $section_data ) {
					$section_data['key'] = $section_key;
					$fields             .= $this->render_section( $section_data );
				}
			}

			$buttons = '';
			if ( ! empty( $this->form_data['buttons'] ) ) {
				foreach ( $this->form_data['buttons'] as $field_id => $data ) {
					$buttons .= $this->render_button( $field_id, $data );
				}
			}

			ob_start();

			if ( $this->has_notices() ) {
				foreach ( $this->get_notices() as $notice ) { ?>
					<span class="fmwp-frontend-form-notice"><?php echo wp_kses( $notice, FMWP()->get_allowed_html( 'templates' ) ); ?></span>
					<?php
				}
			}

			if ( $this->has_error( 'global' ) ) {
				foreach ( $this->get_error( 'global' ) as $error ) {
					?>
					<span class="fmwp-frontend-form-error"><?php echo wp_kses( $error, FMWP()->get_allowed_html( 'templates' ) ); ?></span>
					<?php
				}
			}

			$move_form_tag = apply_filters( 'fmwp_forms_move_form_tag', false );

			if ( ! $move_form_tag ) {
				?>

				<form action="<?php echo esc_attr( $action ); ?>" method="<?php echo esc_attr( $method ); ?>"
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped above per data attribute ?>
				name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" class="fmwp-form" <?php echo $data_attr; ?>>

				<?php
			}

			echo wp_kses( $fields . $hidden . '<div class="fmwp-form-buttons-section">' . $buttons . '</div>', FMWP()->get_allowed_html( 'templates' ) );
			?>

			</form>

			<?php

			remove_all_filters( 'fmwp_forms_move_form_tag' );

			if ( $display ) {
				ob_get_flush();
				return '';
			}

			return ob_get_clean();
		}

		/**
		 * Validate type of the field
		 *
		 * @param array $data
		 *
		 * @return bool
		 *
		 * @since 1.0
		 */
		public function validate_type( $data ) {
			return ( ! empty( $data['type'] ) && in_array( $data['type'], $this->types, true ) );
		}

		/**
		 * Get field value
		 *
		 * @param array $field_data
		 * @param string $i
		 * @return string|array
		 *
		 * @since 1.0
		 */
		public function get_field_value( $field_data, $i = '' ) {
			// phpcs:disable WordPress.Security.NonceVerification -- there is already verified
			$default_index = 'default' . $i;
			$default       = isset( $field_data[ $default_index ] ) ? $field_data[ $default_index ] : '';

			$value_index = 'value' . $i;
			if ( 'checkbox' === $field_data['type'] ) {
				$value = ( isset( $field_data[ $value_index ] ) && '' !== $field_data[ $value_index ] ) ? $field_data[ $value_index ] : $default;
			} else {
				$value = isset( $field_data[ $value_index ] ) ? $field_data[ $value_index ] : $default;
			}

			$name = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			if ( ! empty( $this->form_data['prefix_id'] ) ) {
				$value = isset( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ) : $value;
			} else {
				$value = isset( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : $value;
			}

			if ( ! empty( $value ) ) {
				if ( ! empty( $this->form_data['prefix_id'] ) ) {
					if ( isset( $field_data['encode'] ) && ! isset( $_POST[ $this->form_data['prefix_id'] ][ $name ] ) ) {
						$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
					}
				} elseif ( isset( $field_data['encode'] ) && ! isset( $_POST[ $name ] ) ) {
					$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
				}
			}

			return $value;
			// phpcs:enable WordPress.Security.NonceVerification -- there is already verified
		}

		/**
		 * Render form row
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_form_row( $data ) {
			if ( empty( $data['id'] ) ) {
				return '';
			}

			if ( ! $this->validate_type( $data ) ) {
				return '';
			}

			$field_html = '';
			if ( method_exists( $this, 'render_' . $data['type'] ) ) {
				$field_html = call_user_func( array( &$this, 'render_' . $data['type'] ), $data );
			}

			if ( empty( $field_html ) ) {
				return '';
			}

			$row_classes = array( 'fmwp-form-row', 'fmwp-field-' . $data['type'] . '-type' );
			if ( $this->has_error( $data['id'] ) ) {
				$row_classes[] = $this->error_class;
			}

			ob_start();
			?>

			<div class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
				<?php echo wp_kses( $this->render_field_label( $data ), FMWP()->get_allowed_html( 'templates' ) ); ?>

				<span class="fmwp-form-field-content">

					<?php
					echo wp_kses( $field_html, FMWP()->get_allowed_html( 'templates' ) );

					if ( $this->has_error( $data['id'] ) ) {
						?>
						<span class="fmwp-form-field-error">
							<?php echo wp_kses( $this->get_error( $data['id'] ), FMWP()->get_allowed_html( 'templates' ) ); ?>
						</span>
					<?php } ?>

				</span>
			</div>

			<?php
			return ob_get_clean();
		}

		/**
		 * Render form section
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_section( $data ) {
			$html = '';

			if ( ! empty( $data['title'] ) ) {
				$html .= '<h3 class="fmwp-form-section-title">' . $data['title'] . '</h3>';
			}

			$html = apply_filters( 'fmwp_forms_before_render_section', $html, $data, $this->form_data );

			if ( ! empty( $data['wrap_fields'] ) ) {
				$html .= '<div class="fmwp-form-section-fields-wrapper" data-key="' . esc_attr( $data['key'] ) . '">';
			}

			if ( ! empty( $data['fields'] ) ) {
				foreach ( $data['fields'] as $fields_data ) {
					if ( ! $this->validate_type( $fields_data ) ) {
						continue;
					}

					$html .= $this->render_form_row( $fields_data );
				}
			}

			if ( ! empty( $data['wrap_fields'] ) ) {
				$html .= '</div>';
			}

			return $html;
		}

		/**
		 * Render field label
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_label( $data ) {
			return '<p>' . $data['label'] . '</p>';
		}

		/**
		 * Render button
		 *
		 * @param string $id
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_button( $id, $data ) {

			$type  = isset( $data['type'] ) ? $data['type'] : 'submit';
			$name  = isset( $data['name'] ) ? $data['name'] : $id;
			$label = isset( $data['label'] ) ? $data['label'] : __( 'Submit', 'forumwp' );

			$classes   = array( 'fmwp-form-button' );
			$classes[] = 'fmwp-form-button-' . $type;

			$data = isset( $data['data'] ) ? $data['data'] : array();

			$data_attr = '';
			foreach ( $data as $key => $val ) {
				$data_attr .= " data-{$key}=\"{$val}\" ";
			}

			ob_start();
			?>
			<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- already escaped above per data attribute ?>
			<input type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $label ); ?>" <?php echo $data_attr; ?>
					class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( $name ); ?>" />

			<?php
			return ob_get_clean();
		}

		/**
		 * Render hidden field
		 *
		 * @param string $id
		 * @param string $value
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_hidden( $id, $value ) {
			if ( empty( $value ) ) {
				return '';
			}

			$id_attr = ' id="' . $id . '" ';

			$data = array(
				'field_id' => $id,
			);

			$data_attr = '';
			foreach ( $data as $key => $val ) {
				$data_attr .= " data-{$key}=\"{$val}\" ";
			}

			$name      = $id;
			$name_attr = ' name="' . esc_attr( $name ) . '" ';

			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			return "<input type=\"hidden\" $id_attr $name_attr $data_attr $value_attr />";
		}

		/**
		 * Render field label
		 *
		 * @param array $data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_field_label( $data ) {
			if ( empty( $data['label'] ) ) {
				return '';
			}

			if ( 'label' === $data['type'] ) {
				return '';
			}

			$id       = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $data['id'];
			$for_attr = ' for="' . $id . '" ';

			$label        = $data['label'];
			$disable_star = apply_filters( 'fmwp_frontend_forms_required_star_disabled', false );
			if ( ! empty( $data['required'] ) && ! $disable_star ) {
				$label = $label . '<span class="fmwp-req" title="' . esc_attr__( 'Required', 'forumwp' ) . '">*</span>';
			}

			$helptip = ! empty( $data['helptip'] ) ? ' ' . FMWP()->helptip( $data['helptip'], false, false ) : '';

			return "<label $for_attr class=\"fmwp-form-row-label\">{$label}{$helptip}</label>";
		}

		/**
		 * Render text field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_text( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . $id . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'fmwp-' . $field_data['size'] . '-field' : 'fmwp-long-field';
			$class_attr = ' class="fmwp-forms-field ' . $class . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"{$value}\" ";
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';
			$disabled         = ! empty( $field_data['disabled'] ) ? ' disabled' : '';
			$readonly         = ! empty( $field_data['readonly'] ) ? ' readonly' : '';

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . $name . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			return "<input type=\"text\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required $disabled $readonly />";
		}

		/**
		 * Render text field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_checkbox( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id             = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr        = ' id="' . $id . '" ';
			$id_attr_hidden = ' id="' . $id . '_hidden" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'fmwp-' . $field_data['size'] . '-field' : 'fmwp-long-field';
			$class_attr = ' class="fmwp-forms-field ' . $class . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"{$value}\" ";
			}

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . $name . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . ( isset( $field_data['value'] ) ? $field_data['value'] : '1' ) . '"';

			if ( isset( $field_data['checked'] ) ) {
				if ( is_bool( $field_data['checked'] ) ) {
					$checked = checked( $field_data['checked'], true, false );
				} else {
					$checked = ' data-checked="' . esc_attr( $field_data['checked'] ) . '" ';
				}
			} else {
				$checked = checked( $value, true, false );
			}

			return "<input type=\"hidden\" $id_attr_hidden $name_attr value=\"0\" />
			<input type=\"checkbox\" $id_attr $class_attr $name_attr $data_attr $value_attr $checked />";
		}

		/**
		 * Render email field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_email( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . $id . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'fmwp-' . $field_data['size'] . '-field' : 'fmwp-long-field';
			$class_attr = ' class="fmwp-forms-field ' . $class . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"{$value}\" ";
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . $name . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			return "<input type=\"email\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";
		}

		/**
		 * Render text field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_url( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . $id . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'fmwp-' . $field_data['size'] . '-field' : 'fmwp-long-field';
			$class_attr = ' class="fmwp-forms-field ' . $class . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"{$value}\" ";
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . $name . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			return "<input type=\"url\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";
		}

		/**
		 * Render password field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_password( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . $id . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'fmwp-' . $field_data['size'] . '-field' : 'fmwp-long-field';
			$class_attr = ' class="fmwp-forms-field ' . $class . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"{$value}\" ";
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . $name . '" ';

			$value      = $this->get_field_value( $field_data );
			$value_attr = ' value="' . esc_attr( $value ) . '" ';

			return "<input type=\"password\" $id_attr $class_attr $name_attr $data_attr $value_attr $placeholder_attr $required />";
		}

		/**
		 * Render dropdown field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_select( $field_data ) {
			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			if ( empty( $field_data['options'] ) ) {
				return '';
			}

			$multiple = ! empty( $field_data['multi'] ) ? 'multiple' : '';

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . $id . '" ';

			$class      = ! empty( $field_data['class'] ) ? ' ' . $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? ' fmwp-' . $field_data['size'] . '-field' : ' fmwp-long-field';
			$class_attr = ' class="fmwp-forms-field' . $class . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"{$value}\" ";
			}

			$name             = $field_data['id'];
			$name             = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$hidden_name_attr = ' name="' . esc_attr( $name ) . '" ';
			$name            .= ( ! empty( $field_data['multi'] ) ? '[]' : '' );
			$name_attr        = ' name="' . esc_attr( $name ) . '" ';

			$value = $this->get_field_value( $field_data );

			$options = '';
			if ( ! empty( $field_data['options'] ) ) {
				foreach ( $field_data['options'] as $key => $option ) {
					if ( ! empty( $field_data['multi'] ) ) {

						if ( ! is_array( $value ) || empty( $value ) ) {
							$value = array();
						}

						$options .= '<option value="' . $key . '" ' . selected( in_array( $key, $value, true ), true, false ) . '>' . esc_html( $option ) . '</option>';
					} else {
						$options .= '<option value="' . $key . '" ' . selected( (string) $key === $value, true, false ) . '>' . esc_html( $option ) . '</option>';
					}
				}
			}

			$hidden = '';
			if ( ! empty( $multiple ) ) {
				$hidden = "<input type=\"hidden\" $hidden_name_attr value=\"\" />";
			}
			return "$hidden<select $multiple $id_attr $name_attr $class_attr $data_attr>$options</select>";
		}

		/**
		 * Render WP Editor field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_wp_editor( $field_data ) {

			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] : '' ) . '_' . $field_data['id'];

			$class  = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class .= ! empty( $field_data['size'] ) ? $field_data['size'] : 'fmwp-long-field';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= ' data-' . $key . '="' . esc_attr( $value ) . '" ';
			}

			$name = $field_data['id'];
			$name = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;

			$value = $this->get_field_value( $field_data );

			add_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ), 10, 2 );

			$editor_settings = apply_filters(
				'fmwp_content_editor_options',
				array(
					'textarea_name' => $name,
					'wpautop'       => true,
					'editor_height' => 145,
					'media_buttons' => false,
					'quicktags'     => false,
					'editor_css'    => '<style> .mce-top-part button { background-color: rgba(0,0,0,0.0) !important; } </style>',
					'tinymce'       => array(
						'init_instance_callback' => "function (editor) {
													editor.on( 'keyup paste mouseover', function (e) {
													var content = editor.getContent( { format: 'html' } ).trim();
													var textarea = jQuery( '#' + editor.id );
													textarea.val( content ).trigger( 'keyup' ).trigger( 'keypress' ).trigger( 'keydown' ).trigger( 'change' ).trigger( 'paste' ).trigger( 'mouseover' );
												});}",
					),
				)
			);

			ob_start();

			wp_editor( $value, $id, $editor_settings );

			$editor_contents = ob_get_clean();

			remove_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ), 10 );

			return $editor_contents;
		}

		/**
		 * Render textarea field
		 *
		 * @param array $field_data
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function render_textarea( $field_data ) {

			if ( empty( $field_data['id'] ) ) {
				return '';
			}

			$id      = ( ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '_' : '' ) . $field_data['id'];
			$id_attr = ' id="' . $id . '" ';

			$class      = ! empty( $field_data['class'] ) ? $field_data['class'] : '';
			$class     .= ! empty( $field_data['size'] ) ? 'fmwp-' . $field_data['size'] . '-field' : 'fmwp-long-field';
			$class_attr = ' class="fmwp-forms-field ' . $class . '" ';

			$data = array(
				'field_id' => $field_data['id'],
			);

			$data_attr = '';
			foreach ( $data as $key => $value ) {
				$data_attr .= " data-{$key}=\"{$value}\" ";
			}

			$placeholder_attr = ! empty( $field_data['placeholder'] ) ? ' placeholder="' . $field_data['placeholder'] . '"' : '';
			$required         = ! empty( $field_data['required'] ) ? ' required' : '';

			$name      = isset( $field_data['name'] ) ? $field_data['name'] : $field_data['id'];
			$name      = ! empty( $this->form_data['prefix_id'] ) ? $this->form_data['prefix_id'] . '[' . $name . ']' : $name;
			$name_attr = ' name="' . $name . '" ';

			$value = $this->get_field_value( $field_data );

			$html = "<textarea $id_attr $class_attr $name_attr $data_attr $placeholder_attr $required>" . esc_textarea( $value ) . '</textarea>';

			return $html;
		}

		/**
		 * Remove unusable MCE button for FMWP WP Editors
		 *
		 * @param array $mce_buttons
		 * @param int $editor_id
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function filter_mce_buttons( $mce_buttons, $editor_id ) {
			$mce_buttons = array_diff( $mce_buttons, array( 'alignright', 'alignleft', 'aligncenter', 'wp_adv', 'wp_more', 'fullscreen', 'formatselect', 'spellchecker' ) );
			$mce_buttons = apply_filters( 'fmwp_rich_text_editor_buttons', $mce_buttons, $editor_id, $this );

			return $mce_buttons;
		}

		/**
		 * @param string $field
		 * @param string $text
		 */
		public function add_error( $field, $text ) {
			if ( 'global' === $field ) {
				if ( ! isset( $this->errors['global'] ) ) {
					$this->errors['global'] = array();
				}
				$this->errors['global'][] = apply_filters( 'fmwp_form_global_error', $text );
			} elseif ( ! isset( $this->errors[ $field ] ) ) {
				$this->errors[ $field ] = apply_filters( 'fmwp_form_error', $text, $field );
			}
		}

		/**
		 * If a form has error
		 *
		 * @param  string  $field
		 * @return boolean
		 */
		public function has_error( $field ) {
			return ! empty( $this->errors[ $field ] ) || ! empty( $this->errors[ $field ] );
		}

		/**
		 * If a form has errors
		 *
		 * @return boolean
		 */
		public function has_errors() {
			return ! empty( $this->errors );
		}

		/**
		 * Flush errors
		 */
		public function flush_errors() {
			$this->errors = array();
		}

		/**
		 * @param string $field
		 *
		 * @return array
		 */
		public function get_errors( $field ) {
			return ! empty( $this->errors[ $field ] ) ? $this->errors[ $field ] : array();
		}

		/**
		 * Get a form error by a field key
		 *
		 * @param string $field
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_error( $field ) {
			return ! empty( $this->errors[ $field ] ) ? $this->errors[ $field ] : array();
		}

		/**
		 * Add form notice
		 *
		 * @param string $text
		 *
		 * @since 1.0
		 */
		public function add_notice( $text, $key ) {
			$this->notices[ $key ] = apply_filters( 'fmwp_form_notice', $text, $key );
		}

		/**
		 * If a form has notices
		 *
		 * @return boolean
		 *
		 * @since 1.0
		 */
		public function has_notices() {
			return ! empty( $this->notices );
		}

		/**
		 * Flush notices
		 *
		 * @since 1.0
		 */
		public function flush_notices() {
			$this->notices = array();
		}

		/**
		 * Get a form notices
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_notices() {
			return ! empty( $this->notices ) ? $this->notices : array();
		}
	}
}
