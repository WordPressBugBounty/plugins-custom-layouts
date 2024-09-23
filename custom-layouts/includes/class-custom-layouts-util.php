<?php
/**
 * Util class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */

namespace Custom_Layouts;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A helper class with functions used across the plugin
 */
class Util {
	/**
	 * Stores a copy of any options retrieved to save additional function calls to the same options later
	 * in page processing
	 *
	 * @var array
	 */
	private static $options = array();

	/**
	 * Stores a copy of the queries to save additional function calls for the same info later
	 *
	 * @var array
	 */
	private static $queries     = array();
	private static $svgs_loaded = array();

	/**
	 * Takes either a filter group ID, or a query ID, and returns the related set of filters / query
	 *
	 * @param int    $post_id  The Post ID of the filters / query.
	 * @param string $return   The desired return format - can be 'post' or 'id'.
	 *
	 * @return mixed
	 */
	public static function get_related_post( $post_id, $return = 'post' ) {

		$post_type = get_post_type( $post_id );

		if ( 'sf-query' === $post_type ) {

			$args = array(
				'post_type'   => 'sf-filters',
				'post_status' => array( 'draft', 'pending', 'publish' ),
				'post_parent' => $post_id,
				'fields'      => 'ids',
			);

			$filters_ids = get_posts( $args );

			// there should only be one filter group assocated with a query.
			if ( count( $filters_ids ) === 1 ) {
				$filters_id = $filters_ids[0];
				return $filters_id;
			}
			/*
			 else {
				// TODO - log error.
			} */

		} elseif ( 'sf-filters' === $post_type ) {
			// then just grab the parent ID.
			$query_id = wp_get_post_parent_id( $post_id );
			return $query_id;
		}

		return false;
	}

	/**
	 * Deep cleans a var
	 *
	 * Loops through arrays recursively, sanitizing scalar values only
	 *
	 * @return array
	 */
	public static function deep_clean( $var ) {
		if ( is_array( $var ) ) {
			// don't we need to sanitize the key as well?
			$cleaned = array();
			foreach ( $var as $key => $val ) {
				$cleaned[ sanitize_text_field( $key ) ] = self::deep_clean( $val );
			}
			return $cleaned;
		} else {
			// check if var is multiline or not
			$is_multline = false;
			if ( strstr( $var, PHP_EOL ) ) {
				$is_multline = true;
			}

			 // don't allow anything except scalar or array
			if ( ! is_scalar( $var ) ) {
				return '';
			}

			return $is_multline ? sanitize_textarea_field( $var ) : sanitize_text_field( $var );
		}
	}
	/**
	 * Gets all the Query posts
	 *
	 * @return array
	 */
	public static function get_queries() {

		if ( ! empty( self::$queries ) ) {
			return self::$queries;
		}

		$sf_query     = new \WP_Query( 'post_type=sf-query&post_status=publish&posts_per_page=-1&fields=ids&suppress_filters=1' );
		$sf_query_ids = $sf_query->get_posts();
		$sf_queries   = array();

		foreach ( $sf_query_ids as $query_id ) {
			// now store their query settings somewhere to use.

			$query_integration = get_post_meta( $query_id, 'custom-layouts-layout', true );
			$query_settings    = get_post_meta( $query_id, 'custom-layouts-query', true );

			array_push(
				$sf_queries,
				array(
					'id'                => $query_id,
					'query_integration' => $query_integration,
					'query_settings'    => $query_settings,
				)
			);
		}

		self::$queries = $sf_queries;
		return self::$queries;
	}
	/**
	 * Gets the "section" variable from the admin url or form post
	 *
	 * @return string
	 */
	public static function get_post_edit_section() {
		if ( isset( $_GET['section'] ) ) {
			return sanitize_key( $_GET['section'] );
		} elseif ( isset( $_POST['custom-layouts-section'] ) ) {
			return sanitize_key( $_POST['custom-layouts-section'] );
		}
		return 'query';
	}
	/**
	 * Gets the post type edit screen
	 *
	 * @return string
	 */
	public static function screen_is_template_edit() {
		$current_screen = get_current_screen();
		if ( 'cl-template' === $current_screen->id ) {
			return true;
		}
		return false;
	}
	public static function screen_is_layout_edit() {
		$current_screen = get_current_screen();
		if ( 'cl-layout' === $current_screen->id ) {
			return true;
		}
		return false;
	}

