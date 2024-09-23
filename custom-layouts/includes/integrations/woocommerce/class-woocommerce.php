<?php
/**
 * WooCommerce Integration Class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */

namespace Custom_Layouts\Integrations;

use Custom_Layouts\Settings;
use Custom_Layouts\Core\Data;

/**
 * All WooCommerce integration functionality
 * Add options to admin, integrate with frontend queries
 */
class WooCommerce {

	/**
	 * Init
	 *
	 * @since    1.4.5
	 */
	public static function init() {
		add_action( 'woocommerce_after_product_ordering', 'Custom_Layouts\\Integrations\\WooCommerce::after_product_ordering' );
	}
	/**
	 * Record the order change so we can clear CL cache
	 *
	 * @since    1.4.5
	 */
	public static function after_product_ordering() {
		Data::add_post_type_updated( 'product' );
	}
}
