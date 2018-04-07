( function ( $ ) {
	'use strict';

	var opt = {};

	// Plaid Link
	var linkHandler = Plaid.create( {
		env: $( '#sc-form' ).data( 'env' ),
		// env: 'sandbox',
		apiVersion: 'v2',
		clientName: 'Stripe / Plaid Test',
		key: $( '#linkButton' ).data( 'publickey' ),
		product: 'auth',
		selectAccount: true,
		onSuccess: function ( public_token, metadata ) {
			$( '#linkButton' ).hide();
			$( '#sp-pay' ).show();
			opt.public_token = public_token;
			opt.account_id = metadata.account_id;
			// console.log(opt);
			// console.log(metadata);
		},
	} );

	// Trigger the Link UI
	document.getElementById( 'linkButton' ).onclick = function () {
		linkHandler.open();
	};

	// Get token from plaid
	$( '#sp-pay' ).on( 'click', callPlaid );

	function callPlaid() {

		// format amount
		var amountInt = $( '#sp-amount' ).val() * 1;
		var amountFloat = amountInt.toFixed( 2 );
		var amount = String( amountFloat.replace( '.', '' ) );

		$( '#sp-response' ).hide();

		if ( amountInt >= .50 ) {
			$( '.sp-spinner' ).css( 'opacity', 1 );
			$( '#pay' ).off( 'click' );

			var data = {
				action: 'call_plaid',
				public_token: opt.public_token,
				account_id: opt.account_id,
				nonce: ajax_object.ajax_nonce,
				description: $( '#sp-desc' ).val(),
				email: $( '#lb-ach-email' ).val(),
				amount: amount
			};

			$.ajax( {
				url: ajax_object.ajax_url,
				type: 'POST',
				data: data,
				success: function ( data ) {
					// console.log(data);
					$( '.sp-spinner' ).css( 'opacity', 0 );
					if ( data.error ) {
						// console.log(data);
						addError( 'There was an error processing your payment.' );
					} else {
						$( '#sc-form' ).fadeTo( 'fast', 0 );
						$( '#sp-response' ).show();
						$( '#sp-response' ).text( 'Success. Thank you for your payment.' );
						$( '#sp-response' ).removeClass( 'error' );
						$( '#sp-response' ).addClass( 'success' );
					}
				}
			} );

		} else {
			addError( 'Amount must be at least 50 cents' );
		}
	}

	function addError( message ) {
		$( '#sp-pay' ).on( 'click', callPlaid );
		$( '#sp-response' ).show();
		$( '#sp-response' ).text( message );
		$( '#sp-response' ).addClass( 'error' );
		$( '#sp-response' ).removeClass( 'success' );
	}


} )( jQuery );
