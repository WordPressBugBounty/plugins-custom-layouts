<?php
/**
 * Class to handle the creation and loading of assets.
 *
 * @link       https://github.com/rmorse/custom-layouts
 * @since      1.0.0
 *
 * @package    Custom_Layouts
 * @subpackage Custom_Layouts/includes
 */

namespace Custom_Layouts;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Loader class.
 *
 * Handles the creation, registration and loading of assets (scripts and styles).
 *
 * @since 1.0.0
 */
class Asset_Loader {

	/**
	 * Collection of registered assets.
	 *
	 * @var array
	 */
	private static $registered_assets = array();

	/**
	 * Create an asset from an asset config.
	 *
	 * @since 3.2.0
	 *
	 * @param array $config The script configuration.
	 */
	private static function create_asset( $config ) {

		$defaults = array(
			'script' => array(
				'src'          => '',
				'dependencies' => array(),
				'footer'       => false,
				'asset_path'   => '',
				'version'      => '',
				'data'         => array(),
			),
			'style'  => array(
				'src'          => '',
				'dependencies' => array(),
				'media'        => 'all',
				'version'      => '',
			),
		);

		$config_script = isset( $config['script'] ) ? $config['script'] : array();
		$config_style  = isset( $config['style'] ) ? $config['style'] : array();

		$asset = array(
			'script' => wp_parse_args( $config_script, $defaults['script'] ),
			'style'  => wp_parse_args( $config_style, $defaults['style'] ),
		);

		$script_has_asset = isset( $config['script']['asset_path'] ) && ! empty( $config['script']['asset_path'] );

		// Try to load the asset data from the asset path if it exists.
		if ( $script_has_asset && file_exists( $config['script']['asset_path'] ) ) {
			$script_asset_data               = require $config['script']['asset_path'];
			$asset['script']['dependencies'] = array_unique( array_merge( $asset['script']['dependencies'], $script_asset_data['dependencies'] ) );
			$asset['script']['version']      = $script_asset_data['version'];
		}

		$asset = apply_filters( 'search-filter/core/asset-loader/create_asset', $asset );
		return $asset;
	}

	/**
	 * Create assets array from configs.
	 *
	 * @since 3.2.0
	 *
	 * @param array $asset_configs The asset configurations.
	 * @param array $exclude       The asset names to exclude.
	 * @return array The asset configs.
	 */
	public static function create_assets( $asset_configs, $exclude = array() ) {

		$assets = array();
		foreach ( $asset_configs as $asset_config ) {
			if ( in_array( $asset_config['name'], $exclude, true ) ) {
				continue;
			}
			$asset = self::create_asset( $asset_config );
			if ( ! $asset ) {
				continue;
			}
			$assets[ $asset_config['name'] ] = $asset;
		}

		return $assets;
	}

	/**
	 * Registers the assets.
	 *
	 * @since 3.2.0
	 *
	 * @param array $assets The assets configs to register.
	 */
	public static function register( $assets ) {

		// Allow assets to be modified.
		$assets = apply_filters( 'search-filter/core/asset-loader/register', $assets );

		self::$registered_assets = wp_parse_args( $assets, self::$registered_assets );

		// Register the assets with WP.
		foreach ( $assets as $asset_name => $args ) {
			if ( ! empty( $args['script']['src'] ) ) {

				wp_register_script( $asset_name, $args['script']['src'], $args['script']['dependencies'], $args['script']['version'], $args['script']['footer'] );

				// Add custom data.
				if ( ! empty( $args['script']['data'] ) ) {
					self::add_script_data( $asset_name, $args['script']['data'] );
				}
			}

			if ( ! empty( $args['style']['src'] ) ) {
				$style_src = $args['style']['src'];
				wp_register_style( $asset_name, $style_src, $args['style']['dependencies'], $args['style']['version'], $args['style']['media'] );
			}
		}
	}

	/**
	 * Add custom data to a script.
	 *
	 * @since 3.2.0
	 *
	 * @param string $asset_name The asset name.
	 * @param array  $data       The data to add to the script.
	 */
	private static function add_script_data( $asset_name, $data ) {
		$default_data = array(
			'identifier' => '',
			'value'      => '',
			'position'   => 'before',
		);

		$data = wp_parse_args( $data, $default_data );
		if ( empty( $data['identifier'] ) ) {
			return;
		}
		$script = $data['identifier'] . ' = ' . wp_json_encode( $data['value'] ) . ';';
		wp_add_inline_script( $asset_name, $script, $data['position'] );
	}
	/**
	 * Enqueue the assets.
	 *
	 * @since 3.2.0
	 *
	 * @param array  $asset_handles The asset handles to load.
	 * @param string $type          The type of assets to enqueue (all, scripts, or styles).
	 */
	public static function enqueue( $asset_handles = array(), $type = 'all' ) {

		$asset_handles = apply_filters( 'search-filter/core/asset-loader/enqueue', $asset_handles );

		// Enqueue the assets.
		foreach ( $asset_handles as $asset_name ) {
			// Skip assets that are not registered.
			if ( ! isset( self::$registered_assets[ $asset_name ] ) ) {
				continue;
			}

			$args = self::$registered_assets[ $asset_name ];

			if ( 'all' === $type || 'scripts' === $type ) {
				if ( ! empty( $args['script']['src'] ) ) {
					wp_enqueue_script( $asset_name );
				}
			}

			if ( 'all' === $type || 'styles' === $type ) {
				if ( ! empty( $args['style']['src'] ) ) {
					wp_enqueue_style( $asset_name );
				}
			}
		}
	}
}
