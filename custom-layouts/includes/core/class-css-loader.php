<?php

namespace Custom_Layouts\Core;

use Custom_Layouts\Settings;
use Custom_Layouts\Template\Controller as Template_Controller;

/**
 * Fired during plugin activation
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CSS_Loader {

	private static function generate_css( $regenerate_ids = array() ) {
		// load in regular stylesheet (and concatenate)

		do_action( 'custom-layouts/css/generate/start' );
		$base_css = '';
		ob_start();
		require_once trailingslashit( CUSTOM_LAYOUTS_PATH ) . 'assets/css/frontend/custom-layouts.css';
		$base_css = ob_get_clean();
		$css      = self::clean_css( $base_css );
		// TODO - this is not working when in DEV mode, it seems to strip all the template code (source map issue?)

		$css     .= self::get_layout_css();
		// loop through templates, and build their CSS
		// TODO - cache the CSS for each template, and only rebuild that particular template CSS

		$default_template_data = Settings::get_default_template();
		$css                  .= self::get_package_css( 'default', 'Default', $default_template_data );

		$templates = Settings::get_templates();

		if ( count( $templates ) > 0 ) {
			foreach ( $templates as $template ) {
				$template_css = self::get_template_css( $template, $regenerate_ids );
				$css         .= $template_css;
			}
		}
		do_action( 'custom-layouts/css/generate/finish' );
		return $css;
	}
	// for now just generates the responsive classes + breakpoints
	public static function get_layout_css() {

		$breakpoints = Settings::get_option( 'breakpoints' );

		$css = '';
		// todo - implement grid css instead of all of this
		$css .= '@media only screen and (min-width: 0px){';
		$css .= self::get_layout_cols( 12, 'xs' );
		$css .= '}';

		$css .= '@media only screen and (min-width: ' . absint( $breakpoints['xsmall'] ) . 'px){';
		$css .= self::get_layout_cols( 12, 's' );
		$css .= '}';

		$css .= '@media only screen and (min-width: ' . absint( $breakpoints['small'] ) . 'px){';
		$css .= self::get_layout_cols( 12, 'm' );
		$css .= '}';

		$css .= '@media only screen and (min-width: ' . absint( $breakpoints['medium'] ) . 'px){';
		$css .= self::get_layout_cols( 12, 'l' );
		$css .= '}';

		return $css;
	}
	private static function get_layout_cols( $columns, $size_id ) {

		$css        = '';
		$base_class = '.cl-layout';

		for ( $column = 1; $column <= $columns; $column++ ) {
			$col_class     = $base_class . '.cl-layout--col-' . sanitize_key( $size_id ) . '-' . $column . ' .cl-layout__item';
			$percent_width = self::round_down( 100 / $column, 4 ); // round_down casts to float
			$css          .= $col_class . '{width:calc(' . $percent_width . '% - var( --cl-layout-gap-c ));flex-basis:calc(' . $percent_width . '% - var( --cl-layout-gap-c ));}';
		}

		return $css;
	}


	private static function round_down( $value, $precision ) {
		$value     = (float) $value;
		$precision = (int) $precision;
		if ( $precision < 0 ) {
			$precision = 0;
		}

		$decPointPosition = strpos( $value, '.' );
		if ( $decPointPosition === false ) {
			return $value;
		}
		return (float) ( substr( $value, 0, $decPointPosition + $precision + 1 ) );
	}
	public static function get_template_css( $template_arg, $regenerate_ids = array() ) {

		$css = '';
		if ( is_scalar( $template_arg ) ) {
			// then it is the ID
			$template_id = absint( $template_arg );
			$template    = get_post( $template_id );
		} elseif ( is_object( $template_arg ) ) {
			$template    = $template_arg;
			$template_id = $template->ID;
		} else {
			return '';
		}

		// so regenerate if needed
		$should_regenerate = false;
		if ( empty( $regenerate_ids ) || in_array( $template_id, $regenerate_ids ) ) {
			$should_regenerate = true;
		}

		if ( ! $should_regenerate ) {
			// if no cached version found
			$cached_template_css = get_post_meta( $template_id, 'custom-layouts-template-css', true );
			if ( $cached_template_css ) {
				$css  = '/* Template: ' . esc_html( $template->post_title ) . " */\r\n";
				$css .= self::clean_css( $cached_template_css );
			} else {
				// force generation
				$should_regenerate = true;
			}
		}

		if ( $should_regenerate ) {
			$template_data = Settings::get_template_data( $template_id );
			$css           = self::get_package_css( $template_id, $template->post_title, $template_data );
			delete_post_meta( $template_id, 'custom-layouts-template-css' );
			update_post_meta( $template_id, 'custom-layouts-template-css', $css );
		}

		return $css;
	}
	private static function esc_n_strip( $input ) {
		return esc_html( wp_strip_all_tags( $input ) );
	}
	private static function clean_css( $css ) {
		$css = wp_strip_all_tags( $css );
		$css = preg_replace( '/\/\*((?!\*\/).)*\*\//', '', $css );
		$css = preg_replace( '/\s{2,}/', ' ', $css );
		$css = preg_replace( '/\s*([:;{}])\s*/', '$1', $css );
		$css = preg_replace( '/;}/', '}', $css );
		return $css;
	}
	public static function parse_css_settings( $settings ) {
		$css = '';

		foreach ( $settings as $setting_name => $setting_value ) {
			$css .= self::parse_css_setting( $setting_name, $setting_value );
		}
		return $css;
	}
	public static function parse_css_setting( $property_name, $property_data ) {
		$unit = 'px';

		$is_empty = false;
		if ( is_scalar( $property_data ) ) {
			// we want to allow "nullish" values
			if ( trim( $property_data ) === '' ) {
				$is_empty = true;
			}
		} elseif ( empty( $property_data ) ) {
			$is_empty = true;
		}

		if ( $is_empty ) {
			return '';
		}
		switch ( $property_name ) {
			case 'textColor':
				if ( is_scalar( $property_data ) ) {
					return 'color: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'backgroundColor':
				if ( is_scalar( $property_data ) ) {
					return 'background-color: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'fill':
				if ( is_scalar( $property_data ) ) {
					return 'fill: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'backgroundGradient':
				if ( is_scalar( $property_data ) ) {
					return 'background-image: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'backgroundPosition':
				if ( is_scalar( $property_data ) ) {
					return 'background-position: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'backgroundRepeat':
				if ( is_scalar( $property_data ) ) {
					return 'background-repeat: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'backgroundSize':
				if ( is_scalar( $property_data ) ) {
					return 'background-size: ' . sanitize_key( $property_data, true ) . ';';
				}
				break;
			case 'width':
				if ( is_scalar( $property_data ) ) {
					return 'width: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'maxWidth':
				if ( is_scalar( $property_data ) ) {
					return 'max-width: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'height':
				if ( is_scalar( $property_data ) ) {
					return 'height: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'lineHeight':
				if ( is_scalar( $property_data ) ) {
					return 'line-height: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'display':
				if ( is_scalar( $property_data ) ) {
					return 'display: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'position':
				if ( is_scalar( $property_data ) ) {
					return 'position: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'top':
				if ( is_scalar( $property_data ) ) {
					return 'top: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'left':
				if ( is_scalar( $property_data ) ) {
					return 'left: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'right':
				if ( is_scalar( $property_data ) ) {
					return 'right: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'bottom':
				if ( is_scalar( $property_data ) ) {
					return 'bottom: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'flex':
				if ( is_scalar( $property_data ) ) {
					return 'flex: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'flexGrow':
				if ( is_scalar( $property_data ) ) {
					return 'flex-grow: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'flexShrink':
				if ( is_scalar( $property_data ) ) {
					return 'flex-shrink: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			// case 'borderRadius':
				// return 'border-radius-color: ' . self::esc_n_strip( $property_value, true);
				// break;
			case 'borderRadius':
				$property_value = self::parse_unit_quad( $property_data );

				return 'border-radius: ' . self::esc_n_strip( $property_value ) . ';';
				break;
			case 'paddingSize':
					$property_value = self::parse_unit_quad( $property_data );
					// $property_value = implode( ' ', $property_vals );
				return 'padding: ' . self::esc_n_strip( $property_value ) . ';';
					break;
			case 'padding':
				if ( is_scalar( $property_data ) ) {
					return 'padding: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'paddingTop':
				if ( is_scalar( $property_data ) ) {
					return 'padding-top: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'paddingRight':
				if ( is_scalar( $property_data ) ) {
					return 'padding-right: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'paddingBottom':
				if ( is_scalar( $property_data ) ) {
					return 'padding-bottom: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'paddingLeft':
				if ( is_scalar( $property_data ) ) {
					return 'padding-left: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'margin':
				if ( is_scalar( $property_data ) ) {
					return 'margin: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'marginTop':
				if ( is_scalar( $property_data ) ) {
					return 'margin-top: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'marginRight':
				if ( is_scalar( $property_data ) ) {
					return 'margin-right: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'marginBottom':
				if ( is_scalar( $property_data ) ) {
					return 'margin-bottom: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'marginLeft':
				if ( is_scalar( $property_data ) ) {
					return 'margin-left: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'marginSize':
				$property_value = self::parse_unit_quad( $property_data );
				// $property_value = implode( ' ', $property_vals );
				return 'margin: ' . self::esc_n_strip( $property_value ) . ';';
				break;
			case 'borderWidth':
				if ( is_scalar( $property_data ) ) {
					return 'border-width: ' . self::esc_n_strip( $property_data, true ) . 'px;'; // TODO - we should probably add this as a prop in the JS app if the width has been set.
				}
				break;
			case 'borderColor':
				if ( is_scalar( $property_data ) ) {
					return 'border-color: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'align':
				if ( is_scalar( $property_data ) ) {
					return 'text-align: ' . sanitize_key( $property_data, true ) . ';';
				}
				break;
			case 'alignSelf':
				if ( is_scalar( $property_data ) ) {
					return 'align-self: ' . sanitize_key( $property_data, true ) . ';';
				}
				break;
			case 'alignItems':
				if ( is_scalar( $property_data ) ) {
					return 'align-items: ' . sanitize_key( $property_data, true ) . ';';
				}
				break;
			case 'justifyContent':
				if ( is_scalar( $property_data ) ) {
					return 'justify-content: ' . sanitize_key( $property_data, true ) . ';';
				}
				break;
			case 'fontSize':
				if ( is_scalar( $property_data ) ) {
					// $fontSize = '27.2';
					/*
					if ( 'small' === $property_data ) {

					}
					if ( 'medium' === $property_data ) {

					}
					if ( 'large' === $property_data ) {

					}*/

					if ( $property_data !== '' ) {
						return 'font-size: ' . sanitize_key( $property_data ) . 'px;';
					}
				}
				break;
			case 'fontSizeCustom':
				if ( is_scalar( $property_data ) ) {
					return 'font-size: ' . sanitize_key( $property_data . $unit, true ) . ';';
				}
				break;
			case 'fontFamily':
				if ( is_scalar( $property_data ) && ! empty( $property_data ) ) {
					if ( $property_data === 'default' ) {
						return '';
					}
					return 'font-family: ' . self::esc_n_strip( $property_data, true ) . ';';
				}
				break;
			case 'fontFormatBold':
				if ( is_scalar( $property_data ) ) {
					$font_weight = 'normal';
					if ( 'yes' === $property_data ) {
						$font_weight = 'bold';
					}
					return 'font-weight:' . self::esc_n_strip( $font_weight ) . ';';
				}
				break;
			case 'fontFormatItalic':
				if ( is_scalar( $property_data ) ) {
					$font_style = 'normal';
					if ( 'yes' === $property_data ) {
						$font_style = 'italic';
					}
					return 'font-style: ' . self::esc_n_strip( $font_style ) . ';';
				}
				break;
			case 'fontFormatUnderline':
				if ( is_scalar( $property_data ) ) {
					$text_decoration = 'none';
					if ( 'yes' === $property_data ) {
						$text_decoration = 'underline';
					}
					return 'text-decoration: ' . self::esc_n_strip( $text_decoration ) . ';';
				}
				break;
			default:
				break;
		}
	}
	public static function parse_unit_quad( $property_data ) {

		$property_value = '0px';

		if ( is_array( $property_data ) && count( $property_data ) === 4 ) {
			$property_vals = array();
			foreach ( $property_data as $data ) {

				if ( empty( $data ) ) {
					$data = '0';
				}

				array_push( $property_vals, sanitize_text_field( $data ) );
			}
			$property_value = implode( ' ', $property_vals );

			if ( count( array_unique( $property_data ) ) === 1 ) {
				$property_value = $property_vals[0];
			}
		}
		return $property_value;
	}
	public static function parse_unit( $value, $unit ) {
		return intval( $value ) . sanitize_text_field( $unit );
	}
	private static function get_template_background_css( $template ) {
		// TODO - do this when the user sets an image from the media library
		/*
		$show_featured_image = $template['showFeaturedImage'];
		if ( 'yes' === $show_featured_image ) {
			$image_postion = $template['imagePosition'];
			$image_size = $template['imageSourceSize'];

			$attachment_meta = Util::get_image_by_size( $post_attachment_id, $image_size );
			if ( $attachment_meta ) {
				$image_data[ $size_name ] = $attachment_meta;
			}

		}*/
	}
	private static function get_package_css( $template_id, $template_name, $template_data ) {
		$css = '';
		// $template_id = $template->ID;
		$css .= "\r\n/* Template: " . esc_html( $template_name ) . " */\r\n";

		// add template specific CSS:
		$template_class = self::get_template_class( $template_id );

		if ( $template_data ) {
			$css .= $template_class . '{';
			$css .= self::parse_css_settings( $template_data['template'] );
			$css .= '}';

			// now add the instances CSS
			if ( isset( $template_data['instances'] ) && is_array( $template_data['instances'] ) ) {
				foreach ( $template_data['instances'] as $instance ) {
					$css .= self::get_instance_css( $instance, $template_id, $template_data['template'] );
				}
			}
		}
		return $css;
	}
	private static function get_template_class( $template_id ) {
		return '.cl-template--id-' . intval( $template_id );
	}

	private static function get_element_class( $element_id ) {
		return '.cl-element-' . esc_attr( $element_id );
	}


	private static function get_instance_css( $instance, $template_id, $template ) {
		$template_controller = new Template_Controller( $template_id );
		$element_id          = $instance['elementId'];
		$html_tag            = isset( $instance['data']['htmlTag'] ) ? Validation::esc_html_tag( $instance['data']['htmlTag'] ) : 'div';
		$template_class      = self::get_template_class( $template_id );
		$css                 = '';

		// if the element provides its own css, then use that, otherwise
		// just fallback and use the default processings
		$element = $template_controller->element( $element_id );
		if ( ! $element ) {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'An element with the ID "%1$s" was not found / registered.', 'custom-layouts' ), $element_id ), '1.3.0' );
			return $css;
		}
		$instance_css = $element->get_css( $instance, $template_class, $template );
		if ( $instance_css ) {
			$css .= $instance_css;
		}
		return $css;
	}

	public static function save_css( $regenerate_ids = array() ) {
		$css = self::generate_css( $regenerate_ids );

		// Stash CSS in uploads directory
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php'; // We will probably need to load this file
		}
		$upload_dir = wp_upload_dir(); // Grab uploads folder array
		$cl_dir     = trailingslashit( $upload_dir['basedir'] ) . 'custom-layouts/'; // Set storage directory path

		global $wp_filesystem;
		// Try to init the file system and watch out for the return value of 'false' or 'null'
		$is_filesystem_ready = WP_Filesystem();
		if ( $is_filesystem_ready !== true ) {
			return;
		}

		$filesystem = $wp_filesystem;
		$dir_exists = $filesystem->exists( $cl_dir );
		if ( ! $dir_exists ) {
			$mk_dir_result = $filesystem->mkdir( $cl_dir ); // Try to create the folder
			if ( ! $mk_dir_result ) {
				// log error
				if ( defined( 'WP_DEBUG' ) ) {
					if ( WP_DEBUG === true ) {
						error_log( esc_html__( 'Custom Layouts: Unable to write folder, info:', 'custom-layouts' ) );
						error_log( esc_html( $filesystem->errors->get_error_message() ) );
					}
				}
			} else {
				$dir_exists = true;
			}
		}

		$created_file = false;
		if ( $dir_exists ) {
			$file_permission = 0644;
			if ( defined( 'FS_CHMOD_FILE' ) ) {
				$file_permission = FS_CHMOD_FILE;
			}
			$file_result = $filesystem->put_contents( $cl_dir . 'style.css', $css, $file_permission ); // Finally, store the file
			if ( $file_result ) {
				// save in an option if this method is successful
				$created_file = true;
			} else {
				$created_file = false;
				if ( defined( 'WP_DEBUG' ) ) {
					if ( WP_DEBUG === true ) {
						error_log( esc_html__( 'Custom Layouts: Unable to write file, info:', 'custom-layouts' ) );
						error_log( esc_html( $filesystem->errors->get_error_message() ) );
					}
				}
			}
		}

		if ( $created_file ) {
			// all good, use the CSS file
			self::set_mode( 'file-system' );
		} else {
			// then we need to switch to generating via ajax
			// self::set_mode( 'admin-ajax' );

			// then we need to switch to generating inline
			self::set_mode( 'inline' );
		}

		self::set_version_id(); // update the ID so the request won't be cached
	}

	private static function set_mode( $mode ) {
		update_option( 'custom_layouts_css_mode', sanitize_key( $mode ), false );
	}

	private static function set_version_id() {
		$version_id = absint( get_option( 'custom_layouts_css_version_id' ) );
		$version_id++;
		// I guess we don't want this number to grow forever, so when it hits 1000 reset it
		if ( $version_id === 1000 ) {
			$version_id = 1;
		}
		update_option( 'custom_layouts_css_version_id', absint( $version_id ), false );
	}
	public static function get_version( $plugin_version = -1 ) {
		$version = 0;
		if ( 'file-system' === self::get_mode() ) {
			$version = absint( get_option( 'custom_layouts_css_version_id' ) );
		} elseif ( $plugin_version ) {
			$version = $plugin_version;
		}
		return $version;
	}

	public static function get_mode() {
		return get_option( 'custom_layouts_css_mode' );
	}

	/**
	 * Returns a url to the static CSS file, or url to an ajax action for generating
	 * the CSS on the fly
	 *
	 * @since    1.0.0
	 */
	private static function uploads_url() {
		$upload_dir = wp_get_upload_dir();
		$upload_url = $upload_dir['baseurl'];
		if ( is_ssl() ) {
			return str_replace( 'http://', 'https://', $upload_url );
		}
		return str_replace( 'https://', 'http://', $upload_url );
	}
	public static function get_css_url() {
		if ( 'file-system' === self::get_mode() ) {
			$upload_dir = wp_get_upload_dir();
			$url        = trailingslashit( self::uploads_url() ) . 'custom-layouts/style.css';
			return $url;
		} elseif ( 'admin-ajax' === self::get_mode() ) {
			// we don't want to generate via admin-ajax anymore... it's bad
			// return add_query_arg( 'action', 'custom_layouts_css', admin_url( 'admin-ajax.php' ) );
			// so load the base CSS styles ( and only load the others inline where used )
		} elseif ( 'inline' === self::get_mode() ) {
			// so load the base CSS styles ( and only load the others inline where used ).
			return trailingslashit( CUSTOM_LAYOUTS_URL ) . 'assets/css/frontend/custom-layouts.css';
		} else {
			// it looks like it's not been initialised yet - so attempt to setup
			// should be done already by plugin activation so leave commented
			// self::save_css();
		}
	}
}


