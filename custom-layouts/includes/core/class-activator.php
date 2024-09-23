<?php
namespace Custom_Layouts;

use Custom_Layouts\Core\Upgrade;

/**
 * Fired during plugin activation
 *
 * @link       http://codeamp.com
 * @since      1.4.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activator {

	/**
	 * Active the plugin
	 *
	 * Activation actions - generate CSS.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Attempt to generate the CSS file.
		// If a file cannot be created, an option is set to use fallback
		// we need to generate base css for the default template
		Upgrade::upgrade();
	}

}
