<?php
/**
 * Data storage class
 *
 * @package    Custom_Layouts
 * @since      1.0.0
 */

namespace Custom_Layouts\Core;

use Custom_Layouts\Settings;
use Custom_Layouts\Template\Controller as Template_Controller;

/**
 * Class for storing general data - usually uses `wp_options`
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data management class.
 *
 * Handles storage and retrieval of general data using WordPress options.
 *
 * @since 1.0.0
 */
class Data {

	/**
	 * Option name for storing post types update status.
	 *
	 * @var string
	 */
	private static $post_types_option_name = 'cl_post_types_updated';

	/**
	 * Initialize the data class.
	 */
	public static function init() {
		/*
		 * We need to know when to clear the cache so we need to know all the post
		 * types we're watching first but, this need to be done efficiently - we
		 * can't be querying all layouts, looping through, and getting a list of
		 * overall post types (this will take ages if we have 100 layouts) instead,
		 * we need to store a transient, with this in - which maps all layout Ids->
		 * their post types when a layout is added/updated/deleted, it updates its
		 * own post types in the transient
		 */
		add_action( 'save_post', 'Custom_Layouts\\Core\\Data::save_post' );
	}

	/**
	 * Save post callback to track post type updates.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_post( $post_id ) {
		// Don't do anything for revisions or autosaves.
		if ( is_int( wp_is_post_revision( $post_id ) ) || is_int( wp_is_post_autosave( $post_id ) ) ) {
			return;
		}

		if ( empty( $post_id ) ) {
			return;
		}

		// Keep track of all post types that have been updated.
		// It will get cleared when a loop is rendered on the frontend.
		$post_type = get_post_type( $post_id );
		self::add_post_type_updated( $post_type );
	}

	/**
	 * Add a post type to the updated list.
	 *
	 * @param string $post_type Post type name.
	 */
	public static function add_post_type_updated( $post_type ) {
		$post_types_updated = get_option( self::$post_types_option_name );
		if ( ! $post_types_updated ) {
			$post_types_updated = array();
		}
		array_push( $post_types_updated, $post_type );

		update_option( self::$post_types_option_name, array_unique( $post_types_updated ), false );
	}
}


Data::init();
