(function( $ ) {
	var wonkasoft_stripe_api_key,
	wonkasoft_stripe_btns,
	xhr = new XMLHttpRequest();

	window.onload = function( e ) 
	{
			console.log( 'working' );
		if ( document.querySelector( '.express-btns-text-wrap' ) ) 
		{
			var data = {
				'url': WS_AJAX.send,
				'action': 'get_wonkasoft_stripe_api',
				'security': WS_AJAX.security
			};
			var query_string = Object.keys( data ).map( function( key ) { return key + '=' + data[key]; } ).join('&');
			xhr.onreadystatechange = function() {

				if ( this.readyState == 4 && this.status == 200 ) 
				{
					var response = JSON.parse( this.responseText );
					console.log(response);
				}
			};

			xhr.open('GET', data.url + "?" + query_string );
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.send();
		}
	};

})( jQuery );