	/**
	 * Adds `.min` to a file extension if SCRIPT_DEBUG is disabled
	 *
	 * @param string $file_ext  The extension of the file.
	 *
	 * @return string
	 */
	public static function get_file_ext( $file_ext ) {

		$file_ext = strtolower( $file_ext );

		// TODO - reinstate
		/*
		if ( ( '.js' === $file_ext ) || ( '.css' === $file_ext ) ) {
			if ( false === SCRIPT_DEBUG ) {
				$file_ext = '.min' . $file_ext;
			}
		}*/

		return $file_ext;
	}


	/**
	 * Get the data for the object that gets passed to JS app
	 *
	 * @return array
	 */
	public static function get_js_data() {

		return array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'rest_url' => rest_url( 'custom-layouts' ),
			'home_url' => home_url( '/' ),
		);
	}


	/**
	 * Wrapper for the WP `get_option` function, implementing defaults if they do not exist yet
	 *
	 * @param string $option_name  The option key required.
	 *
	 * @return mixed  The value for the option
	 */
	public static function get_option( $option_name ) {

		// check to see if we've looked this up before, if so return the existing value.
		if ( isset( self::$options[ $option_name ] ) ) {
			return self::$options[ $option_name ];
		}

		// TODO - set defaults externally.
		$option_defaults = array(
			'custom_layouts_lazy_load_js' => 0,
			'custom_layouts_load_js_css'  => 1,
		);

		$option_value = get_option( $option_name );

		// if option is not set, and there is a default for it, use the default.
		if ( ( false === $option_value ) && ( isset( $option_defaults[ $option_name ] ) ) ) {
			$option_value = $option_defaults[ $option_name ];
		}

		self::$options[ $option_name ] = $option_value;

		return $option_value;
	}

	/**
	 * Converts an associative array to a HTML attribute string, escapes data
	 *
	 * @param array $attributes  An associative array of key -> value pairs.
	 *
	 * @return string
	 */
	public static function get_attributes_html( $attributes ) {

		$html = '';

		if ( is_array( $attributes ) ) {
			foreach ( $attributes as $attribute_name => $value ) {

				$clean_value = '';
				$clean_name  = sanitize_key( $attribute_name );
				if ( is_array( $value ) ) {
					$clean_value = esc_attr( wp_json_encode( $value ) );
				} else {
					$clean_value = esc_attr( $value );
				}

				// Make sure the attibute + value are not empty.
				if ( ( ! empty( $attribute_name ) ) && ( ! empty( $clean_value ) ) ) {
					$html .= ' ' . $clean_name . '="' . $clean_value . '" ';
				}

				if ( 'disabled' === $attribute_name ) {
					if ( empty( $clean_value ) ) {
						$html .= ' ' . $clean_name;
					}
				}
			}
		}

		return $html;
	}

	/**
	 * Inlines SVGs by filename, makes sure SVGs are never loaded twice,
	 * we use the ID's to use them so they are essentially just templates
	 *
	 * @param array $svgs  A list of SVG names (without file extension).
	 */
	public static function load_svgs( $svgs ) {

		$svgs_to_load = array();

		// Loop through, and only load the ones not yet loaded ( we can't load multiple times, they have unique IDs ).
		foreach ( $svgs as $svg_name ) {
			if ( ! in_array( $svg_name, self::$svgs_loaded ) ) {
				array_push( $svgs_to_load, $svg_name );
			}
		}

		// Return if empty.
		if ( empty( $svgs_to_load ) ) {
			return;
		}

		// TODO - use file_get_contents instead - https://sheelahb.com/blog/how-to-get-php-to-play-nicely-with-svg-files/ - https://css-tricks.com/using-svg/

		// Now we have some to load, so include + hide them - use inline display to prevent flicker.
		echo '<div class="custom-layouts-svg-template" aria-hidden="true" style="display: none;">';
		foreach ( $svgs_to_load as $svg_name ) {
			include trailingslashit( CUSTOM_LAYOUTS_PATH ) . 'assets/images/' . sanitize_file_name( $svg_name . '.svg' );
			array_push( self::$svgs_loaded, $svg_name );
		}
		echo '</div>';
	}


	public static function get_image_by_size( $post_attachment_id, $size_name ) {
		$attachment_data = wp_get_attachment_image_src( $post_attachment_id, $size_name );

		if ( $attachment_data ) {
			$attachment_meta = array(
				'url'     => $attachment_data[0],
				'width'   => $attachment_data[1],
				'height'  => $attachment_data[2],
				'cropped' => $attachment_data[3],
			);
			return $attachment_meta;
		}

		return false;
	}
}
