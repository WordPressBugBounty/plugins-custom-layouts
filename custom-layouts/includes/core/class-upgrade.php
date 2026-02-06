<?php
/**
 * Upgrade handler file.
 *
 * Handle plugin upgrades.
 * Detect when our plugin has been updated and run routines.
 *
 * @link       http://codeamp.com
 * @since      1.4.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

namespace Custom_Layouts\Core;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upgrade class.
 *
 * Handles version detection and upgrade routines.
 *
 * @since 1.4.0
 */
class Upgrade {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.4.0
	 */
	public static function upgrade() {

		$current_version = CUSTOM_LAYOUTS_VERSION;
		$stored_version  = get_option( 'custom_layouts_version', '0.0.0' );

		if ( version_compare( $current_version, $stored_version, '!=' ) ) {
			CSS_Loader::save_css();
			Cache::purge_all_transients();
			update_option( 'custom_layouts_version', $current_version );
		}
	}
}
