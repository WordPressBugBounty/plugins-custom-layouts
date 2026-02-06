<?php
/**
 * Gutenberg Integration Class
 *
 * @link       http://codeamp.com
 * @since      1.0.0
 * @package    Custom_Layouts
 */

namespace Custom_Layouts\Integrations;

use Custom_Layouts\Core\CSS_Loader;
use Custom_Layouts\Settings;
use Custom_Layouts\Util;
use Custom_Layouts\Asset_Loader;


use Custom_Layouts\Layout\Controller as Layout_Controller;
use Custom_Layouts\Template\Controller as Template_Controller;
/**
 * All Gutenberg integration functionality
 */
class Gutenberg {

	/**
	 * Map of attributes from PHP <-> JS.
	 *
	 * @since    1.0.0
	 * @var array $attributes_map Map of attributes from PHP <-> JS.
	 */
	private static $attributes_map = array(
		'useSavedLayout'      => 'use_saved_layout',
		'layoutId'            => 'layout_id',

		'templateId'          => 'template_id',
		'postTypes'           => 'post_types',
		'columnsLarge'        => 'columns_large',
		'columnsMedium'       => 'columns_medium',
		'columnsSmall'        => 'columns_small',
		'columnsXsmall'       => 'columns_xsmall',
		'columnsMediumLocked' => 'columns_medium_locked',
		'columnsSmallLocked'  => 'columns_small_locked',
		'columnsXsmallLocked' => 'columns_xsmall_locked',

		'itemSpacing'         => 'item_spacing',
		'gridGap'             => 'grid_gap',
		'marginSize'          => 'margin_size',
		'paddingSize'         => 'padding_size',
		'backgroundColor'     => 'background_color',
		'backgroundGradient'  => 'background_gradient',
		'paginationType'      => 'pagination_type',
		'postsPerPage'        => 'posts_per_page',
		'offset'              => 'offset',
		'orderBy'             => 'order_by',
		'orderDir'            => 'order_dir',
		'ignoreStickyPosts'   => 'ignore_sticky_posts',
		'filterTaxonomies'    => 'filter_taxonomies',
		'filterAuthors'       => 'filter_authors',
		'authors'             => 'authors',
		'taxonomyQuery'       => 'taxonomy_query',
		'useSearchFilter'     => 'use_search_filter',
		'searchFilterId'      => 'search_filter_id',
		'excludeCurrentPost'  => 'exclude_current_post',
		'noResultsMessage'    => 'no_results_message',
		'useMasonry'          => 'use_masonry',
		'equalHeightRows'     => 'equal_height_rows',
		'fillLastRow'         => 'fill_last_row',
		'className'           => 'add_class',
	);

	/**
	 * Initialize the Gutenberg integration.
	 */
	public static function init() {

		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}

