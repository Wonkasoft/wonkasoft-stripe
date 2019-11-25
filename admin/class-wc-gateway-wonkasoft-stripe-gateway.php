<?php
/**
 * Wonkasoft Stripe Gateway Class
 *
 * @package Wonkasoft_Stripe
 * @author Wonkasoft, LLC <support@wonkasoft.com>
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * This the class for Wonkasoft Strip Gateway.
 */
class WC_Gateway_Wonkasoft_Stripe_Gateway extends WC_Payment_Gateway {

	/**
	 * ID of the gateway.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * The gateway icon.
	 *
	 * @var string
	 */
	public $icon = '';

	/**
	 * The gateway has fields.
	 *
	 * @var null
	 */
	public $has_fields = null;

	/**
	 * The gateway title.
	 *
	 * @var string
	 */
	public $method_title = '';

	/**
	 * The gateway description.
	 *
	 * @var string
	 */
	public $method_description = '';

	public function __construct() {

		$this->id                 = 'wonkasoft_stripe';
		$this->icon               = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/img/ws-stripe-preview.jpg';
		$this->has_fields         = true;
		$this->method_title       = 'Stripe Payments Express';
		$this->method_description = 'Add express payment options for your customers by the power of Stripe.';
		$this->init_form_fields();
		$this->init_settings();
		$this->title = $this->get_option( 'title' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'                 => array(
				'title'   => __( 'Enable/Disable', 'wonkasoft-stripe' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Stripe Express Payment', 'wonkasoft-stripe' ),
				'default' => 'yes',
			),
			'title'                   => array(
				'title'       => __( 'Title', 'wonkasoft-stripe' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wonkasoft-stripe' ),
				'default'     => __( 'Stripe Payment', 'wonkasoft-stripe' ),
				'placeholder' => __( 'Stripe Credit Cards' ),
				'desc_tip'    => true,
			),
			'description'             => array(
				'title'       => __( 'Description', 'wonkasoft-stripe' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wonkasoft-stripe' ),
				'default'     => 'Pay with your credit card via Stripe.',
				'placeholder' => __( 'Pay with your credit card via Stripe.', 'wonkasoft-stripe' ),
				'desc_tip'    => true,
			),
			'select_mode'             => array(
				'title'       => __( 'Select Mode', 'wonkasoft-stripe' ),
				'type'        => 'select',
				'label'       => __( 'Select Live or Sandbox Modes', 'wonkasoft-stripe' ),
				'description' => __( 'Place the payment gateway in test mode using test API keys.', 'wonkasoft-stripe' ),
				'options'     => array(
					'test_mode' => 'Test Sandbox Mode',
					'live_mode' => 'Live Mode',
				),
				'default'     => array(),
				'desc_tip'    => true,
			),
			'test_publishable_key'    => array(
				'title'       => __( 'Test Publishable Key', 'wonkasoft-stripe' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'default'     => '',
				'placeholder' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'desc_tip'    => true,
			),
			'test_secret_key'         => array(
				'title'       => __( 'Test Secret Key', 'wonkasoft-stripe' ),
				'type'        => 'password',
				'description' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'default'     => '',
				'placeholder' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'desc_tip'    => true,
			),
			'test_webhook_secret'     => array(
				'title'       => __( 'Test Webhook Secret', 'wonkasoft-stripe' ),
				'type'        => 'password',
				'description' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'default'     => '',
				'placeholder' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'desc_tip'    => true,
			),

			'enabled_request_buttons' => array(
				'title'       => __( 'Payment Request Buttons', 'wonkasoft-stripe' ),
				'type'        => 'checkbox',
				'description' => __( 'If enabled, users will be able to pay using Apple Pay or Chrome Payment Request if supported by the browser.', 'wonkasoft-stripe' ),
				'label'       => __( "Enable Payment Request Buttons. (Apple Pay/Chrome Payment Request API) By using Apple Pay, you agree to <a href='https://stripe.com/apple-pay/legal' target='_blank'>Stripe</a> and <a href='https://developer.apple.com/apple-pay/acceptable-use-guidelines-for-websites/' target='_blank'>Apple's</a> terms of service", 'wonkasoft-stripe' ),
				'default'     => '',
				'desc_tip'    => true,
			),

			'live_publishable_key'    => array(
				'title'       => __( 'Live Publishable Key', 'wonkasoft-stripe' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'default'     => '',
				'placeholder' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'desc_tip'    => true,
			),
			'live_secret_key'         => array(
				'title'       => __( 'Live Secret Key', 'wonkasoft-stripe' ),
				'type'        => 'password',
				'description' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'default'     => '',
				'placeholder' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'desc_tip'    => true,
			),
			'live_webhook_secret'     => array(
				'title'       => __( 'Live Webhook Secret', 'wonkasoft-stripe' ),
				'type'        => 'password',
				'description' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'default'     => '',
				'placeholder' => __( 'Get your API keys from your Stripe Account', 'wonkasoft-stripe' ),
				'desc_tip'    => true,
			),

			'enabled_capture'         => array(
				'title'       => __( 'Capture', 'wonkasoft-stripe' ),
				'type'        => 'checkbox',
				'description' => __( 'Whether or not to immediately capture the charge. When uncheck, the charge issues an authorization and will need to be captured later. Uncaptured charges expire in 7 days.', 'wonkasoft-stripe' ),
				'label'       => __( 'Capture charge immediately.', 'wonkasoft-stripe' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'enabled_logging'         => array(
				'title'       => __( 'Loggin', 'wonkasoft-stripe' ),
				'type'        => 'checkbox',
				'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'wonkasoft-stripe' ),
				'label'       => __( 'Log debug messages', 'wonkasoft-stripe' ),
				'default'     => '',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * This processes payment.
	 *
	 * @param  number $order_id contains the order number to process.
	 * @return array           returns the result and redirect.
	 */
	public function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );

		// Mark as on-hold (we're awaiting the Stripe).
		$order->update_status( 'on-hold', __( 'Awaiting Stripe payment', 'wonkasoft-stripe' ) );

		try {

			$order->payment_complete();

			// Remove cart.
			$woocommerce->cart->empty_cart();

			// Return thank you redirect.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		} catch ( Exception $e ) {

			$error_message = $e;

			wc_add_notice( __( 'Payment error:', 'wonkasoft-stripe' ) . $error_message, 'error' );

			return;
		}

	}
}
