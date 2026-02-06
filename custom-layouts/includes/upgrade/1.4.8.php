<?php
/**
 * Upgrade script for version 1.4.8
 *
 * @package    Custom_Layouts
 * @since      1.4.8
 */

namespace Custom_Layouts\Upgrade\v1_4_8;

use Custom_Layouts\Settings;

/**
 * Parse the settings data, and upgrade where necessary according to version numbers.
 *
 * @since    1.4.8
 */

add_action( 'custom-layouts/settings/get', 'Custom_Layouts\\Upgrade\\v1_4_8\\upgrade', 10, 1 );

/**
 * Upgrade settings data from versions prior to 1.4.8.
 *
 * @since 1.4.8
 * @param int $post_id The post ID.
 * @return void
 */
function upgrade( $post_id ) {
	$settings_version = Settings::get_setting_version( $post_id );
	if ( ! version_compare( $settings_version, '1.4.8-beta', '<' ) ) {
		return;
	}

	if ( Settings::is_layout( $post_id ) ) {
		$layout_settings = Settings::get_settings_data( $post_id, array( 'layout' ) );
		upgrade_layout( $layout_settings, $post_id );
	}
}

/**
 * Upgrade layout settings to version 1.4.8 format.
 *
 * @since 1.4.8
 * @param array $layout_settings The layout settings data.
 * @param int   $layout_id The layout post ID.
 * @return void
 */
function upgrade_layout( $layout_settings, $layout_id ) {
	if ( ! isset( $layout_settings['layout'] ) ) {
		return;
	}
	$layout_data = $layout_settings['layout'];

	if ( isset( $layout_data['item_spacing'] ) ) {
		// Convert the old item spacing to grid gap measurements and margin.
		$spacing_gap                = ( absint( $layout_data['item_spacing'] ) * 2 ) . 'px';
		$margin                     = absint( $layout_data['item_spacing'] ) . 'px';
		$grid_gap                   = array(
			'column' => $spacing_gap,
			'row'    => $spacing_gap,
		);
		$layout_data['grid_gap']    = $grid_gap;
		$layout_data['margin_size'] = array(
			'top'    => $margin,
			'right'  => $margin,
			'bottom' => $margin,
			'left'   => $margin,
		);

		unset( $layout_data['item_spacing'] );
	}
	$layout_settings['layout'] = $layout_data;
	Settings::update_settings_data( $layout_id, $layout_settings );
}
