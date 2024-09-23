<?php
namespace Custom_Layouts\Settings;

use Custom_Layouts\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// class that registers the options found in filters
class Query {

	public static function init_dependant_settings() {

	}

	public static function get_data() {

		$settings_data = array(
			array(
				'name'     => 'post_types',
				'label'    => __( 'Post Types', 'custom-layouts' ),
				'type'     => 'Select2',
				'multiple' => true,
				'options'  => Settings::get_post_types(),
				'default'  => array( 'post' ),
				// 'dependsOnGlobal' => true,
				// 'restRoute'       => '/options/query_post_types',
				// 'withAjax'        => true,
				/*
				 'dataDependsOn'   => array(
					'integration_type',
					'taxonomy',
					'post_type',
				), */
			),
			/*
			array(
				'name'            => 'post_type',
				'label'           =>__( 'Post Type', 'custom-layouts' ),
				'type'            => 'Select2',
				'multiple'        => false,
				'options'         => Settings::get_post_types(),
				'default'         => 'post',
				//'dependsOnGlobal' => true,
				//'restRoute'       => '/options/query_post_types',
				//'withAjax'        => true,

			),*/
			/*
			array(
				'name'     => 'post_status',
				'label'    => __( 'Post Status', 'custom-layouts' ),
				'type'     => 'Select2',
				'multiple' => true,
				'options'  => Settings::get_post_stati(),
				'default'  => array( 'publish' ),
			),*/
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
				// 'min'     => '0',
				// 'max'     => '100',
				'default' => '0',
			),
			/*
			array(
				'name'    => 'exclude_ids',
				'label'   => __( 'Exclude Posts', 'custom-layouts' ),
				'type'    => 'Text',
				'default' => '',
				// 'options' => array( '' ),
			),*/
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
			/*
			 array(
				'name'    => 'sticky_posts',
				'label'   => __( 'Sticky posts behaviour', 'custom-layouts' ),
				'type'    => 'Select2',
				'default' => 'default',
				'options' => array(
					array(
						'value' => 'default',
						'label' => 'Default',
					),
					array(
						'value' => 'exclude',
						'label' => 'Exclude',
					),
					array(
						'value' => 'ignore',
						'label' => 'Ignore',
					),
				),
			),*/
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
				// 'label'       => __('Exclude current post', 'custom-layouts'),
				// 'description' => __('Exclude the current page / post / custom post type that the layout is displayed in.', 'custom-layouts'),
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
				// 'label'       => __('Exclude current post', 'custom-layouts'),
				// 'description' => __('Exclude the current page / post / custom post type that the layout is displayed in.', 'custom-layouts'),
				'type' => 'array',
				// 'default'   => 'yes',
				/*
				'options'       => array(
					array(
						'label' => __( 'Yes', 'custom-layouts' ),
						'value' => 'yes',
					),
					array(
						'label' => __( 'No', 'custom-layouts' ),
						'value' => 'no',
					),
				),*/

			),
		);
		return $settings_data;
	}

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

