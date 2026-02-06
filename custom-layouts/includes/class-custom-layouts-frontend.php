<?php
/**
 * Frontend functionality class
 *
 * @package    Custom_Layouts
 * @since      1.0.0
 */

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

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for managing frontend functionality.
 *
 * @since      1.0.0
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */
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
		\Custom_Layouts\Grid::init();
	}

	/**
	 * Register the stylesheets for the public-facing side of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name . '-styles', CSS_Loader::get_css_url(), array(), (string) CSS_Loader::get_version( (int) $this->version ), 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function register_scripts() {

		wp_register_script( $this->plugin_name, plugins_url( 'assets/frontend/app.js', __DIR__ ), array( 'imagesloaded', 'masonry' ), $this->version, true );
		wp_enqueue_script( 'imagesloaded' );
		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( $this->plugin_name );
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

		if ( 1 === $load_jquery_i18n ) {
			wp_enqueue_script( $this->plugin_name . '-plugin-jquery-i18n' );
		}
	}
}
