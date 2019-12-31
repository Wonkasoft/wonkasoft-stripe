<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wonkasoft.com
 * @since      1.0.0
 *
 * @package    Wonkasoft_Stripe
 * @subpackage Wonkasoft_Stripe/public
 */

use includes\stripe\Stripe\Stripe;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wonkasoft_Stripe
 * @subpackage Wonkasoft_Stripe/public
 * @author     Wonkasoft <support@wonkasoft.com>
 */
class Wonkasoft_Stripe_Public {

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
	 * The woocommerce available gateways.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $current_available_gateways    The current available gateways.
	 */
	private $current_available_gateways;

	/**
	 * The public instance of the wonkasoft stripe gateway.
	 *
	 * @var object
	 */
	public $ws_gateway;

	/**
	 * The Wonkasoft Stripe published api key.
	 *
	 * @var string
	 */
	public $ws_pk;

	/**
	 * Gets loaded with stripe account id.
	 *
	 * @var string
	 */
	public $ws_stripe_account_id;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		if ( class_exists( 'Wonkasoft_Stripe_WC_Payment_Gateway' ) && empty( $this->ws_gateway ) ) {
			$this->init_public_wonkasoft_stripe_gateway();
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wonkasoft-stripe-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		if ( is_checkout() ) {
			wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', '', '3.0', true );
			wp_enqueue_script( $this->plugin_name . 'public-js', plugin_dir_url( __FILE__ ) . 'js/wonkasoft-stripe-public.js', array( 'jquery' ), $this->version, true );
			$wonkasoft_stripe_params = array(
				'ws_ajax'            => str_replace( '/aperabags.com', '', get_site_url() ) . WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'ws_charge_endpoint' => esc_url( rest_url( '/wonkasoft/v2' ) . '/wonkasoft-stripe-payment/' ),
				'stripe'             => array(
					'key'        => $this->ws_pk,
					'account_id' => $this->ws_stripe_account_id,
					'btns'       => $this->add_wonkasoft_stripe_buttons(),
				),
				'nonces'             => array(
					'ws_payment'         => wp_create_nonce( 'ws_payment' ),
					'ws_checkout'        => wp_create_nonce( 'woocommerce-process_checkout' ),
					'ws_shipping'        => wp_create_nonce( 'ws_shipping' ),
					'ws_update_shipping' => wp_create_nonce( 'ws_update_shipping' ),
					'ws_request'         => wp_create_nonce( 'ws_request' ),
					'wp_rest'            => wp_create_nonce( 'wp_rest' ),
				),
				'checkout'           => array(
					'url' => wc_get_checkout_url(),
				),
			);
			wp_localize_script(
				$this->plugin_name . 'public-js',
				'WS_STRIPE',
				apply_filters( 'wonkasoft_stripe_params', $wonkasoft_stripe_params )
			);
		} else {
			wp_enqueue_script( $this->plugin_name . 'public-js', plugin_dir_url( __FILE__ ) . 'js/wonkasoft-stripe-public.js', array( 'jquery' ), $this->version, true );
		}

	}

	/**
	 * This sets the Wonkasoft Stripe Gateway public instance.
	 */
	public function init_public_wonkasoft_stripe_gateway() {
		$this->ws_gateway = new Wonkasoft_Stripe_WC_Payment_Gateway();
		$select_mode      = $this->ws_gateway->get_option( 'select_mode' );
		if ( 'sandbox_mode' === $select_mode ) {
			$this->ws_pk = $this->ws_gateway->get_option( 'test_publishable_key' );
		} else {
			$this->ws_pk = $this->ws_gateway->get_option( 'live_publishable_key' );
		}
		$this->ws_stripe_account_id = $this->ws_gateway->get_option( 'stripe_account_id' );

	}

	/**
	 * Add Stipe Buttons
	 *
	 * @since 1.0.0
	 */
	public function add_wonkasoft_stripe_buttons() {
		$output              = array();
		$output['gpay']      = '<button type="button" id="g-pay-btn" class="wonka-btn">';
		$output['gpay']     .= '</button>';
		$output['applepay']  = '<button type="button" id="apple-pay-btn" class="wonka-btn">';
		$output['applepay'] .= '</button>';
		return $output;
	}

