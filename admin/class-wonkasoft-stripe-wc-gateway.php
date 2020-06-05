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
class Wonkasoft_Stripe_WC_Payment_Gateway extends WC_Payment_Gateway {

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

	/**
	 * The gateway settings.
	 *
	 * @var array
	 */
	public $form_fields = array();

	/**
	 * The gateway settings.
	 *
	 * @var array
	 */
	public $current_settings = array();

	/**
	 * types of gateway supports.
	 *
	 * @var array
	 */
	public $supports = array();

	public function __construct() {

		$this->id                 = 'wonkasoft_stripe';
		$this->has_fields         = true;
		$this->method_title       = 'Stripe Payments Express';
		$this->method_description = 'Add express payment options for your customers by the power of Stripe.';
		$this->init_form_fields();
		$this->init_settings();
		$this->title = $this->get_option( 'title' );
		$this->form_fields;

		foreach ( $this->form_fields as $key => $value ) :
			$this->current_settings[ $key ] = $this->get_option( $key );
		endforeach;

		update_option( 'wonkasoft_stripe_settings', $this->current_settings, $this->current_settings );
		$this->supports = array(
			'products',
			'refunds',
			'tokenization',
			'add_payment_method',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
			'multiple_subscriptions',
			'pre-orders',
		);

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = apply_filters(
			'wonkasoft_wc_stripe_settings',
			array(
				'enabled'                 => array(
					'title'       => __( 'Enable/Disable', 'wonkasoft-stripe' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable Stripe Express Payment', 'wonkasoft-stripe' ),
					'description' => __( 'This controls the title which the user sees during checkout.', 'wonkasoft-stripe' ),
					'default'     => 'no',
					'desc_tip'    => true,
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
				'webhook'                 => array(
					'title'       => __( 'Webhook Endpoints', 'wonkasoft-stripe' ),
					'type'        => 'title',
					/*
					 translators: webhook URL */
					'description' => $this->display_admin_settings_webhook_description(),
				),
				'select_mode'             => array(
					'title'       => __( 'Select Mode', 'wonkasoft-stripe' ),
					'type'        => 'select',
					'label'       => __( 'Select Live or Sandbox Modes', 'wonkasoft-stripe' ),
					'description' => __( 'Place the payment gateway in test mode using test API keys.', 'wonkasoft-stripe' ),
					'options'     => array(
						'sandbox_mode' => __( 'Sandbox', 'wonkasoft-stripe' ),
						'live_mode'    => __( 'Live', 'wonkasoft-stripe' ),
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
				'stripe_account_id'       => array(
					'title'       => __( 'Stripe Account Id', 'wonkasoft-stripe' ),
					'type'        => 'password',
					'description' => __( 'Get your Stripe Account Id', 'wonkasoft-stripe' ),
					'default'     => '',
					'placeholder' => __( 'Get your Stripe Account Id', 'wonkasoft-stripe' ),
					'desc_tip'    => true,
				),
				'button_placement'        => array(
					'title'       => __( 'Hook for button placement', 'wonkasoft-stripe' ),
					'type'        => 'text',
					'description' => __( 'Place hook here for where to parse buttons.', 'wonkasoft-stripe' ),
					'label'       => __( 'Show buttons hook', 'wonkasoft-stripe' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'payment_method'          => array(
					'title'       => __( 'Payment Method', 'wonkasoft-stripe' ),
					'type'        => 'select',
					'description' => __( 'Please choose payment method between express, normal, or both.', 'wonkasoft-stripe' ),
					'label'       => __( 'Payment Method Choices.', 'wonkasoft-stripe' ),
					'options'     => array(
						'express'        => __( 'Express', 'wonkasoft-stripe' ),
						'normal'         => __( 'Normal', 'wonkasoft-stripe' ),
						'express_normal' => __( 'Express & Normal', 'wonkasoft-stripe' ),
					),
					'default'     => 'express',
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
			)
		);
	}

	/**
	 * [display_admin_settings_webhook_description description]
	 *
	 * @return [type] [description]
	 */
	public function display_admin_settings_webhook_description() {
		/* translators: 1) webhook url */
		return sprintf( __( 'You must add the following webhook endpoint <strong style="background-color:#ddd;">&nbsp;%s&nbsp;</strong> to your <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe account settings</a>. This will enable you to receive notifications on the charge statuses.', 'wonkasoft-stripe' ), $this->get_webhook_url() );
	}

	/**
	 * Gets the webhook URL for Stripe triggers. Used mainly for
	 * asyncronous redirect payment methods in which statuses are
	 * not immediately chargeable.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return string
	 */
	public static function get_webhook_url() {
		return add_query_arg( 'wc-api', 'wonkasoft_stripe', trailingslashit( get_home_url() ) );
	}

	/**
	 * Payment fields for Wonkasoft Stripe.
	 *
	 * @see WC_Payment_Gateway::payment_fields()
	 */
	public function payment_fields() {
		?>
		<fieldset>
			<div id="wonkasoft-stripe-payment-field"></div>
		</fieldset>
		<?php
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
			// $order->payment_complete();

			// Remove cart.
			// $woocommerce->cart->empty_cart();

			$order->update_status( 'processing', __( 'Stripe payment completed', 'wonkasoft-stripe' ) );
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

	/**
	 * This will echo notices to parse in the woocommerce notices div.
	 */
	public function parse_woocommerce_notices( $msg ) {

		echo $this->get_parse_woocommerce_notices( $msg );
	}

	/**
	 * This will get notices to parse in the woocommerce notices div.
	 */
	public function get_parse_woocommerce_notices( $msg ) {

		$output  = '';
		$output .= '<script>';
		$output .= '( function( $ ) {';
		$output .= 'window.addEventListener( "load", function() {';
		$output .= 'if ( document.querySelector( ".woocommerce-notices-wrapper" ) ) {';
		$output .= 'var notices_wrapper = document.querySelector( ".woocommerce-notices-wrapper" );';
		$output .= "var notice_msg = '<span>$msg</span>';";
		$output .= 'notices_wrapper.innerHTML += notice_msg;';
		$output .= '}';
		$output .= '});';
		$output .= '})( jQuery );';
		$output .= '</script>';

		return $output;
	}

	/**
	 * This will set where to parse the buttons.
	 */
	public function parse_buttons_on_hook() {
		$hook_for_parse = $this->get_option( 'button_placement' );

		add_action( $hook_for_parse, array( $this, 'wonkasoft_stripe_button_elements' ), 10 );
	}

	/**
	 * This will echo elements for button parse.
	 */
	public function wonkasoft_stripe_button_elements() {
		echo $this->get_wonkasoft_stripe_button_elements();
	}

	/**
	 * This will get elements for button parse.
	 */
	public function get_wonkasoft_stripe_button_elements() {

		$output  = '';
		$output .= '<div id="wonkasoft-payment-request-button">';
		$output .= '</div>';

		return $output;
	}

	/**
	 * This will make gateway available.
	 */
	public function is_available() {

		return parent::is_available();
	}
}
