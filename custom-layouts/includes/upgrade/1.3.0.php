<?php
namespace Custom_Layouts\Upgrade\v1_3_0;

use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Settings;

/**
 * Parse the settings data, and upgrade where necessary accoring to version nubers
 *
 * @since    1.3.0
 */

add_action( 'custom-layouts/settings/get', 'Custom_Layouts\\Upgrade\\v1_3_0\\upgrade', 10, 2 );

function upgrade( $post_id, $section ) {

	$settings_version = Settings::get_setting_version( $post_id );
	if ( ! version_compare( $settings_version, '1.3.0-beta', '<' ) ) {
		return;
	}
	if ( Settings::is_template( $post_id ) ) {
		$template_settings = Settings::get_settings_data( $post_id, array( 'template-instances', 'template-data' ) );
		upgrade_template( $template_settings, $post_id );
	}
}


function upgrade_template( $template_settings, $template_id ) {
	// then we need to convert the spacing data which now uses the BoxControl
	// -template-instance-order, app-data
	// $template_sections = array( 'template-instances', 'template-data' );

	$template_instances = array();
	if ( isset( $template_settings['template-instances'] ) ) {
		$template_instances = $template_settings['template-instances'];
	}

	$template_attributes = array();
	if ( isset( $template_settings['template-data'] ) ) {
		$template_attributes = $template_settings['template-data'];
	}

	// cleanup the instances, by upgrading margin, padding + radius to new format
	// and removing the old values that are no longer in use
	if ( is_array( $template_instances ) ) {
		foreach ( $template_instances as $instance_id => $instance ) {
			$instance_attributes = $instance['data'];
			$element_type        = $instance['elementId'];

			// add new prop - width mode
			$instance_attributes['widthMode'] = 'full';
			if ( $element_type === 'link' ) {
				$instance_attributes['widthMode'] = 'auto';
			}

			// fix margin / padding / border radius (upgraded them to use BoxControl)
			if ( isset( $instance_attributes['marginSizeCustom'] ) ) {
				$instance_attributes['marginSize'] = array(
					'top'    => $instance_attributes['marginSizeCustom'][0] . 'px',
					'right'  => $instance_attributes['marginSizeCustom'][1] . 'px',
					'bottom' => $instance_attributes['marginSizeCustom'][2] . 'px',
					'left'   => $instance_attributes['marginSizeCustom'][3] . 'px',
				);
				unset( $instance_attributes['marginSizeCustom'] );
				unset( $instance_attributes['marginSizeLocked'] );
			}

			if ( isset( $instance_attributes['paddingSizeCustom'] ) ) {
				$instance_attributes['paddingSize'] = array(
					'top'    => $instance_attributes['paddingSizeCustom'][0] . 'px',
					'right'  => $instance_attributes['paddingSizeCustom'][1] . 'px',
					'bottom' => $instance_attributes['paddingSizeCustom'][2] . 'px',
					'left'   => $instance_attributes['paddingSizeCustom'][3] . 'px',
				);
				unset( $instance_attributes['paddingSizeCustom'] );
				unset( $instance_attributes['paddingSizeLocked'] );
			}

			if ( isset( $instance_attributes['borderRadiusCustom'] ) ) {
				$instance_attributes['borderRadius'] = array(
					'tl' => $instance_attributes['borderRadiusCustom'][0] . 'px',
					'tr' => $instance_attributes['borderRadiusCustom'][1] . 'px',
					'br' => $instance_attributes['borderRadiusCustom'][2] . 'px',
					'bl' => $instance_attributes['borderRadiusCustom'][3] . 'px',
				);
				unset( $instance_attributes['borderRadiusCustom'] );
				unset( $instance_attributes['borderRadiusLocked'] );
			}

			if ( isset( $instance_attributes['layoutData'] ) ) {
				unset( $instance_attributes['layoutData'] );
			}

			$template_settings['template-instances'][ $instance_id ]['data'] = $instance_attributes;
		}
	}

	// now cleanup the template attributes
	if ( isset( $template_attributes['paddingSizeCustom'] ) ) {
		$template_attributes['paddingSize'] = array(
			'top'    => $template_attributes['paddingSizeCustom'][0] . 'px',
			'right'  => $template_attributes['paddingSizeCustom'][1] . 'px',
			'bottom' => $template_attributes['paddingSizeCustom'][2] . 'px',
			'left'   => $template_attributes['paddingSizeCustom'][3] . 'px',
		);
		unset( $template_attributes['paddingSizeCustom'] );
		unset( $template_attributes['paddingSizeLocked'] );
	}

	if ( isset( $template_attributes['borderRadiusCustom'] ) ) {
		$template_attributes['borderRadius'] = array(
			'tl' => $template_attributes['borderRadiusCustom'][0] . 'px',
			'tr' => $template_attributes['borderRadiusCustom'][1] . 'px',
			'br' => $template_attributes['borderRadiusCustom'][2] . 'px',
			'bl' => $template_attributes['borderRadiusCustom'][3] . 'px',
		);
		unset( $template_attributes['borderRadiusCustom'] );
		unset( $template_attributes['borderRadiusLocked'] );
	}

	$template_settings['template-data'] = $template_attributes;

	Settings::update_settings_data( $template_id, $template_settings );
	CSS_Loader::save_css( array( $template_id ) ); // regenerate the CSS
}