	/**
	 * This function registers a rest end point.
	 */
	public function wonkasoft_rest_payment_endpoints() {
		/**
		 * Handle Payment Method request.
		 */
		register_rest_route(
			'wonkasoft/v2',
			'/wonkasoft-stripe-payment/',
			array(
				'methods'  => array( 'GET', 'POST' ),
				'callback' => array( $this, 'wonkasoft_rest_payment_endpoint_handler' ),
			),
			false
		);
	}

	/**
	 * Handler for the payment endpoint.
	 *
	 * @param  array $request contains the request sent to the end point.
	 * @return [type]          [description]
	 */
	public function wonkasoft_rest_payment_endpoint_handler( $request = null ) {

		$parameters = $request->get_params();
		$ev         = wp_unslash( $parameters['this_ev'] );
		echo "<pre>\n";
		print_r( $parameters );
		echo "</pre>\n";

		// $order_id       = WC()->wc_create_order();
		$payment_method = sanitize_text_field( $parameters['payment_method'] );
		$payment_token  = sanitize_text_field( $parameters['token'] );
		$error          = new WP_Error();
		$response       = array(
			'payment_method' => $payment_method,
			'order_id'       => $order_id,
			'payment_token'  => $payment_token,
			'ev'             => $ev,
			'error'          => $error,
		);

		return new WP_REST_Response( $response );
	}

	/**
	 * Loading Stripe once user lands on the checkout page.
	 *
	 * @param  object $checkout variable is empty at this time.
	 */
	public function wonkasoft_stripe_checkout_init( $checkout ) {

		$wonkasoft_stripe_enabled = $this->ws_gateway->get_option( 'enabled' );

		if ( 'yes' !== $wonkasoft_stripe_enabled ) {
			return;
		}

		$wonkasoft_stripe_select_mode = $this->ws_gateway->get_option( 'select_mode' );

		if ( 'sandbox_mode' === $wonkasoft_stripe_select_mode ) {
			$wonkasoft_stripe_key = $this->ws_gateway->get_option( 'test_secret_key' );
		} else {
			$wonkasoft_stripe_key = $this->ws_gateway->get_option( 'live_secret_key' );
		}

		$stripe = new \Stripe\Stripe();
		\Stripe\Stripe::setAppInfo(
			'Wonkasoft Stripe for WooCommerce',
			$this->version,
			'https://wonkasoft.com/wonkasoft-stripe',
			''
		);

		if ( empty( $wonkasoft_stripe_key ) ) {
			$this->ws_gateway->parse_woocommerce_notices( 'Your Stripe Api Key has not been set in the Wonkasoft Stripe settings.' );
		}

		\Stripe\Stripe::setApiKey( $wonkasoft_stripe_key );
		\Stripe\Stripe::setApiVersion( '2019-11-05' );

		if ( ! isset( $_SERVER['HTTPS'] ) ) {
			$this->ws_gateway->parse_woocommerce_notices( 'There is something that is preventing your connection from being secure.' );
		}

		$current_domain = str_replace( 'https://', '', get_site_url() );

		try {
			\Stripe\ApplePayDomain::create(
				array(
					'domain_name' => $current_domain,
				)
			);

		} catch ( Exception $e ) {
			$this->ws_gateway->parse_woocommerce_notices( $e );
		}

		$this->ws_gateway->parse_buttons_on_hook();

	}

	/**
	 * This checks to see if Wonkasoft Stripe is set to only express payments.
	 *
	 * @param  array $available_gateways contains the available gateways.
	 * @return array returns available gateways.
	 */
	public function wonkasoft_stripe_check_for_express_only( $available_gateways ) {
		$wonkasoft_stripe_payment_method = $this->ws_gateway->get_option( 'payment_method' );

		// if ( 'express' === $wonkasoft_stripe_payment_method && array_key_exists( 'wonkasoft_stripe', $available_gateways ) ) {
		// unset( $available_gateways['wonkasoft_stripe'] );
		// }

		return $available_gateways;
	}
}
