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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

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

		wp_enqueue_script( $this->plugin_name . 'public-js', plugin_dir_url( __FILE__ ) . 'js/wonkasoft-stripe-public.js', array( 'jquery' ), $this->version, true );

		if ( is_checkout() ) {
			wp_localize_script(
				$this->plugin_name . 'public-js',
				'WS_AJAX',
				array(
					'ws_send'     => esc_url( admin_url( 'admin-ajax.php' ) ),
					'ws_endpoint' => esc_url( rest_url( '/wonkasoft/v2' ) . '/wonkasoft-stripe-payment/' ),
					'nonces'      => array(
						'ws_request' => wp_create_nonce( 'ws_request' ),
						'wp_rest'    => wp_create_nonce( 'wp_rest' ),
					),
				)
			);
		}

	}

	/**
	 * Add Stipe Buttons
	 *
	 * @since 1.0.0
	 */
	public function add_wonkasoft_stripe_buttons() {
		$stripe                = new WC_Gateway_Wonkasoft_Stripe_Gateway();
		$stripe_payment_method = $stripe->get_option( 'payment_method' );
		$output                = array();

		if ( 'express' === $stripe_payment_method || 'express_normal' === $stripe_payment_method ) {
			$output['gpay']      = '<button type="button" id="g-pay-btn" class="wonka-btn">';
			$output['gpay']     .= '</button>';
			$output['applepay']  = '<button type="button" id="apple-pay-btn" class="wonka-btn">';
			$output['applepay'] .= '</button>';
			return $output;
		}
	}

	/**
	 * This function registers a rest end point.
	 */
	public function wc_rest_payment_endpoints() {
		/**
		 * Handle Payment Method request.
		 */
		register_rest_route(
			'wonkasoft/v2',
			'/wonkasoft-stripe-payment/',
			array(
				'methods'  => array( 'GET' ),
				'callback' => array( $this, 'wc_rest_payment_endpoint_handler' ),
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
	public function wc_rest_payment_endpoint_handler( $request = null ) {

		$parameters = $request->get_params();
		$ev         = wp_unslash( $parameters['this_ev'] );

		$payment_method = sanitize_text_field( $parameters['payment_method'] );
		$payment_token  = sanitize_text_field( $parameters['payment_token'] );
		$error          = new WP_Error();
		$response       = array(
			'payment_method' => $payment_method,
			'order_id'       => $order_id,
			'payment_token'  => $payment_token,
			'ev'             => $ev,
			'error'          => $error,
		);

		return new WP_REST_Response( $response );

		// <pre>
		// Array
		// (
		// [shippingOption] => Array
		// (
		// [amount] => 0
		// [label] => FedEx SmartPost Ground: FREE
		// [id] => free_shipping:14
		// [detail] =>
		// )

		// [shippingAddress] => Array
		// (
		// [addressLine] => Array
		// (
		// [0] => 25937 MARGARET AVE
		// )

		// [city] => MORENO VALLEY
		// [country] => US
		// [dependentLocality] =>
		// [organization] => Wonkasoft
		// [phone] => +19517436250
		// [postalCode] => 92551-7026
		// [recipient] => Rudy Lister
		// [region] => CA
		// [sortingCode] =>
		// )

		// [payerEmail] => rlister@wonkasoft.com
		// [payerPhone] => +19517436250
		// [payerName] => Rudy Lister
		// [methodName] => basic-card
		// [token] => Array
		// (
		// [id] => tok_1FjIISGUa6yKV42ulbNTKlq1
		// [object] => token
		// [card] => Array
		// (
		// [id] => card_1FjIISGUa6yKV42uaBpXdKQP
		// [object] => card
		// [address_city] => MORENO VALLEY
		// [address_country] => US
		// [address_line1] => 25937 MARGARET AVE
		// [address_line1_check] => unchecked
		// [address_line2] =>
		// [address_state] => CA
		// [address_zip] => 92551-7026
		// [address_zip_check] => unchecked
		// [brand] => Visa
		// [country] => US
		// [cvc_check] => unchecked
		// [dynamic_last4] =>
		// [exp_month] => 2
		// [exp_year] => 2022
		// [funding] => credit
		// [last4] => 4242
		// [metadata] => Array
		// (
		// )

		// [name] => Rudy TesterExpress
		// [tokenization_method] =>
		// )

		// [client_ip] => 76.168.236.90
		// [created] => 1574829516
		// [email] => rlister@wonkasoft.com
		// [livemode] =>
		// [type] => card
		// [used] =>
		// )

		// )
		// </pre>
	}

	/**
	 * This checks to see if Wonkasoft Stripe is set to only express payments.
	 *
	 * @param  array $available_gateways contains the available gateways.
	 * @return array returns available gateways.
	 */
	public function wonkasoft_stripe_check_for_express_only( $available_gateways ) {
		$wonkasoft_stripe_gw             = new WC_Gateway_Wonkasoft_Stripe_Gateway();
		$wonkasoft_stripe_payment_method = $wonkasoft_stripe_gw->get_option( 'payment_method' );

		if ( 'express' === $wonkasoft_stripe_payment_method && array_key_exists( 'wonkasoft_stripe', $available_gateways ) ) {
			unset( $available_gateways['wonkasoft_stripe'] );
		}

		return $available_gateways;
	}
}
