<?php
/**
 * Options/Integration Class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 * @subpackage Admin/Options
 */

namespace Custom_Layouts\Settings;

use Custom_Layouts\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A config class containing the settings for the integration options
 */
class Grid {

	/**
	 * Initialize dependent settings.
	 *
	 * @return void
	 */
	public static function init_dependant_settings() {
	}

	/**
	 * Gets and returns the the data as an associative array
	 *
	 * @return array
	 */
	public static function get_data() {
		$settings_data = array(
			array(
				'name'    => 'display_mode',
				'label'   => __( 'Display mode', 'custom-layouts' ),
				'default' => 'grid',
				'type'    => 'Select2',
				'options' => self::get_display_modes(),
			),
			array(
				'name'    => 'use_search_filter',
				'label'   => __( 'Display mode', 'custom-layouts' ),
				'default' => 'no',
				'type'    => 'Select2',

			),
			array(
				'name'    => 'search_filter_id',
				'label'   => __( 'Display mode', 'custom-layouts' ),
				'type'    => 'number',
				'default' => '',

			),

			array(
				'name'    => 'columns_large',
				'type'    => 'Number',
				'min'     => '1',
				'max'     => '10',
				'default' => '3',
			),
			array(
				'name'    => 'columns_medium',
				'type'    => 'Number',
				'min'     => '1',
				'max'     => '10',
				'default' => '3',
			),
			array(
				'name'    => 'columns_small',
				'type'    => 'Number',
				'min'     => '1',
				'max'     => '10',
				'default' => '2',
			),
			array(
				'name'    => 'columns_xsmall',
				'type'    => 'Number',
				'min'     => '1',
				'max'     => '10',
				'default' => '1',
			),

			array(
				'name'    => 'columns_medium_locked',
				'type'    => 'Toggle',
				'default' => 'yes',
				'options' => array(
					array(
						'label' => __( 'Yes', 'custom-layouts' ),
						'value' => 'yes',
					),
					array(
						'label' => __( 'No', 'custom-layouts' ),
						'value' => 'no',
					),
				),
			),
			array(
				'name'    => 'columns_small_locked',
				'type'    => 'Toggle',
				'default' => 'no',
				'options' => array(
					array(
						'label' => __( 'Yes', 'custom-layouts' ),
						'value' => 'yes',
					),
					array(
						'label' => __( 'No', 'custom-layouts' ),
						'value' => 'no',
					),
				),
			),
			array(
				'name'    => 'columns_xsmall_locked',
				'type'    => 'Toggle',
				'default' => 'no',
				'options' => array(
					array(
						'label' => __( 'Yes', 'custom-layouts' ),
						'value' => 'yes',
					),
					array(
						'label' => __( 'No', 'custom-layouts' ),
						'value' => 'no',
					),
				),
			),
			array(
				'name'    => 'grid_gap',
				'label'   => __( 'Grid Gap', 'custom-layouts' ),
				'type'    => 'Number', // TODO.
				'min'     => '0',
				'max'     => '100',
				'default' => array(
					'column' => '10px',
					'row'    => '10px',
				),
			),
			array(
				'name'    => 'margin_size',
				'label'   => __( 'Margin', 'custom-layouts' ),
				'default' => array(
					'top'    => '0px',
					'right'  => '0px',
					'bottom' => '0px',
					'left'   => '0px',
				),
			),
			array(
				'name'    => 'padding_size',
				'label'   => __( 'Padding', 'custom-layouts' ),
				'default' => array(
					'top'    => '0px',
					'right'  => '0px',
					'bottom' => '0px',
					'left'   => '0px',
				),
			),
			array(
				'name'  => 'background_color',
				'label' => __( 'Background Color', 'custom-layouts' ),
			),
			array(
				'name'  => 'background_gradient',
				'label' => __( 'Background Gradient', 'custom-layouts' ),
			),
			array(
				'name'      => 'use_masonry',
				'label'     => __( 'Use masonry', 'custom-layouts' ),
				'type'      => 'Toggle',
				'default'   => 'no',
				'options'   => array(
					array(
						'label' => __( 'Yes', 'custom-layouts' ),
						'value' => 'yes',
					),
					array(
						'label' => __( 'No', 'custom-layouts' ),
						'value' => 'no',
					),
				),
				'dependsOn' => array(
					'relation' => 'AND',
					array(
						'option'  => 'display_mode',
						'compare' => '=',
						'value'   => 'grid',
					),
				),
			),
			array(
				'name'      => 'equal_height_rows',
				'label'     => __( 'Equal height rows', 'custom-layouts' ),
				'type'      => 'Toggle',
				'default'   => 'no',
				'options'   => array(
					array(
						'label' => __( 'Yes', 'custom-layouts' ),
						'value' => 'yes',
					),
					array(
						'label' => __( 'No', 'custom-layouts' ),
						'value' => 'no',
					),
				),
				'dependsOn' => array(
					'relation' => 'AND',
					array(
						'option'  => 'use_masonry',
						'compare' => '!=',
						'value'   => 'yes',
					),
				),

			),
			array(
				'name'      => 'fill_last_row',
				'label'     => __( 'Fill last row', 'custom-layouts' ),
				'type'      => 'Toggle',
				'default'   => 'no',
				'options'   => array(
					array(
						'label' => __( 'Yes', 'custom-layouts' ),
						'value' => 'yes',
					),
					array(
						'label' => __( 'No', 'custom-layouts' ),
						'value' => 'no',
					),
				),
				'dependsOn' => array(
					'relation' => 'AND',
					array(
						'option'  => 'use_masonry',
						'compare' => '!=',
						'value'   => 'yes',
					),
				),

			),

			array(
				'name'        => 'template_id',
				'label'       => __( 'Post Template', 'custom-layouts' ),
				'type'        => 'Select2',
				'placeholder' => __( 'Choose a post template', 'custom-layouts' ),
				'description' => __( 'The design of the individual post', 'custom-layouts' ),
				'default'     => 'default',
				'options'     => array_merge(
					array(
						array(
							'label' => __( 'None', 'custom-layouts' ),
							'value' => 'none',
						),
					),
					Settings::get_templates_options()
				),
			),
			array(
				'name'        => 'layout_id',
				'label'       => __( 'Saved layout', 'custom-layouts' ),
				'type'        => 'Select2',
				'placeholder' => __( 'Choose a saved layout', 'custom-layouts' ),
				'default'     => '',
				'options'     => array(),
			),
			array(
				'name'    => 'use_saved_layout',
				'label'   => __( 'Use saved Layout', 'custom-layouts' ),
				'default' => 'no',
				'type'    => 'Select2',

			),
			array(
				'name'        => 'pagination_type',
				'label'       => __( 'Pagination', 'custom-layouts' ),
				'type'        => 'Select2',
				'placeholder' => __( 'Choose a pagination type', 'custom-layouts' ),
				'description' => __( 'Choose how to paginate the posts', 'custom-layouts' ),
				'default'     => 'none',
				'options'     => array(
					array(
						'label' => __( 'None', 'custom-layouts' ),
						'value' => 'none',
					),
					array(
						'label' => __( 'Numbers', 'custom-layouts' ),
						'value' => 'numbers',
					),
				),
			),
			array(
				'name'        => 'add_class',
				'default'     => '',
				'label'       => __( 'Add a class', 'search-filter' ),
				'description' => __( 'Seperate class names with a space', 'custom-layouts' ),
				'tab'         => 'advanced',
				'type'        => 'Text',
				'placeholder' => __( 'Add a CSS class to the filter', 'custom-layouts' ),
			),
		);

		return $settings_data;
	}

	/**
	 * Get available display modes.
	 *
	 * @return array Array of display mode options.
	 */
	public static function get_display_modes() {
		$display_methods = array(

			array(
				'value' => 'grid',
				'label' => __( 'List / Grid', 'custom-layouts' ),
			),
		);

		return $display_methods;
	}

		/**
		 * Get available integration methods.
		 *
		 * @return array Array of integration method options.
		 */
	public static function get_integration_methods() {

		$display_methods = array(
			array(
				'value' => 'grid_builder',
				'label' => __( 'Grid Builder', 'custom-layouts' ),
			),
			array(
				'value' => 'shortcode',
				'label' => __( 'Shortcode', 'custom-layouts' ),
			),
			array(
				'value' => 'archive',
				'label' => __( 'Page Template', 'custom-layouts' ),
			),
		);

		return $display_methods;
	}
}
