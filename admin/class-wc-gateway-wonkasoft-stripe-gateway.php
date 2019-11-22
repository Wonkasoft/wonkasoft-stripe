<?php
/**
 * Wonkasoft Stripe Gateway Class
 *
 * @package
 * @author Wonkasoft, LLC <support@wonkasoft.com>
 * @since 1.0.0
 */

defined( 'ABSPATH' ) or exit;

/**
 *
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
		$this->icon               = plugin_dir_path( dirname( __FILE__ ) ) . 'admin/img/wonka-logo.svg';
		$this->has_fields         = false;
		$this->method_title       = 'Stripe Payments Express';
		$this->method_description = 'Add express payment options for your customers by the power of Stripe.';
		// $this->init_form_fields();
		// $this->init_settings();
	}
}
