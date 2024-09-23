<?php
/**
 * Settings Management Class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */

namespace Custom_Layouts;

use Custom_Layouts\Settings\Grid as Grid_Settings;
use Custom_Layouts\Settings\Query as Query_Settings;
use Custom_Layouts\Settings\Setting;

/**
 * The file that defines interactions with S&F settings and edit pages
 *
 * @link       https://codeamp.com
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keep track of all settings loaded, values etc, for specific search forms, and the global settings
 * Provide an API for modifying externally
 */
class Settings {

	/**
	 * Keep track of whether the settings have been registered yet or not
	 *
	 * @var boolean
	 */
	private static $registered_settings = false;

	/**
	 * Keeps track of all settings registered
	 *
	 * @var array
	 */
	private static $settings = array(
		'query'  => array(),
		'layout' => array(),
	);

	/**
	 * Keeps track of the data value of each setting
	 *
	 * @var array
	 */
	private static $data = array(
		// 'query' => array(),
		// 'layout'  => array(),
	);

	/**
	 * Keeps track of the order of the settings
	 *
	 * @var array
	 */
	private static $settings_order = array(
		'query'  => array(),
		'layout' => array(),
	);

	/**
	 * Register built in settings
	 *
	 * @since    1.0.0
	 */
	public static function register() {

		if ( ! self::$registered_settings ) {

			global $post;
			if ( ! is_object( $post ) ) {
				$post_id = $post->ID;
				self::set_settings_data( $post_id, 'layout' );
				self::set_settings_data( $post_id, 'query' );
			}

			self::register_settings_section( 'layout' );
			self::register_settings_section( 'query' );

			do_action( 'custom-layouts/settings/register' );

			self::$registered_settings = true;
		}
	}

	/**
	 * Registers the built in settings for a settings area
	 *
	 * @param string $section  The section name to register.
	 *
	 * @since    1.0.0
	 */
	private static function register_settings_section( $section ) {

		if ( ! empty( self::$settings[ $section ] ) ) {
			return;
		}

		// get the initial data.
		if ( 'layout' === $section ) {
			$settings_data = Grid_Settings::get_data();
		} elseif ( 'query' === $section ) {
			$settings_data = Query_Settings::get_data();
		}/*
		elseif ( 'global' === $section ) {
			$settings_data = Global_Settings::get_data();
		} */

		self::process_settings_data( $settings_data, $section );
	}

	/**
	 * Takes the raw settings data and registers it
	 *
	 * @param array  $settings_data    The data to be stored as settings.
	 * @param string $setting_section  The section name to register the settings to.
	 *
	 * @since    1.0.0
	 */
	private static function process_settings_data( $settings_data, $setting_section ) {

		foreach ( $settings_data as $setting_data ) {
			$args = array(
				'setting' => $setting_data,
				'section' => $setting_section,
			);
			self::register_setting( $args );
		}
	}
	/**
	 * Registers an individual setting
	 *
	 * @param array $args  The config for a setting.
	 *
	 * @since    1.0.0
	 */
	public static function register_setting( $args ) {

		if ( ( ! isset( $args['setting'] ) ) || ( ! isset( $args['section'] ) ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'You must supply an "setting" and "section" argument', 'custom-layouts' ), '1.0.0' );
			return false;
		}

