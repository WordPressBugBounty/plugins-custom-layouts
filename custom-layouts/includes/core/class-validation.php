<?php
/**
 * Validation utilities class
 *
 * @package    Custom_Layouts
 * @since      1.0.0
 */

namespace Custom_Layouts\Core;

/**
 * Class for storing general data - usually uses `wp_options`
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


class Validation {

	/**
	 * List of allowed HTML tags for validation.
	 *
	 * @var array
	 */
	private static $html_tags = array(
		'a',
		'abbr',
		'address',
		'area',
		'article',
		'aside',
		'audio',
		'b',
		'base',
		'bdi',
		'bdo',
		'blockquote',
		// 'body',
		// 'br',
		'button',
		'canvas',
		'caption',
		'cite',
		'code',
		'col',
		'colgroup',
		'data',
		'datalist',
		'dd',
		'del',
		'details',
		// 'dfn',
		'dialog',
		'div',
		'dl',
		'dt',
		'em',
		'embed',
		'fieldset',
		'figure',
		'footer',
		'form',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		// 'head',
		'header',
		'hgroup',
		// 'hr',
		// 'html',
		'i',
		// 'iframe',
		'img',
		// 'input',
		'ins',
		// 'kbd',
		// 'keygen',
		'label',
		'legend',
		'li',
		'link',
		'main',
		'map',
		'mark',
		'menu',
		'menuitem',
		'meta',
		'meter',
		'nav',
		// 'noscript',
		'object',
		'ol',
		'optgroup',
		'option',
		// 'output',
		'p',
		'param',
		'pre',
		'progress',
		'q',
		's',
		'samp',
		'section',
		'select',
		'small',
		// 'source',
		'span',
		'strong',
		// 'style',
		'sub',
		'summary',
		'sup',
		'table',
		'tbody',
		'td',
		'template',
		// 'textarea',
		'tfoot',
		'th',
		'thead',
		'time',
		// 'title',
		'tr',
		// 'track',
		'u',
		'ul',
		// 'var',
		// 'video',
		// 'wbr',

	);

	/**
	 * Initialize the validation class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {
	}

	/**
	 * Escape and validate an HTML tag name.
	 *
	 * @since 1.0.0
	 * @param string $input The tag name to validate.
	 * @return string The validated tag name or empty string if invalid.
	 */
	public static function esc_html_tag( $input ) {
		if ( in_array( $input, self::$html_tags, true ) ) {
			return $input;
		}
		return '';
	}
}
