<?php
/**
 * WPML Integration Class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */
namespace Custom_Layouts\Integrations;

/**
 * All WPML integration functionality
 */
class WPML {
	static $has_checked_wpml  = false;
	static $has_wpml          = false;
	static $is_generating_css = false;

	public static function init() {
		add_filter( 'custom-layouts/layout/id', 'Custom_Layouts\\Integrations\\WPML::convert_layout_id', 10 );
		add_filter( 'custom-layouts/template/id', 'Custom_Layouts\\Integrations\\WPML::convert_template_id', 10 );

		add_filter( 'custom-layouts/admin/layout_info', 'Custom_Layouts\\Integrations\\WPML::api_info', 10 );
		add_filter( 'custom-layouts/admin/template_info', 'Custom_Layouts\\Integrations\\WPML::api_info', 10 );
		add_action( 'custom-layouts/settings/get_templates_options', 'Custom_Layouts\\Integrations\\WPML::options_switch_language', 10 );
		add_action( 'custom-layouts/settings/get_layouts_options', 'Custom_Layouts\\Integrations\\WPML::options_switch_language', 10 );

		add_action( 'custom-layouts/settings/templates', 'Custom_Layouts\\Integrations\\WPML::add_templates_args', 10 );

		/**
		 * Filter the queryies used to get a list of templates / layouts - usually this means
		 * letting WPML add its current language restriction
		 */
		add_filter( 'custom-layouts/settings/layouts/args', 'Custom_Layouts\\Integrations\\WPML::restrict_query_args', 10 );
		add_filter( 'custom-layouts/settings/templates/args', 'Custom_Layouts\\Integrations\\WPML::restrict_query_args', 10 );

		/**
		 * We want to capture when the CSS is being generated, so we don't restrict_query_args on the query
		 * used to fetch templates (we need to generate the CSS for all languages)
		 */
		add_action( 'custom-layouts/css/generate/start', 'Custom_Layouts\\Integrations\\WPML::set_start_css', 10 );
		add_action( 'custom-layouts/css/generate/finish', 'Custom_Layouts\\Integrations\\WPML::set_finish_css', 10 );

		add_filter( 'custom-layouts/settings/set', 'Custom_Layouts\\Integrations\\WPML::update_setting', 10, 3 );
	}

	public static function set_start_css() {
		if ( ! self::has_wpml() ) {
			return;
		}
		self::$is_generating_css = true;
	}
	public static function set_finish_css() {
		if ( ! self::has_wpml() ) {
			return;
		}
		self::$is_generating_css = false;
	}

	/* Change the lang according top the passed param */
	public static function update_setting( $settings, $post_id, $section ) {
		if ( ! self::has_wpml() ) {
			return $settings;
		}
		if ( $section !== 'layout' ) {
			return $settings;
		}

		if ( isset( $settings['template_id'] ) ) {
			// we want to try to convert the template ID to the correct translation value
			$settings['template_id'] = self::convert_template_id( $settings['template_id'] );
		}
		return $settings;
	}

	/* Change the lang according to the passed param */
	public static function options_switch_language( $language ) {
		if ( ! self::has_wpml() || $language == '' ) {
			return;
		}
		do_action( 'wpml_switch_language', $language );
	}
	/* Add WPML data layout info request */
	public static function api_info( $data ) {
		if ( ! self::has_wpml() ) {
			return $data;
		}
		$data['hasWPML']  = self::has_wpml();
		$data['language'] = self::get_current_language();
		return $data;
	}
	/**
	 * Disable suppress_filters so `wpml_switch_lang` will actually have an effect
	 *
	 * @since    1.4.2
	 */
	public static function restrict_query_args( $args ) {
		if ( ! self::has_wpml() ) {
			return $args;
		}
		if ( true === self::$is_generating_css ) {
			// When generating the CSS, we want to include all templates
			$args['suppress_filters'] = true;
		} else {
			// Make sure not to suppress filters (let WPML do its thing)
			$args['suppress_filters'] = false;
		}
		return $args;
	}
	/**
	 * Covert the layout ID the the current language ID (if the translation exists)
	 *
	 * @since    1.4.2
	 */
	public static function convert_template_id( $post_id ) {
		if ( ! self::has_wpml() ) {
			return $post_id;
		}

		return self::convert_post_id( $post_id, 'cl-template' );
	}
	/**
	 * Covert the layout ID the the current language ID (if the translation exists)
	 *
	 * @since    1.4.2
	 */
	public static function convert_layout_id( $post_id ) {
		if ( ! self::has_wpml() ) {
			return $post_id;
		}

		return self::convert_post_id( $post_id, 'cl-layout' );
	}

	/**
	 * Covert the post ID the the current language ID (if the translation exists)
	 *
	 * @since    1.4.2
	 */
	public static function convert_post_id( $post_id, $post_type ) {
		if ( ! self::has_wpml() ) {
			return $post_id;
		}

		// 0 is reserved for the default layout / template
		if ( $post_id === 0 ) {
			return $post_id;
		}

		$translated_id = $post_id;
		$language      = self::get_current_language();
		if ( $language ) {
			$translated_id = self::object_id( $post_id, $post_type, true, $language );
		}
		return $translated_id;
	}

	/**
	 * Check if WPML is enabled or not
	 *
	 * @since    1.4.2
	 */
	public static function has_wpml() {

		if ( ! self::$has_checked_wpml ) {
			self::$has_checked_wpml = true;

			if ( has_filter( 'wpml_object_id' ) ) {
				self::$has_wpml = true;
			}
		}

		return self::$has_wpml;
	}

	public static function get_current_language() {

		if ( ! self::has_wpml() ) {
			return false;
		}

		$current_language_code = apply_filters( 'wpml_current_language', null );

		return $current_language_code;
	}

	public static function object_id( $id = 0, $type = '', $return_original = '', $lang_code = '' ) {
		if ( ! self::has_wpml() ) {
			return $id;
		}
		$lang_id = 0;
		if ( has_filter( 'wpml_object_id' ) ) {
			if ( $lang_code !== '' ) {
				$lang_id = apply_filters( 'wpml_object_id', $id, $type, $return_original, $lang_code );
			} else {
				$lang_id = apply_filters( 'wpml_object_id', $id, $type, $return_original );
			}
		}
		return $lang_id;
	}
}