		if ( ! isset( $args['setting']['name'] ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Setting must have a name', 'custom-layouts' ), '1.0.0' );
			return false;
		}

		if ( true === self::$registered_settings ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'You can only register settings inside the action "custom-layouts/settings/register"', 'custom-layouts' ), '1.0.0' );
			return false;
		}

		$section = $args['section'];
		$name    = $args['setting']['name'];

		if ( ! isset( self::$settings[ $section ] ) ) {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'The section "%1$s" does not exist', 'custom-layouts' ), $section ), '1.0.0' );
			return false;
		}

		if ( isset( self::$settings[ $section ][ $name ] ) ) {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'A setting with the name "%1$s" already exists', 'custom-layouts' ), $name ), '1.0.0' );
			return false;
		}

		$args['setting'] = apply_filters( "custom-layouts/settings/setting/{$name}", $args['setting'], $section );

		self::$settings[ $section ][ $name ] = new Setting( $args['setting'] );

		// Now deal with order.
		if ( isset( $args['after'] ) ) {
			// If "after" is set, position it directly after the named setting.
			$after_name = $args['after'];
			// lookup the setting by its property - "name" - to get its position.
			$found_position = array_search( $after_name, self::$settings_order[ $section ], true );

			if ( false !== $found_position ) {
				$insert_position = $found_position + 1;
				// Make sure the insert position is before or at the end of the array.
				if ( ( $insert_position <= count( self::$settings_order[ $section ] ) ) && ( $insert_position >= 0 ) ) {
					// Then splice it in.
					array_splice( self::$settings_order[ $section ], $insert_position, 0, array( $name ) );
				}
			}
		} elseif ( isset( $args['before'] ) ) {
			// TODO
		} else {
			array_push( self::$settings_order[ $section ], $name );
		}
	}

	/**
	 * Gets a setting by name and section
	 *
	 * @param string $name     The setting name.
	 * @param string $section  The settings section.
	 *
	 * @since    1.0.0
	 */
	public static function get_setting( $name, $section ) {
		// Find the option by name.

		if ( ! isset( self::$settings[ $section ] ) ) {
			// Translators: TODO.
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'The section "%1$s" does not exist', 'custom-layouts' ), $section ), '1.0.0' );
			return false;
		}

		if ( isset( self::$settings[ $section ][ $name ] ) ) {
			return self::$settings[ $section ][ $name ];
		}

		return false;
	}

	/**
	 * Updates a setting by name and section
	 *
	 * @param string $name     The setting name.
	 * @param string $section  The settings section.
	 * @param array  $args     The new setting values.
	 *
	 * @since    1.0.0
	 */
	public static function update_setting( $name, $section, $args ) {

		if ( ! isset( self::$settings[ $section ] ) ) {
			// Translators: TODO.
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'The section "%1$s" does not exist', 'custom-layouts' ), $section ), '1.0.0' );
			return false;
		}

		if ( ! isset( self::$settings[ $section ][ $name ] ) ) {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'A setting with the name "%1$s" does not exist', 'custom-layouts' ), $name ), '1.0.0' );
			return false;
		}

		self::$settings[ $section ][ $name ]->update( $args );

		return true;
	}

	/**
	 * Gets a group of settings by section.
	 *
	 * @param string $section       The settings section.
	 * @param bool   $return_array  Return as numeric array.
	 *
	 * @return mixed  An array of settings
	 *
	 * @since    1.0.0
	 */
	public static function get_settings_by_section( $section, $return_array = false ) {

		if ( ! $return_array ) {
			if ( empty( self::$settings[ $section ] ) ) {
				self::register_settings_section( $section );
			}
			return self::$settings[ $section ];
		} else {
			$settings_array = array();
			// Then return the settings as array, ordered.
			foreach ( self::$settings_order[ $section ] as $setting_name ) {
				array_push( $settings_array, self::$settings[ $section ][ $setting_name ]->get_array() );
			}
			return $settings_array;
		}
	}

	public static function get_settings_defaults( $sections ) {

		// TODO - store the defaults in a "flatter" array for re-using
		// or don't... if someone uses a filter to modify a default,
		// copying the default will make it out of sync, so lets always
		// call dynamically using `get_default()`
		$defaults = array();
		foreach ( $sections as $section ) {
			$settings_section = self::get_settings_by_section( $section );

			foreach ( $settings_section as $setting_name => $setting ) {
				$defaults[ $setting_name ] = $setting->get_default();
			}
		}
		return $defaults;
	}

	/**
	 * Gets the data post data for a specific setting
	 *
	 * @param string $setting_name  The name of the setting.
	 * @param string $section       The settings section.
	 * @param int    $post_id       Return as numeric array.
	 *
	 * @return mixed  An array of settings
	 *
	 * @since    1.0.0
	 */
	public static function get_setting_data( $setting_name, $section, $post_id ) {

		$section_data = self::get_section_data( $post_id, $section );
		if ( empty( $section_data ) ) {
			return false;
		}

		return $section_data[ $setting_name ];
	}

	/**
	 * Generates data for a settings section
	 *
	 * @param string $section  The settings section.
	 * @param int    $post_id  Return as numeric array.
	 *
	 * @since    1.0.0
	 */
	public static function get_section_data( $post_id, $section ) {

		if ( empty( self::$data[ $post_id ] ) ) {
			self::$data[ $post_id ] = array();
		}

		// needs to be after initial set..
		do_action( 'custom-layouts/settings/get', $post_id, $section );

		if ( ! isset( self::$data[ $post_id ][ $section ] ) ) {
			self::set_settings_data( $post_id, $section );
		}

		$value = array();
		if ( isset( self::$data[ $post_id ][ $section ] ) ) {
			$value = self::$data[ $post_id ][ $section ];
		}

		// $value = apply_filters( 'custom-layouts/settings/get', $post_id, $section, $value );

		return $value;
	}

	/**
	 * Gets the local settings data for a post_id
	 * current only spports templates
	 *
	 * @param int $post_id  Return as numeric array.
	 *
	 * @since    1.0.0
	 */
	public static function get_settings_data( $post_id, $sections = array() ) {

		if ( ! isset( self::$data[ $post_id ] ) ) {
			self::$data[ $post_id ] = array();
		}
		if ( ! empty( $sections ) ) {
			foreach ( $sections as $section ) {
				self::set_settings_data( $post_id, $section );
			}
		}
		return isset( self::$data[ $post_id ] ) ? self::$data[ $post_id ] : array();
	}

	/**
	 * Updates the settings for a post (template/layout)
	 *
	 * @param string $section  The settings section.
	 * @param int    $post_id  Return as numeric array.
	 *
	 * @since    1.3.0
	 */
	public static function update_settings_data( $post_id, $settings ) {

		// update local data
		self::$data[ $post_id ] = $settings;

		// copy that to the DB
		foreach ( $settings as $section_name => $section ) {
			$section_clean = Util::deep_clean( $section );
			update_post_meta( $post_id, 'custom-layouts-' . $section_name, $section_clean );
		}

		// add current version
		update_post_meta( $post_id, 'custom-layouts-version', CUSTOM_LAYOUTS_VERSION );
	}

	public static function get_setting_version( $post_id ) {
		$version = get_post_meta( $post_id, 'custom-layouts-version', true );
		if ( $version === '' ) {
			$version = '0.0.0';
		}
		return $version;
	}
	public static function is_template( $post_id ) {
		$post_type = get_post_type( $post_id );
		return $post_type === 'cl-template';
	}
	public static function is_layout( $post_id ) {
		$post_type = get_post_type( $post_id );
		return $post_type === 'cl-layout';
	}
	/**
	 * Gets the setting values from and sets them to vars
	 *
	 * @param int    $post_id  Return as numeric array.
	 * @param string $section  The settings section.
	 *
	 * @since    1.0.0
	 */
	private static function set_settings_data( $post_id, $section ) {

		global $post;
		if ( ( 0 === $post_id ) && ( is_object( $post ) ) ) {
			$post_id = $post->ID;
		}
		do_action( 'custom-layouts/settings/set/before', $post_id, $section );
		$post_id                            = absint( $post_id );
		self::$data[ $post_id ][ $section ] = get_post_meta( $post_id, "custom-layouts-$section", true );
		self::$data[ $post_id ][ $section ] = apply_filters( 'custom-layouts/settings/set', self::$data[ $post_id ][ $section ], $post_id, $section );
		do_action( 'custom-layouts/settings/set/after', $post_id, $section );
	}

	public static function get_layouts_data() {

		$layout_posts = self::get_layouts();
		$layouts      = array();
		foreach ( $layout_posts as $layout ) {
			$layout_id   = $layout->ID;
			$layout_data = array(
				'id'     => $layout_id,
				'title'  => get_the_title( $layout ),
				'query'  => self::get_section_data( $layout_id, 'query' ),
				'layout' => self::get_section_data( $layout_id, 'layout' ),
			);

			array_push( $layouts, $layout_data );
		}
		return $layouts;
	}
	/* get a list of all layout value/label pairs for select fields  */
	public static function get_layouts_options( $language = '' ) {
		do_action( 'custom-layouts/settings/get_layouts_options', $language );
		$layout_posts = self::get_layouts();
		$layouts      = array();
		foreach ( $layout_posts as $layout ) {
			$layout_id   = $layout->ID;
			$layout_data = array(
				'value' => $layout_id,
				'label' => get_the_title( $layout ),
			);

			array_push( $layouts, $layout_data );
		}
		return $layouts;
	}
	/* get a list of all layout value/label pairs for select fields  */
	public static function get_templates_options( $language = '' ) {
		do_action( 'custom-layouts/settings/get_templates_options', $language );
		$template_posts = self::get_templates();
		$templates      = array();
		foreach ( $template_posts as $template ) {
			$template_id    = $template->ID;
			$template_title = get_post_field( 'post_title', $template );
			$template_data  = array(
				'value' => $template_id,
				'label' => $template_title === '' ? __( '(no title)', 'custom-layouts' ) : $template_title,
			);

			array_push( $templates, $template_data );
		}
		return $templates;
	}

	// TODO - this stuff needs to be moved into its own class, or the wp-data class?
	/* get a list of all layout value/label pairs for select fields  */
	public static function get_layouts() {
		$args         = array(
			'post_type'      => 'cl-layout',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		$args         = apply_filters( 'custom-layouts/settings/layouts/args', $args );
		$layout_posts = get_posts( $args );
		return $layout_posts;
	}
	/* get a list of all layout value/label pairs for select fields  */
	public static function get_templates() {

		// TODO - keep a reference to results, so we don't run the query repeatedly
		$args = array(
			'post_type'      => 'cl-template',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$args           = apply_filters( 'custom-layouts/settings/templates/args', $args );
		$template_posts = get_posts( $args );
		return $template_posts;
	}
	/* TODO - this stuff needs to be moved out of settings, some sort of wp-data wrapper */
	public static function get_taxonomies() {

		$args = array(
			'public' => true,
		);

		$output        = 'objects';
		$operator      = 'and';
		$wp_taxonomies = get_taxonomies( $args, $output, $operator );

		$taxonomies = array();
		// only use the data we want
		foreach ( $wp_taxonomies as $taxonomy ) {

			$taxonomy_data = array(
				'name'  => $taxonomy->name,
				'label' => $taxonomy->label,

			);

			$taxonomies[ $taxonomy->name ] = $taxonomy_data;
		}
		return $taxonomies;
	}


	public static function get_taxonomies_w_archive() {

		$args = array(
			// 'public'   => true,
		);

		$output   = 'objects';
		$operator = 'and';

		$wp_taxonomies = get_taxonomies( $args, $output, $operator );

		$taxonomies = array();
		if ( $wp_taxonomies ) {
			foreach ( $wp_taxonomies  as $taxonomy ) {
				// Taxonomies need to be public, and have a query var (if rewrite is disabled) or have rewrites enabled to have an archive.
				if ( ( $taxonomy->public ) && ( ( $taxonomy->query_var ) || ( $taxonomy->rewrite ) ) ) {
					$item          = array();
					$item['value'] = $taxonomy->name;
					$item['label'] = $taxonomy->label . ' (' . $taxonomy->name . ')';
					array_push( $taxonomies, $item );
				}
			}
		}

		return $taxonomies;
	}


	public static function get_public_post_types() {

		$args = array(
			// 'public'              => true,
			// 'publicly_queryable ' => true,
		);

		$wp_post_types = get_post_types( $args, 'objects' );
		$post_types    = array();

		foreach ( $wp_post_types as $post_type ) {

			if ( ( ( /* ( $post_type->has_archive ) && */ ( $post_type->public ) ) || ( 'post' === $post_type->name ) ) && ( $post_type->name !== 'attachment' ) ) {
				$item          = array();
				$item['value'] = $post_type->name;
				$item['label'] = $post_type->labels->name;
				array_push( $post_types, $item );
			}
		}

		return $post_types;
	}
	public static function get_authors() {
		$args         = array(
			'role__not_in' => 'Subscriber',
			'orderby'      => 'display_name',
			'order'        => 'ASC',
			'fields'       => 'ids',
		);
		$author_query = new \WP_User_Query( $args );
		$author_ids   = $author_query->get_results();

		$authors = array();
		foreach ( $author_ids as $author_id ) {

			$author_name = get_the_author_meta( 'display_name', $author_id );

			$item          = array();
			$item['value'] = $author_id;
			$item['label'] = $author_name;
			array_push( $authors, $item );

		}

		return $authors;
	}

	public static function get_public_post_type_objects() {

		$args = array(
			// 'public'              => true,
			// 'publicly_queryable ' => true,
		);

		$wp_post_types = get_post_types( $args, 'objects' );
		$post_types    = array();

		foreach ( $wp_post_types as $post_type ) {

			if ( ( $post_type->public ) || ( 'post' === $post_type->name ) ) {
				array_push( $post_types, $post_type );
			}
		}

		return $post_types;
	}

	public static function get_post_types() {

		$args = array();

		$post_types = get_post_types( $args, 'objects' );

		$exclude_post_types = array( 'custom-layouts', 'revision', 'nav_menu_item', 'shop_webhook' );
		$json_post_types    = array();

		foreach ( $post_types as $post_type ) {

			if ( ! in_array( $post_type->name, $exclude_post_types ) ) {
				$item          = array();
				$item['value'] = $post_type->name;
				$item['label'] = $post_type->labels->name;
				array_push( $json_post_types, $item );
			}
		}
		return $json_post_types;
	}
	public static function get_post_types_taxonomies() {
		$post_types = self::get_public_post_type_objects();

		$post_types_taxonomies = array();

		foreach ( $post_types as $post_type ) {
			$post_type_slug  = $post_type->name;
			$post_type_label = $post_type->labels->name;

			$post_types_taxonomies[ $post_type_slug ]               = array();
			$post_types_taxonomies[ $post_type_slug ]['slug']       = $post_type_slug;
			$post_types_taxonomies[ $post_type_slug ]['label']      = $post_type_label;
			$post_types_taxonomies[ $post_type_slug ]['taxonomies'] = get_object_taxonomies( $post_type_slug, 'names' );
		}

		return $post_types_taxonomies;
	}

	public static function get_post_stati() {

		$post_stati_objects = get_post_stati( array(), 'objects' );
		$post_stati_ignore  = array( 'auto-draft', 'inherit' );

		$post_stati = array();

		foreach ( $post_stati_objects as $post_status_key => $post_status ) {

			// don't add any from the ignore list
			if ( ! in_array( $post_status_key, $post_stati_ignore ) ) {

				$post_status = array(
					'value' => $post_status_key,
					'label' => $post_status->label,
				);

				array_push( $post_stati, $post_status );
			}
		}

		return $post_stati;
	}

	/* TODO - used in frontend */
	public static function get_layout_data( $post_id ) {

		$layout = self::get_section_data( $post_id, 'layout' );
		$query  = self::get_section_data( $post_id, 'query' );

		if ( $layout && $query ) {
			$settings = array_merge( $layout, $query );
			return $settings;
		}

		return false;
	}
	public static function get_template_data( $post_id ) {

		if ( 0 === $post_id ) {
			$settings = self::get_default_template();
			return $settings;
		}

		$settings = array(
			'instances'      => self::get_section_data( $post_id, 'template-instances' ),
			'instance_order' => self::get_section_data( $post_id, 'template-instance-order' ),
			'template'       => self::get_section_data( $post_id, 'template-data' ),
			'app'            => self::get_section_data( $post_id, 'app-data' ),
		);
		return $settings;
	}
	public static function get_option( $option_name ) {
		if ( $option_name === 'breakpoints' ) {
			return get_option(
				'custom_layouts_breakpoints',
				array(
					// 'large' => '1920',
					'medium' => '1280',
					'small'  => '960',
					'xsmall' => '600',
				)
			);
		}
		return false;
	}

	public static function get_all_image_sizes() {
		$default_image_sizes = get_intermediate_image_sizes();
		// array_push( $default_image_sizes, 'full' );
		return $default_image_sizes;

	}

	public static function get_default_template() {
		return array(
			'instances'      => array(
				'featured_media' =>
				array(
					'data'      =>
					array(
						'customClass'              => '',
						'align'                    => 'center center',
						'fontSize'                 => '',
						'fontFamily'               => 'Arial',
						'fontFormatBold'           => 'no',
						'fontFormatItalic'         => 'no',
						'fontFormatUnderline'      => 'no',
						'formattingHoverIsLocked'  => 'yes',
						'fontFormatBoldHover'      => '',
						'fontFormatItalicHover'    => '',
						'fontFormatUnderlineHover' => '',
						'marginSize'               => array(
							'top'    => '0px',
							'right'  => '0px',
							'bottom' => '0px',
							'left'   => '0px',
						),
						'paddingSize'              => array(
							'top'    => '0px',
							'right'  => '0px',
							'bottom' => '0px',
							'left'   => '0px',
						),
						'borderRadius'             => array(
							'tl' => '4px',
							'tr' => '4px',
							'br' => '4px',
							'bl' => '4px',
						),
						'imagePosition'            => 'top',
						'imageSourceSize'          => 'medium',
						'imageAlign'               => 'center center',
						'backgroundColor'          => '#efefef',
						'foregroundColor'          => '#c2c2c2',
						'linkToPost'               => 'yes',
						'containerSizing'          => 'natural',
						'aspectRatio'              => '16_9',
						'imageFitMode'             => 'full_width',
						'openNewWindow'            => 'no',

					),
					'elementId' => 'featured_media',
					'id'        => 'eluid-1001',
					'locked'    => 'yes',
				),
				'section'        =>
				array(
					'data'      =>
					array(
						'customClass'              => '',
						'align'                    => 'left',
						'fontSize'                 => '',
						'fontFamily'               => 'Arial',
						'fontFormatBold'           => 'no',
						'fontFormatItalic'         => 'no',
						'fontFormatUnderline'      => 'no',
						'formattingHoverIsLocked'  => 'yes',
						'fontFormatBoldHover'      => '',
						'fontFormatItalicHover'    => '',
						'fontFormatUnderlineHover' => '',
						'marginSize'               => array(
							'top'    => '0px',
							'right'  => '0px',
							'bottom' => '0px',
							'left'   => '0px',
						),
						'paddingSize'              => array(
							'top'    => '5px',
							'right'  => '5px',
							'bottom' => '5px',
							'left'   => '5px',
						),
						'borderRadius'             => array(
							'tl' => '0px',
							'tr' => '0px',
							'br' => '0px',
							'bl' => '0px',
						),
					  // 'widthMode' => 'wide',
					),
					'elementId' => 'section',
					'id'        => 'eluid-1002',
					'locked'    => 'yes',
				),
				'eluid-1003'     =>
				array(
					'data'      =>
					array(
						'fontSize'                 => '',
						'fontFamily'               => '',
						'fontFormatBold'           => 'yes',
						'fontFormatItalic'         => 'no',
						'fontFormatUnderline'      => 'no',
						'fontFormatBoldHover'      => 'yes',
						'fontFormatItalicHover'    => 'no',
						'fontFormatUnderlineHover' => 'yes',
						'formattingHoverIsLocked'  => 'no',
						'htmlTag'                  => 'h3',
						'align'                    => 'left',
						'openNewWindow'            => 'no',

						'marginSize'               => array(
							'top'    => '0px',
							'right'  => '0px',
							'bottom' => '0px',
							'left'   => '0px',
						),
						'paddingSize'              => array(
							'top'    => '5px',
							'right'  => '0px',
							'bottom' => '5px',
							'left'   => '0px',
						),
						'borderRadius'             => array(
							'tl' => '0px',
							'tr' => '0px',
							'br' => '0px',
							'bl' => '0px',
						),
						'linkToPost'               => 'yes',
						'customClass'              => '',
						'widthMode'                => 'wide',
						'lineHeight'               => '',
					),
					'elementId' => 'title',
					'locked'    => 'no',
					'id'        => 'eluid-1003',
				),
				'eluid-1004'     =>
				array(
					'data'      =>
					array(
						'excerptUseContent'   => 'yes',
						'excerptTrim'         => 'no',
						'excerptHideReadMore' => 'yes',
						'excerptLength'       => 15,
						'marginSize'          => array(
							'top'    => '0px',
							'right'  => '0px',
							'bottom' => '0px',
							'left'   => '0px',
						),
						'paddingSize'         => array(
							'top'    => '5px',
							'right'  => '0px',
							'bottom' => '5px',
							'left'   => '0px',
						),
						'borderRadius'        => array(
							'tl' => '0px',
							'tr' => '0px',
							'br' => '0px',
							'bl' => '0px',
						),
						'customClass'         => '',
						'widthMode'           => 'wide',
						'lineHeight'          => '',
					),
					'elementId' => 'excerpt',
					'locked'    => 'no',
					'id'        => 'eluid-1004',
				),
				'eluid-1005'     =>
				array(
					'data'      =>
					array(
						'label'                    => 'Read More',
						'openNewWindow'            => 'no',
						'textColor'                => '#ffffff',
						'backgroundColor'          => '#0693e3',

						'marginSize'               => array(
							'top'    => '10px',
							'right'  => '0px',
							'bottom' => '0px',
							'left'   => '0px',
						),
						'paddingSize'              => array(
							'top'    => '5px',
							'right'  => '10px',
							'bottom' => '5px',
							'left'   => '10px',
						),
						'borderRadius'             => array(
							'tl' => '5px',
							'tr' => '5px',
							'br' => '5px',
							'bl' => '5px',
						),

						'align'                    => 'right',
						'customClass'              => '',
						'fontFormatBold'           => 'no',
						'fontFormatItalic'         => 'no',
						'fontFormatUnderline'      => 'no',
						'fontFormatBoldHover'      => 'no',
						'fontFormatItalicHover'    => 'no',
						'fontFormatUnderlineHover' => 'yes',
						'formattingHoverIsLocked'  => 'no',

						'lineHeight'               => '',
						'widthMode'                => 'auto',
					),
					'elementId' => 'link',
					'locked'    => 'no',
					'id'        => 'eluid-1005',
				),
			),
			'instance_order' => array( 'eluid-1003', 'eluid-1004', 'eluid-1005' ),
			'template'       => array(
				'showFeaturedImage'     => 'yes',
				'imageSourceSize'       => 'medium',
				'imagePosition'         => 'top',
				'backgroundColor'       => '#fff',
				'textColor'             => '#333333',
				'backgroundImageSource' => 'none',

				'marginSize'            => array(
					'top'    => '0px',
					'right'  => '0px',
					'bottom' => '0px',
					'left'   => '0px',
				),
				'paddingSize'           => array(
					'top'    => '7px',
					'right'  => '7px',
					'bottom' => '7px',
					'left'   => '6px',
				),
				'borderRadius'          => array(
					'tl' => '4px',
					'tr' => '4px',
					'br' => '4px',
					'bl' => '4px',
				),
				'borderWidth'           => 2,
				'borderColor'           => '#cccccc',
				'previewWidth'          => 250,
				'customClass'           => '',
			),
		);
	}
	public static function get_default_layout() {
		return array(
			'attributes' => array(
				'templateId'          => 'default',
				'layoutId'            => 'default',
				'columnsLarge'        => 3,
				'columnsMedium'       => 3,
				'columnsMediumLocked' => 'yes',
				'columnsSmall'        => 2,
				'columnsSmallLocked'  => 'no',
				'columnsXSmall'       => 1,
				'columnsXSmallLocked' => 'no',
				'useMasonry'          => 'no',
				'equalHeightRows'     => 'no',
				'fillLastRow'         => 'no',
				'backgroundColor'     => '',
				'marginSize'          => array(
					'top'    => '0px',
					'right'  => '0px',
					'bottom' => '0px',
					'left'   => '0px',
				),
				'paddingSize'         => array(
					'top'    => '0px',
					'right'  => '0px',
					'bottom' => '0px',
					'left'   => '0px',
				),
				'gridGap'             => array(
					'column' => '10px',
					'row'    => '10px',
				),
				'postTypes'           => array( 'post' ),
				'paginationType'      => array( 'none' ),
				'postsPerPage'        => 6,
				'offset'              => 0,
				'ignoreStickyPosts'   => 'no',
				'filterTaxonomies'    => 'no',
				'taxonomyQuery'       => new \StdClass(),
				'filterAuthors'       => 'no',
				'authors'             => array(),
				'excludeCurrentPost'  => 'yes',
				'noResultsMessage'    => 'No results found.',
				'orderDir'            => 'desc',
				'orderBy'             => 'date',
				'useSearchFilter'     => 'no',
				'useSavedLayout'      => 'no',
				'searchFilterId'      => '',
				'align'               => '',
			),
		);
	}
}
