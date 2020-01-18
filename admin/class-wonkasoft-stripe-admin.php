<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wonkasoft.com
 * @since      1.0.0
 *
 * @package    Wonkasoft_Stripe
 * @subpackage Wonkasoft_Stripe/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wonkasoft_Stripe
 * @subpackage Wonkasoft_Stripe/admin
 * @author     Wonkasoft <support@wonkasoft.com>
 */
class Wonkasoft_Stripe_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wonkasoft_Stripe_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wonkasoft_Stripe_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wonkasoft-stripe-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wonkasoft_Stripe_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wonkasoft_Stripe_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . 'admin-js', plugin_dir_url( __FILE__ ) . 'js/wonkasoft-stripe-admin.js', array( 'jquery' ), $this->version, true );

	}


	/**
	 * Loading required files.
	 */
	public function init_wonkasoft_stripe_gateway() {

		/**
		 * The class responsible for defining wonkasoft stripe gateway.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wonkasoft-stripe-wc-gateway.php';

		/**
		 * The Wonkasoft Stripe Payment Gateway.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/abstracts/abstract-wonkasoft-stripe-payment-gateway.php';

		/**
		 * The Wonkasoft Stripe webhook handler.
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wonkasoft-stripe-webhook-handler.php';
	}

	/**
	 * Register Woocommerce gateway.
	 *
	 * @since 1.0.0
	 * @param array $methods
	 */
	public function add_wonkasoft_stripe_gateways( $methods ) {
		$methods[] = 'Wonkasoft_Stripe_WC_Payment_Gateway';
		// $methods[] = 'Wonkasoft_Stripe_WC_Payment_Gateway_CC';

		return $methods;
	}

	/**
	 * This will load the ajax request handler file.
	 */
	public function wonkasoft_stripe_ajax_requests() {

		require_once WONKASOFT_STRIPE_PATH . 'admin/class-wonkasoft-stripe-payment-requests.php';
	}

	/**
	 * This function adds action links on the plugins
	 *
	 * @param  [type] $links contains the action links for the plugin.
	 * @return array $links returns array of action links for the plugin.
	 */
	public function wonkasoft_action_link_filter( $links ) {
		$links[] = '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wonkasoft_stripe" target="_self">Settings</a>';
		$links[] = '<a href="https://paypal.me/Wonkasoft" target="blank"><img src="' . plugins_url( '../img/wonka-logo.svg', __FILE__ ) . '" style="width: 20px; height: 20px; display: inline-block; vertical-align: text-top; float: none;" /></a>';
		return $links;
	}

	/**
	 * This function add links to the meta of the plugin
	 *
	 * @param  array $links contains the action links for the plugin.
	 * @param  file  $file  unknown.
	 * @return array $links returns array of action links for the plugin.
	 */
	public function wonkasoft_plugin_row_meta_filter( $links, $file ) {
		$links[] = '<a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=checkout&section=wonkasoft_stripe" target="_self">Settings</a>';
		$links[] = '<a href="https://paypal.me/Wonkasoft" target="blank">Donate <img src="' . plugins_url( '../img/wonka-logo.svg', __FILE__ ) . '" style="width: 20px; height: 20px; display: inline-block; vertical-align: text-top;" /></a>';
		return $links;
	}
}
