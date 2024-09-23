<?php
namespace Custom_Layouts\Core;

/**
 * A wrapper for WP functions to prevent repeated calls for the same information
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

class WP_Data {

	private static $post_types = array();
	private static $post_stati = array();
	private static $terms      = array();
	// get post types
	// get taxonomies
	// get taxonomy terms
	// get post status

	public static function get_post_types() {

		if ( empty( self::$post_types ) ) {

			$args = array(
				// 'public'                  => true,
				// 'publicly_queryable '     => true
			);

			self::$post_types = get_post_types( $args, 'objects' );
		}

		return self::$post_types;
	}

	public static function get_post_stati() {

		if ( empty( self::$post_stati ) ) {

			$post_stati_all    = get_post_stati( array(), 'objects' );
			$post_stati_ignore = array( 'auto-draft', 'inherit' );
			$post_stati        = array();

			foreach ( $post_stati_all as $post_status_key => $post_status ) {

				// Don't add any from the ignore list.
				if ( ! in_array( $post_status_key, $post_stati_ignore ) ) {
					array_push( $post_stati, $post_status );
				}
			}

			self::$post_stati = $post_stati;
		}

		return self::$post_stati;
	}

	public static function get_terms( $taxonomy_name ) {

		if ( ! isset( self::$terms[ $taxonomy_name ] ) ) {

			$term_args = array(
				'taxonomy'   => $taxonomy_name,
				'hide_empty' => false,
			);

			self::$terms[ $taxonomy_name ] = get_terms( $term_args );
		}

		return self::$terms[ $taxonomy_name ];
	}
}
