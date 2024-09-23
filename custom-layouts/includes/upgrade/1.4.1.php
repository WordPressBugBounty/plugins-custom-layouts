<?php
namespace Custom_Layouts\Upgrade\v1_4_1;

use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Settings;

/**
 * Parse the settings data, and upgrade where necessary accoring to version nubers
 *
 * @since    1.4.0
 */

add_action( 'custom-layouts/settings/get', 'Custom_Layouts\\Upgrade\\v1_4_1\\upgrade', 10, 2 );

function upgrade( $post_id, $section ) {

	$settings_version = Settings::get_setting_version( $post_id );

	if ( ! version_compare( $settings_version, '1.4.1-beta', '<' ) ) {
		return;
	}

	if ( Settings::is_template( $post_id ) ) {
		$template_settings = Settings::get_settings_data( $post_id, array( 'template-instances', 'template-data' ) );
		upgrade_template( $template_settings, $post_id );
	}
}

function upgrade_template( $template_settings, $template_id ) {

	$template_instances = array();
	if ( isset( $template_settings['template-instances'] ) ) {
		$template_instances = $template_settings['template-instances'];
	}

	$template_attributes = array();
	if ( isset( $template_settings['template-data'] ) ) {
		$template_attributes = $template_settings['template-data'];
	}

	/**
	 * Cleanup the instances by adding new excerpt values
	 * and removing the old values that are no longer in use
	 */
	if ( is_array( $template_instances ) ) {
		foreach ( $template_instances as $instance_id => $instance ) {
			$instance_attributes = $instance['data'];
			$element_type        = $instance['elementId'];

			if ( $element_type === 'excerpt' ) {
				$instance_attributes['excerptTrim']         = 'no';
				$instance_attributes['excerptHideReadMore'] = 'no';
			}
			$template_settings['template-instances'][ $instance_id ]['data'] = $instance_attributes;
		}
	}

	$template_settings['template-data'] = $template_attributes;

	Settings::update_settings_data( $template_id, $template_settings );
	CSS_Loader::save_css( array( $template_id ) ); // regenerate the CSS
}
