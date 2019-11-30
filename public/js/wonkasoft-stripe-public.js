/* WS_STRIPE */
( function( $ ) {
	'use strict';

	var stripe = Stripe( WS_STRIPE.stripe.key, {
		'stripeAccount': WS_STRIPE.stripe.account_id,
	} ),
		paymentRequestType;

	/**
	 * Object to handle Stripe payment forms.
	 */
	var wonkasoft_stripe_payment_request = {
		/**
		 * Get WC AJAX endpoint URL.
		 *
		 * @param  {String} endpoint Endpoint.
		 * @return {String}
		 */
		getAjaxURL: function( endpoint ) {
			return WS_STRIPE.ws_ajax
				.toString()
				.replace( '%%endpoint%%', 'wonkasoft_stripe_' + endpoint );
		},
		getCartDetails: function() {
			var data = {
				security: WS_STRIPE.nonces.ws_request
			};

			$.ajax( {
				type:    'POST',
				data:    data,
				url:     wonkasoft_stripe_payment_request.getAjaxURL( 'get_cart_details' ),
				success: function( response ) {
					wonkasoft_stripe_payment_request.startPaymentRequest( response );
				}
			} );
		},
		getAttributes: function() {
			var select = $( '.variations_form' ).find( '.variations select' ),
				data   = {},
				count  = 0,
				chosen = 0;

			select.each( function() {
				var attribute_name = $( this ).data( 'attribute_name' ) || $( this ).attr( 'name' );
				var value          = $( this ).val() || '';

				if ( value.length > 0 ) {
					chosen ++;
				}

				count ++;
				data[ attribute_name ] = value;
			});

			return {
				'count'      : count,
				'chosenCount': chosen,
				'data'       : data
			};			
		},
		processSource: function( source, paymentRequestType ) {
			var data = wonkasoft_stripe_payment_request.getOrderData( source, paymentRequestType );

			return $.ajax( {
				type:    'POST',
				data:    data,
				dataType: 'json',
				url:     wonkasoft_stripe_payment_request.getAjaxURL( 'create_order' )
			} );
		},

		/**
		 * Get order data.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 * @param {PaymentResponse} source Payment Response instance.
		 *
		 * @return {Object}
		 */
		getOrderData: function( evt, paymentRequestType ) {
			var source   = evt.source;
			var email    = source.owner.email;
			var phone    = source.owner.phone;
			var billing  = source.owner.address;
			var name     = source.owner.name;
			var shipping = evt.shippingAddress;
			var data     = {
				_wpnonce:                  WS_STRIPE.nonces.ws_checkout,
				billing_first_name:        null !== name ? name.split( ' ' ).slice( 0, 1 ).join( ' ' ) : '',
				billing_last_name:         null !== name ? name.split( ' ' ).slice( 1 ).join( ' ' ) : '',
				billing_company:           '',
				billing_email:             null !== email   ? email : evt.payerEmail,
				billing_phone:             null !== phone   ? phone : evt.payerPhone.replace( '/[() -]/g', '' ),
				billing_country:           null !== billing ? billing.country : '',
				billing_address_1:         null !== billing ? billing.line1 : '',
				billing_address_2:         null !== billing ? billing.line2 : '',
				billing_city:              null !== billing ? billing.city : '',
				billing_state:             null !== billing ? billing.state : '',
				billing_postcode:          null !== billing ? billing.postal_code : '',
				shipping_first_name:       '',
				shipping_last_name:        '',
				shipping_company:          '',
				shipping_country:          '',
				shipping_address_1:        '',
				shipping_address_2:        '',
				shipping_city:             '',
				shipping_state:            '',
				shipping_postcode:         '',
				shipping_method:           [ null === evt.shippingOption ? null : evt.shippingOption.id ],
				order_comments:            '',
				payment_method:            'wonkasoft_stripe',
				ship_to_different_address: 1,
				terms:                     1,
				stripe_source:             source.id,
				payment_request_type:      paymentRequestType
			};

			if ( shipping ) {
				data.shipping_first_name = shipping.recipient.split( ' ' ).slice( 0, 1 ).join( ' ' );
				data.shipping_last_name  = shipping.recipient.split( ' ' ).slice( 1 ).join( ' ' );
				data.shipping_company    = shipping.organization;
				data.shipping_country    = shipping.country;
				data.shipping_address_1  = typeof shipping.addressLine[0] === 'undefined' ? '' : shipping.addressLine[0];
				data.shipping_address_2  = typeof shipping.addressLine[1] === 'undefined' ? '' : shipping.addressLine[1];
				data.shipping_city       = shipping.city;
				data.shipping_state      = shipping.region;
				data.shipping_postcode   = shipping.postalCode;
			}

			return data;
		},

		/**
		 * Generate error message HTML.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 * @param  {String} message Error message.
		 * @return {Object}
		 */
		getErrorMessageHTML: function( message ) {
			return $( '<div class="woocommerce-error" />' ).text( message );
		},

		/**
		 * Abort payment and display error messages.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 * @param {PaymentResponse} payment Payment response instance.
		 * @param {String}          message Error message to display.
		 */
		abortPayment: function( payment, message ) {
			payment.complete( 'fail' );

			$( '.woocommerce-error' ).remove();

			if ( WS_STRIPE.is_product_page ) {
				var element = $( '.product' );

				element.before( message );

				$( 'html, body' ).animate({
					scrollTop: element.prev( '.woocommerce-error' ).offset().top
				}, 600 );
			} else {
				var $form = $( '.shop_table.cart' ).closest( 'form' );

				$form.before( message );

				$( 'html, body' ).animate({
					scrollTop: $form.prev( '.woocommerce-error' ).offset().top
				}, 600 );
			}
		},

		/**
		 * Complete payment.
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 * @param {PaymentResponse} payment Payment response instance.
		 * @param {String}          url     Order thank you page URL.
		 */
		completePayment: function( payment, url ) {
			wonkasoft_stripe_payment_request.block();

			payment.complete( 'success' );

			// Success, then redirect to the Thank You page.
			window.location = url;
		},

		block: function() {
			$.blockUI( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );
		},

		/**
		 * Update shipping options.
		 *
		 * @param {Object}         details Payment details.
		 * @param {PaymentAddress} address Shipping address.
		 */
		updateShippingOptions: function( details, address ) {
			var data = {
				security:  WS_STRIPE.nonces.ws_shipping,
				country:   address.country,
				state:     address.region,
				postcode:  address.postalCode,
				city:      address.city,
				address:   typeof address.addressLine[0] === 'undefined' ? '' : address.addressLine[0],
				address_2: typeof address.addressLine[1] === 'undefined' ? '' : address.addressLine[1],
				payment_request_type: paymentRequestType
			};

			return $.ajax( {
				type:    'POST',
				data:    data,
				url:     wonkasoft_stripe_payment_request.getAjaxURL( 'get_shipping_options' )
			} );
		},

		/**
		 * Updates the shipping price and the total based on the shipping option.
		 *
		 * @param {Object}   details        The line items and shipping options.
		 * @param {String}   shippingOption User's preferred shipping option to use for shipping price calculations.
		 */
		updateShippingDetails: function( details, shippingOption ) {
			var data = {
				security: WS_STRIPE.nonces.ws_update_shipping,
				shipping_method: [ shippingOption.id ],
				payment_request_type: paymentRequestType
			};

			return $.ajax( {
				type: 'POST',
				data: data,
				url:  wonkasoft_stripe_payment_request.getAjaxURL( 'update_shipping_method' )
			} );
		},

		/**
		 * Starts the payment request
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		startPaymentRequest: function( cart ) {
			var paymentDetails,
				options;

			options = {
				total: cart.order_data.total,
				currency: cart.order_data.currency,
				country: cart.order_data.country_code,
				requestPayerName: true,
				requestPayerEmail: true,
				requestPayerPhone: true,
				requestShipping: cart.shipping_required ? true : false,
				displayItems: cart.order_data.displayItems
			};

			paymentDetails = cart.order_data;

			var paymentRequest = stripe.paymentRequest( options );

			// Check the availability of the Payment Request API first.
            paymentRequest.canMakePayment().then( function( result ) {
              if ( result ) {
              	paymentRequestType = result.applePay ? 'apple_pay' : 'payment_request_api';
                  if ( null !== WS_STRIPE.stripe.btns.gpay ) 
                  {
                    document.querySelector( '#wonkasoft-payment-request-button' ).innerHTML = WS_STRIPE.stripe.btns.gpay;
                    document.querySelector( '#g-pay-btn' ).addEventListener( 'click', function( e ) 
                        {
                            var target = e.target;
                            if ( 'BUTTON' === target.nodeName ) 
                            {
                                paymentRequest.show();
                            }
                        } );
                  }
                  if ( true === result.applePay ) 
                  {
                      document.querySelector( '#wonkasoft-payment-request-button' ).innerHTML = WS_STRIPE.stripe.btns.applepay;
                    document.querySelector( '#apple-pay-btn' ).addEventListener( 'click', function( e ) 
                        {
                            var target = e.target;
                            if ( 'BUTTON' === target.nodeName ) 
                            {
                                paymentRequest.show();
                            }
                        } );
                  }
              } else {
                document.getElementById('wonkasoft-payment-request-button').style.display = 'none';
              }
            });

			// Possible statuses success, fail, invalid_payer_name, invalid_payer_email, invalid_payer_phone, invalid_shipping_address.
			paymentRequest.on( 'shippingaddresschange', function( evt ) {
				$.when( wonkasoft_stripe_payment_request.updateShippingOptions( paymentDetails, evt.shippingAddress ) ).then( function( response ) {
					evt.updateWith( { status: response.result, shippingOptions: response.shipping_options, total: response.total, displayItems: response.displayItems } );
				} );
			} );

			paymentRequest.on( 'shippingoptionchange', function( evt ) {
				$.when( wonkasoft_stripe_payment_request.updateShippingDetails( paymentDetails, evt.shippingOption ) ).then( function( response ) {
					if ( 'success' === response.result ) {
						evt.updateWith( { status: 'success', total: response.total, displayItems: response.displayItems } );
					}

					if ( 'fail' === response.result ) {
						evt.updateWith( { status: 'fail' } );
					}
				} );												
			} );

			paymentRequest.on( 'source', function( evt ) {
				$.when( wonkasoft_stripe_payment_request.processSource( evt, paymentRequestType ) ).then( function( response ) {
					console.log(response);
					if ( 'success' === response.result ) {
						wonkasoft_stripe_payment_request.completePayment( evt, response.redirect );
					} else {
						wonkasoft_stripe_payment_request.abortPayment( evt, response.messages );
					}
				} );
			} );
		},

		/**
		 * Initialize event handlers and UI state
		 *
		 * @since 1.0.0
		 * @version 1.0.0
		 */
		init: function() {
			wonkasoft_stripe_payment_request.getCartDetails();
		}
	};

	wonkasoft_stripe_payment_request.init();

	// We need to refresh payment request data when total is updated.
	$( document.body ).on( 'updated_cart_totals', function() {
		wonkasoft_stripe_payment_request.init();
	} );

	// We need to refresh payment request data when total is updated.
	$( document.body ).on( 'updated_checkout', function() {
		wonkasoft_stripe_payment_request.init();
	} );

})( jQuery );
