<?php
/**
 * Upgrade script for version 1.4.0
 *
 * @package    Custom_Layouts
 * @since      1.4.0
 */

namespace Custom_Layouts\Upgrade\v1_4_0;

use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Settings;

/**
 * Parse the settings data, and upgrade where necessary according to version numbers.
 *
 * @since    1.4.0
 */

add_action( 'custom-layouts/settings/get', 'Custom_Layouts\\Upgrade\\v1_4_0\\upgrade', 10, 1 );

/**
 * Upgrade settings data from versions prior to 1.4.0.
 *
 * @since 1.4.0
 * @param int $post_id The post ID.
 * @return void
 */
function upgrade( $post_id ) {

	$settings_version = Settings::get_setting_version( $post_id );

	if ( ! version_compare( $settings_version, '1.4.0-beta', '<' ) ) {
		return;
	}

	if ( Settings::is_template( $post_id ) ) {
		$template_settings = Settings::get_settings_data( $post_id, array( 'template-instances', 'template-data' ) );
		upgrade_template( $template_settings, $post_id );
	}
}

/**
 * Upgrade template settings to version 1.4.0 format.
 *
 * @since 1.4.0
 * @param array $template_settings The template settings data.
 * @param int   $template_id The template post ID.
 * @return void
 */
function upgrade_template( $template_settings, $template_id ) {

	$template_instances = array();
	if ( isset( $template_settings['template-instances'] ) ) {
		$template_instances = $template_settings['template-instances'];
	}

	$template_attributes = array();
	if ( isset( $template_settings['template-data'] ) ) {
		$template_attributes = $template_settings['template-data'];
	}

	// Cleanup the instances, by upgrading margin, padding + radius to new format
	// and removing the old values that are no longer in use.
	if ( is_array( $template_instances ) ) {
		foreach ( $template_instances as $instance_id => $instance ) {
			$instance_attributes = $instance['data'];
			$element_type        = $instance['elementId'];

			if ( $element_type === 'taxonomy' ) {
				$instance_attributes['termLineHeight'] = '';
			}
			$instance_attributes['lineHeight'] = '';

			$template_settings['template-instances'][ $instance_id ]['data'] = $instance_attributes;
		}
	}

	$template_settings['template-data'] = $template_attributes;

	Settings::update_settings_data( $template_id, $template_settings );
	CSS_Loader::save_css( array( $template_id ) ); // Regenerate the CSS.
}
