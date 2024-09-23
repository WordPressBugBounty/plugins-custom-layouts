<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Custom Layouts - Post + Product grids made easy
 * Plugin URI:        https://customlayouts.com
 * Description:       Build a list or grid layout of any post type.  Design the look of each item in the layout using our powerful drag and drop template editor.
 * Version:           1.4.12
 * Author:            Code Amp
 * Author URI:        https://codeamp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-layouts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CUSTOM_LAYOUTS_DEBUG' ) ) {
	define( 'CUSTOM_LAYOUTS_DEBUG', false );
}

if ( ! defined( 'CUSTOM_LAYOUTS_QUERY_DEBUG' ) ) {
	define( 'CUSTOM_LAYOUTS_QUERY_DEBUG', false );
}
if ( ! defined( 'CUSTOM_LAYOUTS_PATH' ) ) {
	define( 'CUSTOM_LAYOUTS_PATH', plugin_dir_path( __FILE__ ) );
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-layouts-activator.php
 */
function activate_custom_layouts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-activator.php';
	Custom_Layouts\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-layouts-deactivator.php
 */
function deactivate_custom_layouts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-deactivator.php';
	Custom_Layouts\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_custom_layouts' );
register_deactivation_hook( __FILE__, 'deactivate_custom_layouts' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-custom-layouts.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_custom_layouts() {

	$plugin = new Custom_Layouts();
	$plugin->run();

}
run_custom_layouts();
