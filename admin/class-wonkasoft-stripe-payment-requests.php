<?php
/**
 * This file contains the class that handles ajax requests.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wonkasoft_Stripe_Payment_Requests' ) ) {

	/**
	 * The Class for Wonkasoft Stripe Ajax Requests.
	 */
	class Wonkasoft_Stripe_Payment_Requests {

		/**
		 * Setting descriptors.
		 *
		 * @var string
		 */
		public $total_label;

		/**
		 * This Instance.
		 *
		 * @var class
		 */
		private static $_this;

		/**
		 * This inits the ajax requests.
		 */
		public function __construct() {
			self::$_this       = $this;
			$this->total_label = str_replace( "'", '', $this->total_label ) . apply_filters( 'wonkasoft_stripe_payment_request_total_label_suffix', ' (via WooCommerce)' );
			add_action( 'template_redirect', array( $this, 'set_session' ) );
			$this->init();
		}

		/**
		 * Get this instance.
		 *
		 * @since 1.0.0
		 * @return class
		 */
		public static function instance() {
			return self::$_this;
		}

		/**
		 * Sets the WC customer session if one is not set.
		 * This is needed so nonces can be verified by AJAX Request.
		 *
		 * @since 1.0.0
		 */
		public function set_session() {
			if ( ! is_product() || ( isset( WC()->session ) && WC()->session->has_session() ) ) {
				return;
			}

			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
			$wc_session    = new $session_class();

			if ( version_compare( WC_VERSION, '3.3', '>=' ) ) {
				$wc_session->init();
			}

			$wc_session->set_customer_session_cookie( true );
		}

		/**
		 * Initialize hooks.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function init() {
			add_action( 'wc_ajax_wonkasoft_stripe_get_cart_details', array( $this, 'ajax_get_cart_details' ), 10 );
			add_action( 'wc_ajax_nopriv_wonkasoft_stripe_get_cart_details', array( $this, 'ajax_get_cart_details' ), 10 );

			add_action( 'wc_ajax_wonkasoft_stripe_get_shipping_options', array( $this, 'ajax_get_shipping_options' ), 10 );
			add_action( 'wc_ajax_nopriv_wonkasoft_stripe_get_shipping_options', array( $this, 'ajax_get_shipping_options' ), 10 );

			add_action( 'wc_ajax_wonkasoft_stripe_update_shipping_method', array( $this, 'ajax_update_shipping_method' ), 10 );
			add_action( 'wc_ajax_nopriv_wonkasoft_stripe_update_shipping_method', array( $this, 'ajax_update_shipping_method' ), 10 );

			add_action( 'wc_ajax_wonkasoft_stripe_create_order', array( $this, 'ajax_create_order' ), 10 );
			add_action( 'wc_ajax_nopriv_wonkasoft_stripe_create_order', array( $this, 'ajax_create_order' ), 10 );

			add_filter( 'woocommerce_gateway_title', array( $this, 'filter_gateway_title' ), 10, 2 );
			add_filter( 'woocommerce_validate_postcode', array( $this, 'postal_code_validation' ), 10, 3 );

			add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_order_meta' ), 10, 2 );
		}

		/**
		 * Filters the gateway title to reflect Payment Request type
		 */
		public function filter_gateway_title( $title, $id ) {
			global $post;

			if ( ! is_object( $post ) ) {
				return $title;
			}

			if ( Wonkasoft_Stripe_Helper::is_wc_lt( '3.0' ) ) {
				$method_title = get_post_meta( $post->ID, '_payment_method_title', true );
			} else {
				$order        = wc_get_order( $post->ID );
				$method_title = is_object( $order ) ? $order->get_payment_method_title() : '';
			}

			if ( 'stripe' === $id && ! empty( $method_title ) && 'Apple Pay (Stripe)' === $method_title ) {
				return $method_title;
			}

			if ( 'stripe' === $id && ! empty( $method_title ) && 'Chrome Payment Request (Stripe)' === $method_title ) {
				return $method_title;
			}

			return $title;
		}

		/**
		 * Removes postal code validation from WC.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function postal_code_validation( $valid, $postcode, $country ) {
			$gateways = WC()->payment_gateways->get_available_payment_gateways();

			if ( ! isset( $gateways['stripe'] ) ) {
				return $valid;
			}

			$payment_request_type = isset( $_POST['payment_request_type'] ) ? wc_clean( $_POST['payment_request_type'] ) : '';

			if ( 'apple_pay' !== $payment_request_type ) {
				return $valid;
			}

			/**
			 * Currently Apple Pay truncates postal codes from UK and Canada to first 3 characters
			 * when passing it back from the shippingcontactselected object. This causes WC to invalidate
			 * the order and not let it go through. The remedy for now is just to remove this validation.
			 * Note that this only works with shipping providers that don't validate full postal codes.
			 */
			if ( 'GB' === $country || 'CA' === $country ) {
				return true;
			}

			return $valid;
		}

		/**
		 * Add needed order meta
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 * @param int   $order_id
		 * @param array $posted_data The posted data from checkout form.
		 */
		public function add_order_meta( $order_id, $posted_data ) {
			if ( empty( $_POST['payment_request_type'] ) ) {
				return;
			}

			$order = wc_get_order( $order_id );

			$payment_request_type = wc_clean( $_POST['payment_request_type'] );

			if ( 'apple_pay' === $payment_request_type ) {
				if ( Wonkasoft_Stripe_Helper::is_wc_lt( '3.0' ) ) {
					update_post_meta( $order_id, '_payment_method_title', 'Apple Pay (Stripe)' );
				} else {
					$order->set_payment_method_title( 'Apple Pay (Stripe)' );
					$order->save();
				}
			}

			if ( 'payment_request_api' === $payment_request_type ) {
				if ( Wonkasoft_Stripe_Helper::is_wc_lt( '3.0' ) ) {
					update_post_meta( $order_id, '_payment_method_title', 'Chrome Payment Request (Stripe)' );
				} else {
					$order->set_payment_method_title( 'Chrome Payment Request (Stripe)' );
					$order->save();
				}
			}
		}

		/**
		 * Normalizes the state/county field because in some
		 * cases, the state/county field is formatted differently from
		 * what WC is expecting and throws an error. An example
		 * for Ireland the county dropdown in Chrome shows "Co. Clare" format
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function normalize_state() {
			$billing_country  = ! empty( $_POST['billing_country'] ) ? wc_clean( $_POST['billing_country'] ) : '';
			$shipping_country = ! empty( $_POST['shipping_country'] ) ? wc_clean( $_POST['shipping_country'] ) : '';
			$billing_state    = ! empty( $_POST['billing_state'] ) ? wc_clean( $_POST['billing_state'] ) : '';
			$shipping_state   = ! empty( $_POST['shipping_state'] ) ? wc_clean( $_POST['shipping_state'] ) : '';

			if ( $billing_state && $billing_country ) {
				$valid_states = WC()->countries->get_states( $billing_country );

				// Valid states found for country.
				if ( ! empty( $valid_states ) && is_array( $valid_states ) && sizeof( $valid_states ) > 0 ) {
					foreach ( $valid_states as $state_abbr => $state ) {
						if ( preg_match( '/' . preg_quote( $state ) . '/i', $billing_state ) ) {
							$_POST['billing_state'] = $state_abbr;
						}
					}
				}
			}

			if ( $shipping_state && $shipping_country ) {
				$valid_states = WC()->countries->get_states( $shipping_country );

				// Valid states found for country.
				if ( ! empty( $valid_states ) && is_array( $valid_states ) && sizeof( $valid_states ) > 0 ) {
					foreach ( $valid_states as $state_abbr => $state ) {
						if ( preg_match( '/' . preg_quote( $state ) . '/i', $shipping_state ) ) {
							$_POST['shipping_state'] = $state_abbr;
						}
					}
				}
			}
		}

		/**
		 * Create order. Security is handled by WC.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		public function ajax_create_order() {
			if ( WC()->cart->is_empty() ) {
				wp_send_json_error( __( 'Empty cart', 'wonkasoft-stripe' ) );
			}

			if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
				define( 'WOOCOMMERCE_CHECKOUT', true );
			}

			$this->normalize_state();

			WC()->checkout()->process_checkout();

			die( 0 );
		}

		/**
		 * Update shipping method
		 */
		public function ajax_update_shipping_method() {
			check_ajax_referer( 'ws_update_shipping', 'security' );

			if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
				define( 'WOOCOMMERCE_CART', true );
			}

			$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
			$shipping_method         = filter_input( INPUT_POST, 'shipping_method', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			if ( is_array( $shipping_method ) ) {
				foreach ( $shipping_method as $i => $value ) {
					$chosen_shipping_methods[ $i ] = wc_clean( $value );
				}
			}

			WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

			WC()->cart->calculate_totals();

			$data           = array();
			$data          += $this->build_display_items();
			$data['result'] = 'success';

			wp_send_json( $data );
		}

		/**
		 * Getting shipping options.
		 */
		public function ajax_get_shipping_options() {

			check_ajax_referer( 'ws_shipping', 'security' );

			try {
				// Set the shipping package.
				$posted = filter_input_array(
					INPUT_POST,
					array(
						'country'   => FILTER_SANITIZE_STRING,
						'state'     => FILTER_SANITIZE_STRING,
						'postcode'  => FILTER_SANITIZE_STRING,
						'city'      => FILTER_SANITIZE_STRING,
						'address'   => FILTER_SANITIZE_STRING,
						'address_2' => FILTER_SANITIZE_STRING,
					)
				);

				$this->calculate_shipping( apply_filters( 'wonkasoft_stripe_payment_request_shipping_posted_values', $posted ) );

				// Set the shipping options.
				$data     = array();
				$packages = WC()->shipping->get_packages();

				if ( ! empty( $packages ) && WC()->customer->has_calculated_shipping() ) {
					foreach ( $packages as $package_key => $package ) {
						if ( empty( $package['rates'] ) ) {
							throw new Exception( __( 'Unable to find shipping method for address.', 'wonkasoft-stripe' ) );
						}

						foreach ( $package['rates'] as $key => $rate ) {
							$data['shipping_options'][] = array(
								'id'     => $rate->id,
								'label'  => $rate->label,
								'detail' => '',
								'amount' => $this->get_stripe_amount( $rate->cost ),
							);
						}
					}
				} else {
					throw new Exception( __( 'Unable to find shipping method for address.', 'wonkasoft-stripe' ) );
				}

				if ( isset( $data[0] ) ) {
					// Auto select the first shipping method.
					WC()->session->set( 'chosen_shipping_methods', array( $data[0]['id'] ) );
				}

				WC()->cart->calculate_totals();

				$data          += $this->build_display_items();
				$data['result'] = 'success';

				wp_send_json( $data );
			} catch ( Exception $e ) {
				$data          += $this->build_display_items();
				$data['result'] = 'invalid_shipping_address';

				wp_send_json( $data );
			}
		}

		/**
		 * Calculate and set shipping method.
		 *
		 * @since 3.1.0
		 * @version 4.0.0
		 * @param array $address
		 */
		protected function calculate_shipping( $address = array() ) {
			$country   = $address['country'];
			$state     = $address['state'];
			$postcode  = $address['postcode'];
			$city      = $address['city'];
			$address_1 = $address['address'];
			$address_2 = $address['address_2'];
			$wc_states = WC()->countries->get_states( $country );

			/**
			 * In some versions of Chrome, state can be a full name. So we need
			 * to convert that to abbreviation as WC is expecting that.
			 */
			if ( 2 < strlen( $state ) && ! empty( $wc_states ) ) {
				$state = array_search( ucwords( strtolower( $state ) ), $wc_states, true );
			}

			WC()->shipping->reset_shipping();

			if ( $postcode && WC_Validation::is_postcode( $postcode, $country ) ) {
				$postcode = wc_format_postcode( $postcode, $country );
			}

			if ( $country ) {
				WC()->customer->set_location( $country, $state, $postcode, $city );
				WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
			}

			WC()->customer->set_calculated_shipping( true );
			WC()->customer->save();

			$packages = array();

			$packages[0]['contents']                 = WC()->cart->get_cart();
			$packages[0]['contents_cost']            = 0;
			$packages[0]['applied_coupons']          = WC()->cart->applied_coupons;
			$packages[0]['user']['ID']               = get_current_user_id();
			$packages[0]['destination']['country']   = $country;
			$packages[0]['destination']['state']     = $state;
			$packages[0]['destination']['postcode']  = $postcode;
			$packages[0]['destination']['city']      = $city;
			$packages[0]['destination']['address']   = $address_1;
			$packages[0]['destination']['address_2'] = $address_2;

			foreach ( WC()->cart->get_cart() as $item ) {
				if ( $item['data']->needs_shipping() ) {
					if ( isset( $item['line_total'] ) ) {
						$packages[0]['contents_cost'] += $item['line_total'];
					}
				}
			}

			$packages = apply_filters( 'woocommerce_cart_shipping_packages', $packages );

			WC()->shipping->calculate_shipping( $packages );
		}


		/**
		 * This request gets the cart details.
		 */
		public function ajax_get_cart_details() {

			check_ajax_referer( 'ws_request', 'security' );

			if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
				define( 'WOOCOMMERCE_CART', true );
			}

			WC()->cart->calculate_totals();

			$currency = get_woocommerce_currency();

			// Set mandatory payment details.
			$data = array(
				'shipping_required' => WC()->cart->needs_shipping(),
				'order_data'        => array(
					'currency'     => strtolower( $currency ),
					'country_code' => substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
				),
			);

			$data['order_data'] += $this->build_display_items();

			wp_send_json( $data );
		}

		/**
		 * Builds the line items to pass to Payment Request
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		protected function build_display_items() {
			if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
				define( 'WOOCOMMERCE_CART', true );
			}

			$items     = array();
			$subtotal  = 0;
			$discounts = 0;

			// Default show only subtotal instead of itemization.
			if ( ! apply_filters( 'wonkasoft_stripe_payment_request_hide_itemization', true ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$amount         = $cart_item['line_subtotal'];
					$subtotal      += $cart_item['line_subtotal'];
					$quantity_label = 1 < $cart_item['quantity'] ? ' (x' . $cart_item['quantity'] . ')' : '';

					$product_name = $cart_item['data']->get_name();

					$item = array(
						'label'  => $product_name . $quantity_label,
						'amount' => $this->get_stripe_amount( $amount ),
					);

					$items[] = $item;
				}
			}

			if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
				$discounts = wc_format_decimal( WC()->cart->get_cart_discount_total(), WC()->cart->dp );
			} else {
				$applied_coupons = array_values( WC()->cart->get_coupon_discount_totals() );

				foreach ( $applied_coupons as $amount ) {
					$discounts += (float) $amount;
				}
			}

			$discounts   = wc_format_decimal( $discounts, WC()->cart->dp );
			$tax         = wc_format_decimal( WC()->cart->tax_total + WC()->cart->shipping_tax_total, WC()->cart->dp );
			$shipping    = wc_format_decimal( WC()->cart->shipping_total, WC()->cart->dp );
			$items_total = wc_format_decimal( WC()->cart->cart_contents_total, WC()->cart->dp ) + $discounts;
			$order_total = version_compare( WC_VERSION, '3.2', '<' ) ? wc_format_decimal( $items_total + $tax + $shipping - $discounts, WC()->cart->dp ) : WC()->cart->get_total( false );

			if ( wc_tax_enabled() ) {
				$items[] = array(
					'label'  => esc_html( __( 'Tax', 'wonkasoft-stripe' ) ),
					'amount' => $this->get_stripe_amount( $tax ),
				);
			}

			if ( WC()->cart->needs_shipping() ) {
				$items[] = array(
					'label'  => esc_html( __( 'Shipping', 'wonkasoft-stripe' ) ),
					'amount' => $this->get_stripe_amount( $shipping ),
				);
			}

			if ( WC()->cart->has_discount() ) {
				$items[] = array(
					'label'  => esc_html( __( 'Discount', 'wonkasoft-stripe' ) ),
					'amount' => $this->get_stripe_amount( $discounts ),
				);
			}

			if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
				$cart_fees = WC()->cart->fees;
			} else {
				$cart_fees = WC()->cart->get_fees();
			}

			// Include fees and taxes as display items.
			foreach ( $cart_fees as $key => $fee ) {
				$items[] = array(
					'label'  => $fee->name,
					'amount' => $this->get_stripe_amount( $fee->amount ),
				);
			}

			return array(
				'displayItems' => $items,
				'total'        => array(
					'label'   => $this->total_label,
					'amount'  => max( 0, apply_filters( 'woocommerce_stripe_calculated_total', $this->get_stripe_amount( $order_total ), $order_total, WC()->cart ) ),
					'pending' => false,
				),
			);
		}

		/**
		 * Get Stripe amount to pay
		 *
		 * @param float  $total Amount due.
		 * @param string $currency Accepted currency.
		 *
		 * @return float|int
		 */
		public static function get_stripe_amount( $total, $currency = '' ) {
			if ( ! $currency ) {
				$currency = get_woocommerce_currency();
			}

			if ( in_array( strtolower( $currency ), self::no_decimal_currencies() ) ) {
				return absint( $total );
			} else {
				return absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) ); // In cents.
			}
		}

		/**
		 * List of currencies supported by Stripe that has no decimals
		 * https://stripe.com/docs/currencies#zero-decimal from https://stripe.com/docs/currencies#presentment-currencies
		 *
		 * @return array $currencies
		 */
		public static function no_decimal_currencies() {
			return array(
				'bif', // Burundian Franc
				'clp', // Chilean Peso
				'djf', // Djiboutian Franc
				'gnf', // Guinean Franc
				'jpy', // Japanese Yen
				'kmf', // Comorian Franc
				'krw', // South Korean Won
				'mga', // Malagasy Ariary
				'pyg', // Paraguayan Guaraní
				'rwf', // Rwandan Franc
				'ugx', // Ugandan Shilling
				'vnd', // Vietnamese Đồng
				'vuv', // Vanuatu Vatu
				'xaf', // Central African Cfa Franc
				'xof', // West African Cfa Franc
				'xpf', // Cfp Franc
			);
		}

		/**
		 * Sanitize statement descriptor text.
		 *
		 * Stripe requires max of 22 characters and no
		 * special characters with ><"'.
		 *
		 * @since 1.0.0
		 * @param string $statement_descriptor
		 * @return string $statement_descriptor Sanitized statement descriptor
		 */
		public static function clean_statement_descriptor( $statement_descriptor = '' ) {
			$disallowed_characters = array( '<', '>', '"', "'" );

			// Remove special characters.
			$statement_descriptor = str_replace( $disallowed_characters, '', $statement_descriptor );

			$statement_descriptor = substr( trim( $statement_descriptor ), 0, 22 );

			return $statement_descriptor;
		}

	}

	new Wonkasoft_Stripe_Payment_Requests();
}
