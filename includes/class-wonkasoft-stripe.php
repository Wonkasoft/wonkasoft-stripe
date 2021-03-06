<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wonkasoft.com
 * @since      1.0.0
 *
 * @package    Wonkasoft_Stripe
 * @subpackage Wonkasoft_Stripe/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wonkasoft_Stripe
 * @subpackage Wonkasoft_Stripe/includes
 * @author     Wonkasoft <support@wonkasoft.com>
 */
class Wonkasoft_Stripe {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wonkasoft_Stripe_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'WONKASOFT_STRIPE_VERSION' ) ) {
			$this->version = WONKASOFT_STRIPE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wonkasoft-stripe';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wonkasoft_Stripe_Loader. Orchestrates the hooks of the plugin.
	 * - Wonkasoft_Stripe_i18n. Defines internationalization functionality.
	 * - Wonkasoft_Stripe_Admin. Defines all hooks for the admin area.
	 * - Wonkasoft_Stripe_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wonkasoft-stripe-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wonkasoft-stripe-i18n.php';

		/**
		 * The Wonkasoft Stripe logger handler.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wonkasoft-stripe-logger.php';

		/**
		 * The Wonkasoft Stripe Helper.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wonkasoft-stripe-helper.php';

		/**
		 * The Wonkasoft Stripe Customer.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wonkasoft-stripe-customer.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wonkasoft-stripe-admin.php';

		/**
		 * The init file for stripe.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/stripe/init.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wonkasoft-stripe-public.php';

		$this->loader = new Wonkasoft_Stripe_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wonkasoft_Stripe_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wonkasoft_Stripe_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wonkasoft_Stripe_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'init_wonkasoft_stripe_gateway' );
		$this->loader->add_action( 'init', $plugin_admin, 'wonkasoft_stripe_ajax_requests' );

		$this->loader->add_filter( 'woocommerce_payment_gateways', $plugin_admin, 'add_wonkasoft_stripe_gateways' );
		$this->loader->add_filter( 'plugin_action_links_' . WONKASOFT_STRIPE_BASENAME, $plugin_admin, 'wonkasoft_action_link_filter', 10, 1 );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'wonkasoft_plugin_row_meta_filter', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wonkasoft_Stripe_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 50 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts', 50 );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'wonkasoft_rest_payment_endpoints', 50 );
		$this->loader->add_action( 'plugins_loaded', $plugin_public, 'init_public_wonkasoft_stripe_gateway' );
		$this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public, 'wonkasoft_stripe_checkout_init', 50 );

		$this->loader->add_filter( 'woocommerce_available_payment_gateways', $plugin_public, 'wonkasoft_stripe_check_for_express_only', 10 );
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
	 * @return    Wonkasoft_Stripe_Loader    Orchestrates the hooks of the plugin.
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
