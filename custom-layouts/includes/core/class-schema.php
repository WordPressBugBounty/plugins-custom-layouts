<?php
namespace Custom_Layouts\Core;

/**
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/subpackage
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Schema {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Creates the main CPTs
	 *
	 * @since    1.0.0
	 */
	public function create_post_types() {

		$public = false;
		// $show_in_menu = true;
		$show_in_menu = 'custom-layouts';

		$post_type_args = array(
			'public'             => $public,
			'show_ui'            => true,
			// 'builtin'            => false,
			'capability_type'    => 'page',
			'hierarchical'       => true,
			'rewrite'            => false,
			'publicly_queryable' => false,
			'show_in_menu'       => $show_in_menu,
			'supports'           => array( 'title', 'author' ),
			'has_archive'        => false,
			// 'show_in_rest'       => true,
		);

		// Custom Layouts - Layouts
		$labels                   = array(
			'name'               => __( 'Layouts', 'custom-layouts' ),
			// 'menu_name'                   =>  __( 'Custom Layouts', 'custom-layouts' ),
			// 'name_admin_bar'                  =>  __( 'Query', 'custom-layouts' ),
			'singular_name'      => __( 'Layout', 'custom-layouts' ),
			'add_new'            => __( 'Add New Layout', 'custom-layouts' ),
			'add_new_item'       => __( 'Add New Layout', 'custom-layouts' ),
			'edit_item'          => __( 'Edit Layout', 'custom-layouts' ),
			'new_item'           => __( 'New Layout', 'custom-layouts' ),
			'view_item'          => __( 'View Layout', 'custom-layouts' ),
			'search_items'       => __( 'Search Layout', 'custom-layouts' ),
			'not_found'          => __( 'No Layouts found', 'custom-layouts' ),
			'not_found_in_trash' => __( 'No Layouts found in Trash', 'custom-layouts' ),
		);
		$post_type_args['labels'] = $labels;
		register_post_type( 'cl-layout', $post_type_args );

		// Custom Layouts - Posts Templates
		$labels = array(
			'name'               => __( 'Templates', 'custom-layouts' ),
			// 'menu_name'                   =>  __( 'Custom Layouts', 'custom-layouts' ),
			// 'name_admin_bar'              =>  __( 'Query', 'custom-layouts' ),
			'singular_name'      => __( 'Template', 'custom-layouts' ),
			'add_new'            => __( 'Add New Template', 'custom-layouts' ),
			'add_new_item'       => __( 'Add New Template', 'custom-layouts' ),
			'edit_item'          => __( 'Edit Template', 'custom-layouts' ),
			'new_item'           => __( 'New Template', 'custom-layouts' ),
			'view_item'          => __( 'View Template', 'custom-layouts' ),
			'search_items'       => __( 'Search Templates', 'custom-layouts' ),
			'not_found'          => __( 'No Templates found', 'custom-layouts' ),
			'not_found_in_trash' => __( 'No Templates found in Trash', 'custom-layouts' ),
		);

		$post_type_args['labels'] = $labels;
		register_post_type( 'cl-template', $post_type_args );
	}
}
