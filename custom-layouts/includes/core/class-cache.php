<?php
/**
 * Cache management class
 *
 * @package    Custom_Layouts
 * @since      1.0.0
 */

namespace Custom_Layouts\Core;

use Custom_Layouts\Settings;
/**
 * Interface for interacting with WP transients / cache
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
// TODO - need to clear transients when a post type we're querying is updated.
// Use `save_post`.

/**
 * Cache class for managing WordPress transients.
 *
 * Provides methods for setting, getting, and managing transients with automatic key tracking.
 *
 * @since 1.0.0
 */
class Cache {

	/**
	 * Array of tracked transient keys.
	 *
	 * @var array
	 */
	public static $transient_keys = array();

	/**
	 * Array of tracked query transient keys.
	 *
	 * @var array
	 */
	public static $transient_query_keys = array();

	/**
	 * Option name for storing transient keys.
	 *
	 * @var string
	 */
	public static $transient_keys_key = 'custom_layouts_transient_keys';

	/**
	 * Option name for storing query transient keys.
	 *
	 * @var string
	 */
	public static $transient_queries_key = 'custom_layouts_transient_query_keys';

	/**
	 * Cache options array.
	 *
	 * @var array
	 */
	public static $cache_options = array();

	/**
	 * Cache option name.
	 *
	 * @var string
	 */
	public static $cache_option_name = 'custom-layouts-cache';

	/**
	 * Whether to use transients (1 = enabled, 0 = disabled, -1 = check required).
	 *
	 * @var int
	 */
	// public static $use_transients = -1;
	public static $use_transients = 1;

	/**
	 * Set a transient with automatic key tracking.
	 *
	 * @param string   $transient_key The transient key.
	 * @param mixed    $data The data to store.
	 * @param int|null $lifespan The lifespan in seconds.
	 * @return bool|void True if successful, void if transients disabled.
	 */
	public static function set_transient( $transient_key, $data, $lifespan = null ) {

		self::update_transient_keys( $transient_key );

		if ( $lifespan === null ) {
			$lifespan = DAY_IN_SECONDS * 30;
		}

		if ( self::$use_transients !== 1 ) {
			return;
		}

		// Only set transients if the cache has completed.
		// @phpstan-ignore identical.alwaysFalse
		if ( $lifespan === null ) {
			$lifespan = self::get_transient_lifespan();
		}
		return set_transient( self::create_transient_key( $transient_key ), $data, $lifespan );
	}
	/**
	 * Set a query transient with automatic key tracking.
	 *
	 * @param string   $transient_key The transient key.
	 * @param mixed    $data The data to store.
	 * @param int|null $lifespan The lifespan in seconds.
	 * @return bool|void True if successful, void if transients disabled.
	 */
	public static function set_query_transient( $transient_key, $data, $lifespan = null ) {

		self::update_query_transient_keys( $transient_key );

		if ( $lifespan === null ) {
			$lifespan = DAY_IN_SECONDS * 30;
		}

		if ( self::$use_transients !== 1 ) {
			return;
		}
		// Only set transients if the cache has completed.
		// @phpstan-ignore identical.alwaysFalse
		if ( $lifespan === null ) {
			$lifespan = self::get_transient_lifespan();
		}
		return set_transient( self::create_transient_key( $transient_key ), $data, $lifespan );
	}

	/**
	 * Get a transient by key.
	 *
	 * @param string $transient_key The transient key.
	 * @return mixed|false The transient value or false if not found.
	 */
	public static function get_transient( $transient_key ) {
		// self::update_transient_keys($transient_key);

		if ( self::$use_transients !== 1 ) {
			return false;
		}

		$transient = get_transient( self::create_transient_key( $transient_key ) );
		return $transient;
	}

	/**
	 * Delete a transient by key.
	 *
	 * @param string $transient_key The transient key.
	 * @return bool True if successful, false otherwise.
	 */
	public static function delete_transient( $transient_key ) {
		self::update_transient_keys( $transient_key, true );
		return delete_transient( self::create_transient_key( $transient_key ) );
	}

