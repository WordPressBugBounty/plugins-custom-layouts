<?php
/**
 * Query settings class
 *
 * @package    Custom_Layouts
 * @since      1.0.0
 */

namespace Custom_Layouts\Settings;

use Custom_Layouts\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that registers the options found in filters.
 */
class Query {

	/**
	 * Initialize dependent settings.
	 *
	 * @return void
	 */
	public static function init_dependant_settings() {
	}

	/**
	 * Get query settings data.
	 *
	 * @return array The query settings configuration array.
	 */
	public static function get_data() {

		$settings_data = array(
			array(
				'name'     => 'post_types',
				'label'    => __( 'Post Types', 'custom-layouts' ),
				'type'     => 'Select2',
				'multiple' => true,
				'options'  => Settings::get_post_types(),
				'default'  => array( 'post' ),
			),

			array(
				'name'    => 'posts_per_page',
				'label'   => __( 'Posts Per Page', 'custom-layouts' ),
				'type'    => 'Number',
				'min'     => '1',
				'max'     => '100',
				'default' => '6',
			),
			array(
				'name'    => 'offset',
				'label'   => __( 'Offset', 'custom-layouts' ),
				'type'    => 'Number',
				'default' => '0',
			),

			array(
				'name'    => 'order_by',
				'key'     => 'order_by',
				'label'   => __( 'Order By', 'custom-layouts' ),
				'type'    => 'Select2',
				'default' => 'date',
				'options' => self::get_order_options(),
			),
			array(
				'name'    => 'order_dir',
				'label'   => __( 'Order Direction', 'custom-layouts' ),
				'type'    => 'Select2',
				'default' => 'desc',
				'options' => array(
					array(
						'value' => 'desc',
						'label' => 'Descending',
					),
					array(
						'value' => 'asc',
						'label' => 'Ascending',
					),
				),
			),
			array(
				'name'    => 'ignore_sticky_posts',
				'label'   => __( 'Ignore sticky posts', 'custom-layouts' ),
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
				'name'        => 'exclude_current_post',
				'label'       => __( 'Exclude current post', 'custom-layouts' ),
				'description' => __( 'Exclude the current page / post / custom post type that the layout is displayed in.', 'custom-layouts' ),
				'type'        => 'Toggle',
				'default'     => 'yes',
				'options'     => array(
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
				'name'        => 'no_results_message',
				'label'       => __( 'No results message', 'custom-layouts' ),
				'description' => __( 'The message that is displayed when no posts are found matching the parameters.', 'custom-layouts' ),
				'type'        => 'Text',
				'default'     => __( 'No results found.', 'custom-layouts' ),
			),
			array(
				'name'    => 'filter_taxonomies',
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
				'name'    => 'filter_authors',
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
				'name'    => 'authors',
				'type'    => 'array',
				'default' => array(),
			),
			array(
				'name' => 'taxonomy_query',
				'type' => 'array',

			),
		);
		return $settings_data;
	}

	/**
	 * Get available order by options.
	 *
	 * @return array Array of order by options.
	 */
	private static function get_order_options() {
		$options = array(
			array(
				'value' => 'title',
				'label' => 'Post Title',
			),
			array(
				'value' => 'date',
				'label' => 'Published Date',
			),
			array(
				'value' => 'modified',
				'label' => 'Modified Date',
			),
			array(
				'value' => 'id',
				'label' => 'Post ID',
			),

		);
		return $options;
	}
}
