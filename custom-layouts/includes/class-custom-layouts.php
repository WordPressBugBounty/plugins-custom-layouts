<?php
/**
 * The file that defines the core plugin class
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
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

if ( ! defined( 'CUSTOM_LAYOUTS_VERSION' ) ) {
	define( 'CUSTOM_LAYOUTS_VERSION', '1.4.12' );
}

class Custom_Layouts {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Custom_Layouts_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'custom-layouts';
		$this->version     = CUSTOM_LAYOUTS_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_schema_hooks();
		$this->upgrade();

		// correctly load public / admin classes & hooks
		if ( ( ! is_admin() ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$this->define_frontend_hooks();
		} elseif ( is_admin() ) {
			$this->define_admin_hooks();
		}

		$this->define_ajax_hooks();
		$this->setup_integrations();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Custom_Layouts_Loader. Orchestrates the hooks of the plugin.
	 * - Custom_Layouts_i18n. Defines internationalization functionality.
	 * - Custom_Layouts_Admin. Defines all hooks for the admin area.
	 * - Custom_Layouts_Frontend. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/* CORE */
		if ( ! defined( 'CUSTOM_LAYOUTS_PATH' ) ) {
			define( 'CUSTOM_LAYOUTS_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
		}
		if ( ! defined( 'CUSTOM_LAYOUTS_URL' ) ) {
			define( 'CUSTOM_LAYOUTS_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		}
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-i18n.php';

		/**
		 * The class responsible for handling updates based on plugin version
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-upgrade.php';

		/**
		 * The class responsible for setting up the data types & structure for S&F
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-schema.php';

		/**
		 * A class to store re-used WP data (post types, taxonomies) and avoid repeated processing / DB calls for that data
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-wp-data.php';

		/**
		 * A class to validate input/ouput
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-validation.php';

		/**
		 * A class to handle the method of storing & retrieving generated CSS
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-css-loader.php';
		/**
		 * A class to handle interactions with WP cache
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-cache.php';
		/**
		 * A class to handle interacting with options data
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-data.php';

		/**
		 * The class contains utility functions that are commonly used throughout
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-util.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-frontend.php';

		/**
		 * The class contains REST API functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-rest-api.php';

		/**
		 * The class contains Settings related functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/settings/class-setting.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/settings/class-query.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/settings/class-grid.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/settings/class-defaults.php';

		/* ADMIN */

		/**
		 * The class contains Admin options related functions
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-options.php';

		/* FRONTEND */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-query.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/query/class-selector.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-grid.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/layout/class-controller.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/class-controller.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-element-base.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-title.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-excerpt.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-content.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-author.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-published-date.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-modified-date.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-custom-field.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-taxonomy.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-post-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-link.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-comment-count.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-text.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-featured-media.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template/elements/class-section.php';

		/* Integrations */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-layouts-integrations.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/integrations/woocommerce/class-woocommerce.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/integrations/gutenberg/class-gutenberg.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/integrations/wordpress-importer/class-wordpress-importer.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/integrations/search-filter-pro/class-search-filter-pro.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/integrations/wpml/class-wpml.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/integrations/polylang/class-polylang.php';

		/* Upgrades */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/upgrade/1.3.0.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/upgrade/1.4.0.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/upgrade/1.4.1.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/upgrade/1.4.2.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/upgrade/1.4.3.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/upgrade/1.4.8.php';

		$this->loader = new Custom_Layouts\Core\Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Custom_Layouts_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Custom_Layouts\Core\i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Attaches the upgrade routines on plugins_loaded
	 *
	 * @since    1.4.0
	 * @access   private
	 */
	private function upgrade() {
		$this->loader->add_action( 'plugins_loaded', 'Custom_Layouts\Core\upgrade', 'upgrade' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Custom_Layouts\Admin( $this->get_plugin_name(), $this->get_version() );

		// scripts & css
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 10 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 10 );

		// admin pages
		// $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'remove_metaboxes', 1000 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_metaboxes', 20 );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'admin_head', 20 );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'admin_footer', 20 );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_pages', 9 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_pages_more_menu_items', 10 );

	}
	private function define_ajax_hooks() {

		// stop doing this
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_frontend_hooks() {

		$plugin_public = new Custom_Layouts\Frontend( $this->get_plugin_name(), $this->get_version() );

		// scripts & css
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 100 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_scripts' );

	}
	/**
	 * Register
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function setup_integrations() {
		$this->integrations = Custom_Layouts\Integrations::init();

	}

	private function define_schema_hooks() {

		$schema = new Custom_Layouts\Core\Schema( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $schema, 'create_post_types' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Custom_Layouts_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
