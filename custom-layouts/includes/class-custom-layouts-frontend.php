<?php
namespace Custom_Layouts;

use Custom_Layouts\Util;
use Custom_Layouts\Core\CSS_Loader;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/public
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		// \Custom_Layouts\Query\Selector::init();
		// \Custom_Layouts\Query::init();
		\Custom_Layouts\Grid::init();
	}

	/**
	 * Register the stylesheets for the public-facing side of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$file_ext = Util::get_file_ext( '.css' );
		// $load_js_css = get_option( 'custom_layouts_load_js_css' );

		// if( false === $load_js_css ) {
		// $load_js_css = 1;
		// }

		// if( 1 === $load_js_css ) {
			// wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname(__FILE__) ) . 'assets/css/frontend/custom-layouts' . $file_ext, array(), $this->version, 'all' );
			// wp_enqueue_style( $this->plugin_name . '-styles', CSS_Loader::get_css_url(), array(), $this->version, 'all' );

			wp_enqueue_style( $this->plugin_name . '-styles', CSS_Loader::get_css_url(), array(), CSS_Loader::get_version( $this->version ), 'all' );
		// }
	}

	/**
	 * Register the JavaScript for the public-facing side of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function register_scripts() {

		$file_ext = Util::get_file_ext( '.js' );

		wp_register_script( $this->plugin_name, plugins_url( 'assets/js/frontend/custom-layouts' . $file_ext, dirname( __FILE__ ) ), array( 'imagesloaded', 'masonry' ), $this->version, true );
		// wp_register_script( $this->plugin_name, plugins_url( 'assets/js/frontend/custom-layouts' . $file_ext, dirname(__FILE__) ), array('jquery'), $this->version, true );
		wp_enqueue_script( 'imagesloaded' );
		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( $this->plugin_name );

		/*
		 * figure out if we need to enqueue the scripts now or later
		 */

		// if the user has set lazy load JS, then we won't want to load here
		/*
		$lazy_load_js = Util::get_option( 'custom_layouts_lazy_load_js' );

		// checks whether the user has enabled  CSS & JS (they can disabled in settings)
		$load_js_css = Util::get_option( 'custom_layouts_load_js_css' );

		// if lazy load js is off, and load css & js files is on
		if( ( 1 != $lazy_load_js ) && ( 1 == $load_js_css ) ) {
			//$this->enqueue_scripts();
		}*/
	}

	/**
	 * Enqueue the JavaScript for the public-facing side of the site based on user settings.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$load_jquery_i18n = Util::get_option( 'custom_layouts_load_jquery_i18n' );
		$combobox_script  = Util::get_option( 'custom_layouts_combobox_script' );

		wp_enqueue_script( $this->plugin_name );

		if ( 1 == $load_jquery_i18n ) {
			wp_enqueue_script( $this->plugin_name . '-plugin-jquery-i18n' );
		}
	}
}

