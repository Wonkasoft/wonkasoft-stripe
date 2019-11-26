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

		wp_localize_script(
			$this->plugin_name . 'public-js',
			'WS_AJAX',
			array(
				'ws_send'  => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'ws-request-nonce' ),
			)
		);

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
	public function wc_rest_payment_endpoints( $server ) {
		/**
		 * Handle Payment Method request.
		 */
		register_rest_route(
			'wc/v2',
			'/wonkasoft-stripe-payment/',
			array(
				'methods'  => array( WP_REST_Server::READABLE, WP_REST_Server::EDITABLE ),
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
		$response       = array();
		$parameters     = $request->get_params();
		$payment_method = sanitize_text_field( $parameters['payment_method'] );
		$order_id       = sanitize_text_field( $parameters['order_id'] );
		$payment_token  = sanitize_text_field( $parameters['payment_token'] );
		$error          = new WP_Error();
		echo "<pre>\n";
		print_r( $parameters );
		echo "</pre>\n";

		return new WP_REST_Response( $response );

	}
}
