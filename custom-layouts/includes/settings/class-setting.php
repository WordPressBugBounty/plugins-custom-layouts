<?php
/**
 * The instance of an individual Setting
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */

namespace Custom_Layouts\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Container helper functions for manipulating settings
 * and changing their options
 */
class Setting {

	/**
	 * Contains most of the data for a setting as an assoc array
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $data    The data of this setting
	 */
	private $data = array();

	/**
	 * The options are stored as assoc array - in order
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The options
	 */
	private $options = array();

	/**
	 * Initialize the class
	 *
	 * @since    1.0.0
	 * @param    array $args       Initial data for this setting.
	 */
	public function __construct( $args ) {

		foreach ( $args as $key => $val ) {

			if ( 'options' === $key ) {
				$this->add_options_from_array( $val, $this->options );
			} else {
				$this->data[ $key ] = $val;
			}
		}
	}

	/**
	 * Takes options as an array of options (numeric) and converts into
	 * and ordered assoc array.
	 *
	 * @since    1.0.0
	 * @param    array $options_arr       Initial options.
	 * @param    array $to_target         Where to add the options (to support nested options).
	 */
	private function add_options_from_array( $options_arr, &$to_target ) {

		if ( ! is_array( $to_target ) ) {
			return;
		}

		if ( ! is_array( $options_arr ) ) {
			return;
		}

		foreach ( $options_arr as $option ) {
			if ( $this->is_valid_option( $option ) ) {
				$to_target[ $option['value'] ] = $option;

			} elseif ( $this->is_valid_option_group( $option ) ) {
				// It's a group, so recurse through the options.
				$to_target[ $option['name'] ]            = $option;
				$to_target[ $option['name'] ]['options'] = array();

				$this->add_options_from_array( $option['options'], $to_target[ $option['name'] ]['options'] );

			} else {
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'An option in the setting "%1$s" does not have valid values', 'custom-layouts' ), esc_html( $this->data['name'] ) ), '1.0.0' );
			}
		}
	}

	/**
	 * Checks whether an option is valid option (not a group)
	 *
	 * @since    1.0.0
	 * @param    array $option            The option.
	 */
	private function is_valid_option( $option ) {
		if ( ( ! isset( $option['value'] ) ) || ( ! isset( $option['label'] ) ) ) {
			return false;
		}
		if ( ( empty( $option['value'] ) ) || ( empty( $option['label'] ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if an option is valid option group (based on its properties)
	 *
	 * @since    1.0.0
	 * @param    array $option            The option.
	 */
	private function is_valid_option_group( $option ) {
		if ( ( ! isset( $option['name'] ) ) || ( ! isset( $option['label'] ) ) || ( ! isset( $option['options'] ) ) ) {
			return false;
		}
		if ( ( empty( $option['name'] ) ) || ( empty( $option['label'] ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Based on whether an option is a flat option, or an option group,
	 * returns a unique identifier based on its properties.
	 *
	 * @since    1.0.0
	 * @param    array $option            The option.
	 */
	private function get_option_key( $option ) {
		if ( $this->is_valid_option( $option ) ) {
			return $option['value'];
		} elseif ( $this->is_valid_option_group( $option ) ) {
			return $option['name'];
		}
		return -1;
	}

	/**
	 * Return the data
	 *
	 * @since    1.0.0
	 */
	public function get_data() {
		return $this->data;
	}
	/**
	 * Return the data
	 *
	 * @since    1.0.0
	 */
	public function get_default() {
		return isset( $this->data['default'] ) ? $this->data['default'] : '';
	}

	/**
	 * Map the args back into the data object
	 *
	 * @since    1.0.0
	 * @param    array $args            The args.
	 */
	public function update( $args ) {
		$this->data = wp_parse_args( $args, $this->data );
	}

	/**
	 * Get the internal data + options (as numerical array) for use in JS
	 *
	 * @since    1.0.0
	 */
	public function get_array() {

		$setting = $this->data;
		if ( ! empty( $this->options ) ) {
			$setting['options'] = $this->get_options_array( $this->options );
		}
		return $setting;
	}

	/**
	 * Returns options (as numerical array) for use in JS
	 *
	 * @since    1.0.0
	 *
	 * @param    array $options            The assoc array of options.
	 */
	public function get_options_array( $options ) {
		// TODO - remember to add in "default" options to our select2 fields as we've removed them from config

		$options_arr = array();
		foreach ( $options as $key => $option ) {
			if ( ! isset( $option['options'] ) ) {
				array_push( $options_arr, $option );
			} else {
				$option['options'] = $this->get_options_array( $option['options'] );
				array_push( $options_arr, $option );
			}
		}

		return $options_arr;
	}
	/**
	 * Returns options (as numerical array) for use in JS
	 *
	 * @since    1.0.0
	 *
	 * @param    array $new_option         The new option data.
	 * @param    array $args               Additional params, such as `parent`.
	 */
	public function add_option( $new_option, $args = array() ) {

		$defaults = array(
			'parent' => -1,
		);

		$args = wp_parse_args( $args, $defaults );

		$option_key = $this->get_option_key( $new_option );

		if ( ! $option_key ) {
			// If it doesn't have a name/value to be used as a key then return.
			return;
		}

		if ( ( isset( $args['after'] ) ) || ( isset( $args['before'] ) ) ) {

			// Figure out which options array to update (in case of nested).
			$options = &$this->get_options( $args['parent'] );
			if ( ! $options ) {
				return;
			}

			// Rebuild the array in order, inserting our new option before/after the desired target.
			$new_options = array();
			foreach ( $options as $key => $option ) {

				if ( isset( $args['before'] ) ) {
					if ( $args['before'] === $key ) {
						$new_options[ $option_key ] = $new_option;
					}
				}

				$new_options[ $key ] = $option;

				if ( isset( $args['after'] ) ) {
					if ( $args['after'] === $key ) {
						$new_options[ $option_key ] = $new_option;
					}
				}
			}

			$options = $new_options;

		} else {
			$options[ $option_key ] = $new_option;
		}
	}
	/**
	 * Returns an individual option
	 *
	 * @since    1.0.0
	 *
	 * @param    array $option_name        The option name.
	 * @param    array $args               Add a parent to look in.
	 */
	public function get_option( $option_name, $args ) {

		$defaults = array(
			'parent' => -1,
		);
		$args     = wp_parse_args( $args, $defaults );

		$options = &$this->get_options( $args['parent'] );

		if ( ! $options ) {
			return false;
		}

		if ( ! isset( $options[ $option_name ] ) ) {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'The option "%1$s" does not exist', 'custom-layouts' ), $option_name ), '1.0.0' );
			return false;
		}

		return $options[ $option_name ];
	}

	/**
	 * Returns the options array based on the `parent` argument
	 *
	 * @since    1.0.0
	 *
	 * @param    string $parent             The parent name.
	 *
	 * @return   array $options            Returns the options array that belongs to the parent, by reference so it can be modified
	 */
	public function &get_options( $parent = -1 ) {
		if ( -1 === $parent ) {
			$options = &$this->options;
		} elseif ( ( $parent ) && ( isset( $this->options[ $parent ] ) ) ) {
			if ( isset( $this->options[ $parent ]['options'] ) ) {
				$options = &$this->options[ $parent ]['options'];
			} else {
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'The parent "%1$s" does not have children', 'custom-layouts' ), $parent ), '1.0.0' );
				return false;
			}
		} else {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'The parent "%1$s" does not exist', 'custom-layouts' ), $parent ), '1.0.0' );
			return false;
		}

		return $options;
	}

	/**
	 * Updates a specific option
	 *
	 * @since    1.0.0
	 *
	 * @param    string $option_name             The option name.
	 * @param    string $option_data             The new option data.
	 * @param    string $args                    Additional args.
	 */
	public function update_option( $option_name, $option_data, $args = array() ) {

		$defaults = array(
			'parent' => -1,
		);
		$args     = wp_parse_args( $args, $defaults );

		$option = $this->get_option( $option_name, $args );

		if ( ! $option ) {
			return false;
		}

		// Remove value + name from update data to prevent array index issues
		// - essentially, these cannot be modified.
		if ( isset( $option_data['value'] ) ) {
			unset( $option_data['value'] );
		}
		if ( isset( $option_data['name'] ) ) {
			unset( $option_data['name'] );
		}

		$option = wp_parse_args( $option_data, $option );

		$options = &$this->get_options( $args['parent'] );

		// Update the option.
		$options[ $option_name ] = $option;

	}
}
