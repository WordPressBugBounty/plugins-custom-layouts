<?php
/**
 * WooCommerce Integration Class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */

namespace Custom_Layouts\Integrations;

use Custom_Layouts\Core\CSS_Loader;


use Custom_Layouts\Layout\Controller as Layout_Controller;
/**
 * All WooCommerce integration functionality
 * Add options to admin, integrate with frontend queries
 */
class WordPress_Importer {

	/**
	 * Init
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		add_action( 'import_end', 'Custom_Layouts\\Integrations\\WordPress_Importer::generate_css', 10 );
	}
	public static function generate_css() {
		// update the CSS file with the new templates
		// TODO - only do this when our templates have been imported
		CSS_Loader::save_css();
	}
}
