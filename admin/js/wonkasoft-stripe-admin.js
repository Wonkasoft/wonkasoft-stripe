(function( $ ) {
	'use strict';
	var select_mode;

	if ( document.querySelector( 'select[name=woocommerce_wonkasoft_stripe_select_mode]' ) ) 
	{
		window.addEventListener( 'load', function( e ) 
		{
			select_mode = document.querySelector( 'select[name=woocommerce_wonkasoft_stripe_select_mode]' );
			set_options();

			select_mode.onchange = function() 
			{
				set_options();
			};
		} );
	}

	function set_options() 
	{
		var table_rows = document.querySelectorAll( 'table.form-table tr');

		if ( 'live_mode' === select_mode.value )
		{
			table_rows.forEach( function( row, i ) 
				{
					if ( row.getElementsByTagName( 'input' )[0] ) 
					{
						if ( '' !== row.style ) 
						{
							row.style = '';
						}

						if ( row.getElementsByTagName( 'input' )[0].name.includes( 'test' ) ) 
						{
							row.setAttribute( 'style', 'display: none;' );
						}
					}
				});
		}

		if ( 'sandbox_mode' === select_mode.value ) 
		{
			table_rows.forEach( function( row, i ) 
				{
					if ( row.getElementsByTagName( 'input' )[0] ) 
					{
						if ( '' !== row.style ) 
						{
							row.style = '';
						}
						
						if ( row.getElementsByTagName( 'input' )[0].name.includes( 'live' ) ) 
						{
							row.setAttribute( 'style', 'display: none;' );
						}
					}
				});
		}
	}

})( jQuery );