	/**
	 * Purge all transients tracked by this class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function purge_all_transients() {
		self::init_transient_keys( true );
		// For each key, delete that transient.
		foreach ( self::$transient_keys as $t ) {
			delete_transient( $t );
		}
		self::$transient_keys = array();

		// Reset our DB value.
		update_option( self::$transient_keys_key, array() );

		// Now do the queries too.
		self::purge_all_query_transients();
	}
	/**
	 * Purge all query transients tracked by this class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function purge_all_query_transients() {
		self::init_transient_keys( true );
		// For each key, delete that transient.
		foreach ( self::$transient_query_keys as $t ) {
			delete_transient( $t );
		}
		self::$transient_query_keys = array();
		// Reset our DB value.
		update_option( self::$transient_queries_key, array() );
	}

	/**
	 * Get the default transient lifespan.
	 *
	 * @since 1.0.0
	 * @return int The lifespan in seconds.
	 */
	public static function get_transient_lifespan() {
		$ten_mins = ( DAY_IN_SECONDS / 24 / 60 ) * 10;
		$one_week = DAY_IN_SECONDS * 7;
		return $one_week;
	}

	/**
	 * Create a hashed transient key with prefix.
	 *
	 * @since 1.0.0
	 * @param string $transient_key The original transient key.
	 * @return string The hashed transient key.
	 */
	public static function create_transient_key( $transient_key ) {
		// Max length of transient key is 45 characters.
		// Md5 gives 32 characters.
		// So we have 13 characters to play with.
		return 'cl_' . md5( $transient_key );
	}

	/**
	 * Initialize transient keys from database.
	 *
	 * @since 1.0.0
	 * @param bool $override Whether to override the use_transients check.
	 * @return void
	 */
	public static function init_transient_keys( $override = false ) {
		if ( self::$use_transients === -1 ) {
			self::$use_transients = 0; // Disabled as Search_Filter_Helper doesn't exist.
		}

		if ( ( self::$use_transients === 1 ) || ( $override === true ) ) {
			if ( empty( self::$transient_keys ) ) {
				$transient_keys = get_option( self::$transient_keys_key );

				if ( ! empty( $transient_keys ) ) {
					self::$transient_keys = $transient_keys;
				}
			}
			if ( empty( self::$transient_query_keys ) ) {
				$transient_query_keys = get_option( self::$transient_queries_key );

				if ( ! empty( $transient_query_keys ) ) {
					self::$transient_query_keys = $transient_query_keys;
				}
			}
		}
	}
	/**
	 * Update the list of tracked transient keys.
	 *
	 * @since 1.0.0
	 * @param string $transient_key The transient key to add or remove.
	 * @param bool   $delete Whether to delete the key instead of adding it.
	 * @return void
	 */
	public static function update_transient_keys( $transient_key, $delete = false ) {

		self::init_transient_keys();

		if ( self::$use_transients !== 1 ) {
			return;
		}

		$real_transient_key = self::create_transient_key( $transient_key );

		if ( ! in_array( $real_transient_key, self::$transient_keys, true ) ) {
			array_push( self::$transient_keys, $real_transient_key );
			update_option( self::$transient_keys_key, self::$transient_keys );

		} elseif ( $delete === true ) {
			// If delete is true try to find it and remove it.
			$search_index = array_search( $real_transient_key, self::$transient_keys );

			if ( $search_index !== false ) {
				unset( self::$transient_keys[ $search_index ] );
				update_option( self::$transient_keys_key, self::$transient_keys );
			}
		}
	}
	/**
	 * Update the list of tracked query transient keys.
	 *
	 * @since 1.0.0
	 * @param string $transient_key The transient key to add or remove.
	 * @param bool   $delete Whether to delete the key instead of adding it.
	 * @return void
	 */
	public static function update_query_transient_keys( $transient_key, $delete = false ) {

		self::init_transient_keys();

		if ( self::$use_transients !== 1 ) {
			return;
		}

		$real_transient_key = self::create_transient_key( $transient_key );

		if ( ! in_array( $real_transient_key, self::$transient_query_keys, true ) ) {
			array_push( self::$transient_query_keys, $real_transient_key );
			update_option( self::$transient_queries_key, self::$transient_query_keys );

		} elseif ( $delete === true ) {
			// If delete is true try to find it and remove it.
			$search_index = array_search( $real_transient_key, self::$transient_query_keys );

			if ( $search_index !== false ) {
				unset( self::$transient_query_keys[ $search_index ] );
				update_option( self::$transient_queries_key, self::$transient_query_keys );
			}
		}
	}
}
