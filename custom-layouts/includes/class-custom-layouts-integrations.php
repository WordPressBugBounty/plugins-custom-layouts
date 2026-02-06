<?php
/**
 * Integrations loader file.
 *
 * Loads all 3rd party integrations.
 *
 * @package Custom_Layouts
 */

namespace Custom_Layouts;

use Custom_Layouts\Settings;
use Custom_Layouts\Integrations\Gutenberg;
use Custom_Layouts\Integrations\WordPress_Importer;
use Custom_Layouts\Integrations\Search_Filter_Pro;
use Custom_Layouts\Integrations\WooCommerce;
use Custom_Layouts\Integrations\WPML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrations class.
 *
 * Loads all 3rd party integrations.
 *
 * @link       https://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */
class Integrations {

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Initialize integrations.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		Gutenberg::init();
		WordPress_Importer::init();
		Search_Filter_Pro::init();
		WPML::init();
		WooCommerce::init();
	}
}
