<?php
namespace Custom_Layouts;

use \add_shortcode;
use \add_action;
use \shortcode_atts;

/**
 * Handles the frontend display of the filters
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

use Custom_Layouts\Settings;
use Custom_Layouts\Layout\Controller as Layout_Controller;
use Custom_Layouts\Template\Controller as Template_Controller;
// use Custom_Layouts\Filters\Filter\ChoiceSelect;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Grid {

	const SHORTCODE_TAG          = 'custom-layouts';
	const LAYOUT_SHORTCODE_TAG   = 'custom-layout';
	const TEMPLATE_SHORTCODE_TAG = 'custom-template';

	private static $registered_filters = false;
	private static $filters;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	/*
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}*/

	/**
	 * Init...
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'init', 'Custom_Layouts\\Grid::wp_init', 10 );
	}

	public static function wp_init() {
		add_shortcode( self::SHORTCODE_TAG, 'Custom_Layouts\\Grid::shortcode' );
		add_shortcode( self::LAYOUT_SHORTCODE_TAG, 'Custom_Layouts\\Grid::layout_shortcode' );
		add_shortcode( self::TEMPLATE_SHORTCODE_TAG, 'Custom_Layouts\\Grid::template_shortcode' );
	}

	/**
	 * The main `[custom-layouts]` shortcode.
	 *
	 * @since    1.0.0
	 */
	public static function shortcode( $attributes ) {

		$defaults = array(
			'id'    => '',
			'name'  => '',
			'cache' => 'yes',
		);

		$attributes = shortcode_atts( $defaults, $attributes, self::SHORTCODE_TAG );

		$output = '';
		$id     = 0;

		if ( '' !== $attributes['name'] ) {
			// TODO - lookup ID by slug - make a helper function.
		} elseif ( '' !== $attributes['id'] ) {
			$id = absint( $attributes['id'] );
		} else {
			return $output;
		}

		ob_start();
		// Get the layout data associated with the ID.
		$layout = self::get_layout( $id, $attributes['cache'] );
		$layout->render();
		$output = ob_get_clean();

		return $output;
	}
	public static function layout_shortcode( $attributes ) {

		$defaults = array(
			'id'    => '',
			'name'  => '',
			'cache' => 'yes',
		);

		$attributes = shortcode_atts( $defaults, $attributes, self::LAYOUT_SHORTCODE_TAG );

		$output = '';
		$id     = 0;

		if ( '' !== $attributes['name'] ) {
			// TODO - lookup ID by slug - make a helper function.
		} elseif ( '' !== $attributes['id'] ) {
			$id = absint( $attributes['id'] );
		} else {
			return $output;
		}

		ob_start();
		// Get the layout data associated with the ID.
		$layout = self::get_layout( $id, $attributes['cache'] );
		$layout->render();
		$output = ob_get_clean();

		return $output;
	}
	public static function template_shortcode( $attributes ) {

		$defaults = array(
			'id'      => '',
			'post_id' => '',
			'name'    => '',
			'cache'   => 'yes',
		);

		$attributes = shortcode_atts( $defaults, $attributes, self::LAYOUT_SHORTCODE_TAG );

		$output = '';
		$id     = 0;

		if ( '' !== $attributes['name'] ) {
			// TODO - lookup ID by slug - make a helper function.
		} elseif ( '' !== $attributes['id'] ) {
			$id = absint( $attributes['id'] );
		} else {
			return $output;
		}

		ob_start();
		// Get the template data associated with the ID.
		$template = new Template_Controller( $id );
		$template->render( $attributes['post_id'] );
		$output = ob_get_clean();

		return $output;
	}

	public static function get_layout( $id, $cache ) {

		// Now create an instance of the Layout class and render.
		// $filter = new $filter_class_ref( $id, $filter_name, $grid_data );
		$grid = new Layout_Controller( $id, $cache );

		return $grid;
	}
}
