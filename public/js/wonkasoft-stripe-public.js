(function( $ ) {
	var wonkasoft_stripe_api_key,
	gpay_btn,
	applepay_btn,
	wonkasoft_stripe_btns_container,
	xhr = new XMLHttpRequest();

	if ( document.querySelector( '#wonkasoft-payment-request-button' ) ) 
	{
		wonkasoft_stripe_btns_container = document.querySelector( '#wonkasoft-payment-request-button' );
		window.addEventListener( 'load',function( e ) 
		{
			var data = {
				'url': WS_AJAX.ws_send,
				'action': 'get_wonkasoft_stripe_api',
				'security': WS_AJAX.nonces.ws_request,
			};
			var query_string = Object.keys( data ).map( function( key ) { return key + '=' + data[key]; } ).join('&');
			xhr.onreadystatechange = function() {

				if ( this.readyState == 4 && this.status == 200 ) 
				{
					var response = JSON.parse( this.responseText );
					var cart = response.data.cart;
					console.log(response);

					var cart_total = 0;
					var line_items = [];
					cart.forEach( function( item, i ) 
						{
							line_items.push( { 'label': item.product_title, 'amount': Number( item.amount.replace( '.', '' ) ), } );
							cart_total += item.amount.replace('.', '') * item.quantity;
						});
					cart_total = Number( cart_total );

					var stripe = Stripe( response.data.api_key, {
						stripeAccount: response.data.account_id,
					} );
					var elements = stripe.elements();
					if ( null !== response.data.gpay_btn ) 
					{
						gpay_btn = response.data.gpay_btn;
					}

					if ( null !== response.data.applepay_btn ) 
					{
						applepay_btn = response.data.applepay_btn;
					}

					console.log( elements );
					var card = elements.create( 'card', {
						classes: {
							base: 'wonka-btn StripeElement',
						},
						style: {
							base: {
							    color: '#303238',
							    fontSize: '16px',
							    color: "#32325d",
							    fontSmoothing: 'antialiased',
							    '::placeholder': {
							      color: '#ccc',
						    	},
						    },
						},
					});

					// var paymentRequest = stripe.paymentRequest({
					//   country: 'US',
					//   currency: 'usd',
					//   requestPayerName: true,
					//   requestPayerEmail: true,
					//   requestPayerPhone: true,
					//   requestShipping: true,
					//   shippingOptions: response.data.shipping_options,
					//   displayItems: line_items,
					//   total: {
					//     label: 'checkout',
					//     amount: cart_total + response.data.shipping_options[0].amount,
					//     pending: true,
					//   },
					// });

					console.log( card );

					document.querySelector( '#wonkasoft-payment-request-button' ).innerHTML += gpay_btn;
					var payment_box = document.querySelector('#payment' );
					card.mount( '#wonkasoft-stripe-payment-field' );
					payment_box.addEventListener( 'wc_fragments_loaded', function( e ) 
					{
						card.mount( '#wonkasoft-stripe-payment-field' );

					});
					// console.log( paymentRequest );
					// paymentRequest.on('shippingoptionchange', function(ev) {
					//   if ( null !== ev.shippingOption ) {
				 //      	var new_total = ( paymentRequest._initialOptions.total.amount + ev.shippingOption.amount );
				 //      	ev.updateWith({
			  //     	        status: 'success',
			  //     	        shippingOptions: response.data.shipping_options,
			  //     	        total: {
					// 		    label: 'checkout',
					// 		    amount: new_total,
					// 		  },
			  //     	      });
					//   }
					// });

					// Check the availability of the Payment Request API first.
					// paymentRequest.canMakePayment().then( function( result ) {
					//   if ( result ) {
					//   	if ( null !== response.data.gpay_btn ) 
					//   	{
					//     	document.querySelector( '#wonkasoft-payment-request-button' ).innerHTML = response.data.gpay_btn;
					//     	document.querySelector( '#g-pay-btn' ).addEventListener( 'click', function( e ) 
					//     		{
					//     			var target = e.target;
					//     			if ( 'BUTTON' === target.nodeName ) 
					//     			{
					//     				paymentRequest.show();
					//     			}
					//     		} );
					//   	}
					//   	if ( true === result.applePay ) 
					//   	{
					//   		document.querySelector( '#wonkasoft-payment-request-button' ).innerHTML = response.data.applepay_btn;
					//     	document.querySelector( '#apple-pay-btn' ).addEventListener( 'click', function( e ) 
					//     		{
					//     			var target = e.target;
					//     			if ( 'BUTTON' === target.nodeName ) 
					//     			{
					//     				paymentRequest.show();
					//     			}
					//     		} );
					//   	}
					//   } else {
					//     document.getElementById('wonkasoft-payment-request-button').style.display = 'none';
					//   }
					// });

					// paymentRequest.on( 'token', function(ev) {
					// 	var data2 = {
					// 		'url': WS_AJAX.ws_endpoint,
					// 		'token': ev.token.id,
					//     	'this_ev': ev,
					//     	};
				 //    	xhr.onreadystatechange = function() {

				 //    		if ( this.readyState == 4 && this.status == 200 ) 
				 //    		{
				 //    			var response = JSON.parse( this.responseText );
					//       		console.log( response );
					//       		console.log( ev );

					//         	ev.complete('success');
					        	
					//         	// ev.complete('fail');
				 //    		}
				 //    	};
					//     xhr.open('GET', data2.url );
					//     xhr.setRequestHeader( "Content-type", "application/json; charset=UTF-8" );
					//     xhr.send( JSON.stringify( data2 ) );
					// });
				}
			};

			xhr.open('GET', data.url + "?" + query_string );
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.send();
		});
	}

})( jQuery );
