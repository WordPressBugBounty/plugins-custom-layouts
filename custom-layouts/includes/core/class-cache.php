<?php
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
// TODO - need to clear transients when a post type we're querying is updated
// use `save_post`


class Cache {

	public static $transient_keys        = array();
	public static $transient_query_keys  = array();
	public static $transient_keys_key    = 'custom_layouts_transient_keys';
	public static $transient_queries_key = 'custom_layouts_transient_query_keys';
	public static $cache_options         = array();
	public static $cache_option_name     = 'custom-layouts-cache';
	// public static $use_transients = -1;
	public static $use_transients = 1;

	public static function set_transient( $transient_key, $data, $lifespan = null ) {

		self::update_transient_keys( $transient_key );

		if ( $lifespan === null ) {
			$lifespan = DAY_IN_SECONDS * 30;
		}

		if ( self::$use_transients !== 1 ) {
			return;
		}

		// only set transients if the cache has completed..
		$update_transient = true;
		if ( $update_transient ) {
			if ( $lifespan === null ) {
				$lifespan = self::get_transient_lifespan();
			}
			return set_transient( self::create_transient_key( $transient_key ), $data, $lifespan );
		}

		return false;
	}
	public static function set_query_transient( $transient_key, $data, $lifespan = null ) {

		self::update_query_transient_keys( $transient_key );

		if ( $lifespan === null ) {
			$lifespan = DAY_IN_SECONDS * 30;
		}

		if ( self::$use_transients !== 1 ) {
			return;
		}
		// only set transients if the cache has completed..
		$update_transient = true;
		if ( $update_transient ) {
			if ( $lifespan === null ) {
				$lifespan = self::get_transient_lifespan();
			}
			return set_transient( self::create_transient_key( $transient_key ), $data, $lifespan );
		}

		return false;
	}

	public static function get_transient( $transient_key ) {
		// self::update_transient_keys($transient_key);

		if ( self::$use_transients !== 1 ) {
			return false;
		}

		$transient = get_transient( self::create_transient_key( $transient_key ) );
		return $transient;
	}

	public static function delete_transient( $transient_key ) {
		self::update_transient_keys( $transient_key, true );
		return delete_transient( self::create_transient_key( $transient_key ) );
	}

	public static function purge_all_transients() {
		self::init_transient_keys( true );
		// For each key, delete that transient.
		foreach ( self::$transient_keys as $t ) {
			delete_transient( $t );
		}
		self::$transient_keys = array();

		// Reset our DB value.
		update_option( self::$transient_keys_key, array() );

		// now do the queries too
		self::purge_all_query_transients();
	}
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

	public static function get_transient_lifespan() {
		$ten_mins = ( DAY_IN_SECONDS / 24 / 60 ) * 10;
		$one_week = DAY_IN_SECONDS * 7;
		return $one_week;
	}

	public static function create_transient_key( $transient_key ) {
		// max length of transient key is 45 characters
		// md5 gives 32 characters
		// so we have 13 characters to play with
		return 'cl_' . md5( $transient_key );
	}

	public static function init_transient_keys( $override = false ) {
		if ( self::$use_transients === -1 ) {
			self::$use_transients = (int) Search_Filter_Helper::get_option( 'cache_use_transients' );
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
	public static function update_transient_keys( $transient_key, $delete = false ) {

		self::init_transient_keys();

		if ( self::$use_transients !== 1 ) {
			return;
		}

		$real_transient_key = self::create_transient_key( $transient_key );

		if ( ! in_array( $real_transient_key, self::$transient_keys ) ) {
			array_push( self::$transient_keys, $real_transient_key );
			update_option( self::$transient_keys_key, self::$transient_keys );

		} elseif ( $delete === true ) {
			// if delete is true try to find it and remove it
			$search_index = array_search( $real_transient_key, self::$transient_keys );

			if ( $search_index !== false ) {
				unset( self::$transient_keys[ $search_index ] );
				update_option( self::$transient_keys_key, self::$transient_keys );
			}
		}
	}
	public static function update_query_transient_keys( $transient_key, $delete = false ) {

		self::init_transient_keys();

		if ( self::$use_transients !== 1 ) {
			return;
		}

		$real_transient_key = self::create_transient_key( $transient_key );

		if ( ! in_array( $real_transient_key, self::$transient_query_keys ) ) {
			array_push( self::$transient_query_keys, $real_transient_key );
			update_option( self::$transient_queries_key, self::$transient_query_keys );

		} elseif ( $delete === true ) {
			// if delete is true try to find it and remove it
			$search_index = array_search( $real_transient_key, self::$transient_query_keys );

			if ( $search_index !== false ) {
				unset( self::$transient_query_keys[ $search_index ] );
				update_option( self::$transient_queries_key, self::$transient_query_keys );
			}
		}
	}
	/*
	public static function update_transient_query_keys( $transient_key, $delete = false ) {
		self::init_transient_keys();

		if ( self::$use_transients !== 1 ) {
			return;
		}

		$real_transient_key = self::create_transient_key( $transient_key );

		if ( ! in_array( $real_transient_key, self::$transient_query_keys ) ) {
			array_push( self::$transient_query_keys, $real_transient_key );
			update_option( self::$transient_queries_key, self::$transient_query_keys );
		} else if ( $delete=== true ) {
			//if delete is true try to find it and remove it
			$search_index = array_search( $real_transient_key, self::$transient_query_keys );

			if ( $search_index !== false ) {
				unset( self::$transient_query_keys[ $search_index ] );
				update_option( self::$transient_queries_key, self::$transient_query_keys );
			}
		}
	}*/
}
