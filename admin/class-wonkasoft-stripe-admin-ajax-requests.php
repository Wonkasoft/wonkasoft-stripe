<?php
/**
 * This file contains the class that handles ajax requests.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wonkasoft_Stripe_Admin_Ajax_Requests' ) ) {
	/**
	 * The Class for Wonkasoft Stripe Ajax Requests.
	 */
	class Wonkasoft_Stripe_Admin_Ajax_Requests {


		/**
		 * This inits the ajax requests.
		 */
		public function __construct() {

			add_action( 'wp_ajax_get_wonkasoft_stripe_api', array( $this, 'get_wonkasoft_stripe_api' ), 10 );
			add_action( 'wp_ajax_nopriv_get_wonkasoft_stripe_api', array( $this, 'get_wonkasoft_stripe_api' ), 10 );

		}

		/**
		 * This request is for the api key.
		 */
		public function get_wonkasoft_stripe_api() {

			$nonce = ( isset( $_REQUEST['security'] ) ) ? wp_kses_post( wp_unslash( $_REQUEST['security'] ) ) : null;
			// Check if nonce is valid.
			if ( ! wp_verify_nonce( $nonce, 'ws-request-nonce' ) ) {
				die( esc_html__( 'nonce failed', 'aperabags' ) );
			}

			global $woocommerce;
			$cart  = array();
			$items = WC()->cart->get_cart();
			foreach ( $items as $item => $values ) {

				$cart[] = array(
					'_product'      => $values['data']->post,
					'product_title' => $values['data']->post->post_title,
					'description'   => $item->post_content,
					'quantity'      => $values['quantity'],
					'amount'        => get_post_meta( $values['product_id'], '_price', true ),
				);
			}
			$output      = array();
			$get_buttons = Wonkasoft_Stripe_Public::add_wonkasoft_stripe_buttons();

			try {
				$wonka_stripe      = new WC_Gateway_Wonkasoft_Stripe_Gateway();
				$wonka_stripe_mode = $wonka_stripe->get_option( 'select_mode' );
				if ( 'live_mode' === $wonka_stripe_mode ) {
					$output['api_key']      = $wonka_stripe->get_option( 'live_publishable_key' );
					$output['account_id']   = 'acct_1EGeL2GUa6yKV42u';
					$output['woocommerce']  = $cart;
					$output['gpay_btn']     = $get_buttons['gpay'];
					$output['applepay_btn'] = $get_buttons['applepay'];
				}

				if ( 'sandbox_mode' === $wonka_stripe_mode ) {
					$output['api_key']      = $wonka_stripe->get_option( 'test_publishable_key' );
					$output['account_id']   = 'acct_1EGeL2GUa6yKV42u';
					$output['woocommerce']  = $cart;
					$output['gpay_btn']     = $get_buttons['gpay'];
					$output['applepay_btn'] = $get_buttons['applepay'];
				}

				wp_send_json_success( $output );

			} catch ( Exception $e ) {

				wp_send_json_error( $e );
			}
		}
	}

	new Wonkasoft_Stripe_Admin_Ajax_Requests();
}