		add_action( 'enqueue_block_editor_assets', 'Custom_Layouts\\Integrations\\Gutenberg::editor_assets', 10 );
		add_action( 'init', 'Custom_Layouts\\Integrations\\Gutenberg::register_blocks', 10 );
		add_filter( 'block_editor_rest_api_preload_paths', '\\Custom_Layouts\\Integrations\\Gutenberg::preload_api_paths', 10 );
	}

	/**
	 * Adds our commonly used (required on init) rest api paths for blocks.
	 *
	 * @param array $preload_paths  Existing api paths.
	 * @return array
	 */
	public static function preload_api_paths( $preload_paths ) {
		$paths         = array(
			'/custom-layouts/v1/templates/get',
			'/custom-layouts/v1/layouts/get',
			'/custom-layouts/v1/wp/post_types',
			'/custom-layouts/v1/wp/authors',
			array( '/custom-layouts/v1/layout/info', 'GET' ),
			'/custom-layouts/v1/layout/get?id=default',
			'/custom-layouts/v1/template/get?id=default',
		);
		$preload_paths = array_merge( $preload_paths, $paths );
		return $preload_paths;
	}
	/**
	 * Register the blocks for the block editor.
	 *
	 * @since    1.0.0
	 */
	public static function register_blocks() {

		$blocks_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR;

		register_block_type_from_metadata(
			$blocks_dir . 'layout' . DIRECTORY_SEPARATOR,
			array(
				'editor_script'   => 'custom-layouts-gutenberg',
				'render_callback' => array( 'Custom_Layouts\\Integrations\\Gutenberg', 'render_layout' ),
			)
		);

		register_block_type_from_metadata(
			$blocks_dir . 'template' . DIRECTORY_SEPARATOR,
			array(
				'editor_script'   => 'custom-layouts-gutenberg',
				'render_callback' => array( 'Custom_Layouts\\Integrations\\Gutenberg', 'render_template' ),
				'uses_context'    => array(
					'queryId',
					'query',
					'queryContext',
					'layout',
					'templateSlug',
					'postId',
					'postType',
				),
			)
		);
	}

	/**
	 * Maps attibute names from PHP <-> JS.
	 *
	 * @param string $from The direction of the mapping.
	 * @param array  $attributes_values The block attributes.
	 * @return array The mapped attributes.
	 */
	public static function map_attributes( $from, $attributes_values ) {
		$new_attributes = array();

		$temp_attributes_map = self::$attributes_map;
		// Default is from js due the existing setup for self::$attributes_map.
		if ( 'php' === $from ) {
			$temp_attributes_map = array_flip( $temp_attributes_map );
		}

		foreach ( $attributes_values as $attribute_name => $attribute_value ) {
			if ( isset( $temp_attributes_map[ $attribute_name ] ) ) {
				$setting_name                    = $temp_attributes_map[ $attribute_name ];
				$new_attributes[ $setting_name ] = $attribute_value;
			}
		}

		return $new_attributes;
	}
	/**
	 * Render the layout block.
	 *
	 * @param array $block_attributes The block attributes.
	 * @return string The rendered layout.
	 */
	public static function render_layout( $block_attributes ) {

		$args = array();
		$args = self::map_attributes( 'js', $block_attributes );

		// Add support for align block attribute.
		if ( isset( $block_attributes['align'] ) ) {

			$args['container_class'] = 'align' . $block_attributes['align'];
		}

		$layout_args = $args;
		// Check if we are loading a saved layout, or regular.
		if ( isset( $layout_args['use_saved_layout'] ) && $layout_args['use_saved_layout'] === 'yes' ) {
			$layout_id         = absint( $layout_args['layout_id'] );
			$layout_controller = new Layout_Controller( $layout_id );
			if ( isset( $block_attributes['align'] ) ) {
				$layout_controller->add_container_class( 'align' . $block_attributes['align'] );
			}
			if ( isset( $block_attributes['className'] ) ) {
				$layout_controller->add_class( $block_attributes['className'] );
			}
		} else {
			$layout_controller = new Layout_Controller( $layout_args );
		}

		ob_start();
		$layout_controller->render();
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Render the template block.
	 *
	 * @param array  $block_attributes The block attributes.
	 * @param string $content The block content.
	 * @param object $block The block object.
	 *
	 * @return string The rendered template.
	 */
	public static function render_template( $block_attributes, $content, $block ) {

		$args = array();
		$args = self::map_attributes( 'js', $block_attributes );

		$post_ID = '';
		if ( isset( $block_attributes['postId'] ) ) {
			// Use the setting for the post ID.
			$post_ID = $block_attributes['postId'];
		} elseif ( isset( $block->context['postId'] ) ) {
			$post_ID = $block->context['postId'];
		} else {
			return '';
		}

		ob_start();

		// TODO - we don't have defaults for template block in our config files
		// $defaults = Settings::get_settings_defaults( array( 'template' ) );
		// $template_args = wp_parse_args( $args, $defaults );.
		if ( ! isset( $args['template_id'] ) ) {
			$args['template_id'] = 0;
		} elseif ( $args['template_id'] === 'default' ) {
			$args['template_id'] = 0;
		}

		$template_controller = new Template_Controller( $args['template_id'] ); // Can be "default".
		$template_controller->render( $post_ID );

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Enqueue the Gutenberg editor assets.
	 */
	public static function editor_assets() {

		// Bail if we're on one of our admin screens, we don't want to load block editor assets.
		if ( Util::screen_is_custom_layouts() ) {
			return;
		}

		// Enqueue Gutenberg assets using Asset_Loader.
		Asset_Loader::enqueue( array( 'custom-layouts-gutenberg' ) );

		// Add the container for loading our modals into.
		add_action( 'admin_footer', array( 'Custom_Layouts\\Integrations\\Gutenberg', 'add_editor_modal' ) );
	}

	/**
	 * Add the container for loading our modals into.
	 */
	public static function add_editor_modal() {
		echo '<div id="cl-admin-app-modal" style="position:relative;"></div>';
	}

	/**
	 * Adds a toggle to stop event propogation to keyboard events.
	 *
	 * Possible damage control for - https://github.com/WordPress/gutenberg/issues/18755
	 */
	public static function shortcut_capture_script() {
		?>
		<script>
		// Lets get in before gutenberg even loads, we'll use a nasty hack to interrupt.
		// Mousetrap keyboard shortcuts.
		window.customLayoutsHandler = { blockShortcuts: false };
		const displayPostDockKeyBlock = function( e ) {
			if ( window.customLayoutsHandler.blockShortcuts === true ) {
				e.stopImmediatePropagation();
			}
		};
		document.addEventListener( 'keydown', displayPostDockKeyBlock, true );
		</script>
		<?php
	}
}
