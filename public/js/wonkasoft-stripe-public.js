(function( $ ) {
	var wonkasoft_stripe_api_key,
	wonkasoft_stripe_btns,
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
				'security': WS_AJAX.security
			};
			var query_string = Object.keys( data ).map( function( key ) { return key + '=' + data[key]; } ).join('&');
			xhr.onreadystatechange = function() {

				if ( this.readyState == 4 && this.status == 200 ) 
				{
					var response = JSON.parse( this.responseText );
					var cart = response.data.cart;
					console.log(response);
					console.log(cart);
					console.log(response.data.woocommerce);

					var cart_total = 0;
					cart.forEach( function( item, i ) 
						{
							cart_total += item.amount.replace('.', '') * item.quantity;
						});
					cart_total = Number( cart_total );

					var stripe = Stripe( response.data.api_key, {
						stripeAccount: response.data.account_id,
					} );
					var elements = stripe.elements();
					var paymentRequest = stripe.paymentRequest({
					  country: 'US',
					  currency: 'usd',
					  total: {
					    label: 'checkout',
					    amount: cart_total
					  },
					  requestPayerName: true,
					  requestPayerEmail: true,
					});

					console.log( elements );
					console.log( paymentRequest );

					// Check the availability of the Payment Request API first.
					paymentRequest.canMakePayment().then( function( result ) {
						console.log( result );
					  if ( result ) {
					  	if ( null !== response.data.gpay_btn ) 
					  	{
					    	document.querySelector( '#wonkasoft-payment-request-button' ).innerHTML = response.data.gpay_btn;
					    	document.querySelector( '#g-pay-btn' ).addEventListener( 'click', function( e ) 
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

					paymentRequest.on( 'token', function(ev) {

					  // Send the token to your server to charge it!
					    fetch('../api/wc/v2/wonkasoft-stripe-payment/', {
					      method: 'POST',
					      body: JSON.stringify({token: ev.token.id}),
					      headers: {'content-type': 'application/json'},
					    })
					    .then(function(response) {
					      	console.log( response );
					      if (response.ok) {
					      	console.log( response );
					        // Report to the browser that the payment was successful, prompting
					        // it to close the browser payment interface.
					        ev.complete('success');
					      } else {
					      	console.log( response );
					        // Report to the browser that the payment failed, prompting it to
					        // re-show the payment interface, or show an error message and close
					        // the payment interface.
					        ev.complete('fail');
					      }
					    });
					});
					
					paymentRequest.on('shippingaddresschange', function(ev) {
					  if (ev.shippingAddress.country !== 'US') {
					    ev.updateWith({status: 'invalid_shipping_address'});
					  } else {
					    // Perform server-side request to fetch shipping options
					    fetch('/calculateShipping', {
					      data: JSON.stringify({
					        shippingAddress: ev.shippingAddress
					      })
					    }).then(function(response) {
					      return response.json();
					    }).then(function(result) {
					      ev.updateWith({
					        status: 'success',
					        shippingOptions: result.supportedShippingOptions,
					      });
					    });
					  }
					});
				}
			};

			xhr.open('GET', data.url + "?" + query_string );
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.send();
		});
	}

})( jQuery );
