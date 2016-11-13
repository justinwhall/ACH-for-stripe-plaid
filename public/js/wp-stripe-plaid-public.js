(function( $ ) {
	'use strict';

	var opt = {};
 	
 	// Plaid Link
	var  linkHandler = Plaid.create({
		  env: 'tartan',
		  clientName: 'Stripe / Plaid Test',
		  key: $('#linkButton').data( 'publickey' ),
		  product: 'auth',
		  selectAccount: true,
		  onSuccess: function(public_token, metadata) {
		    $('#linkButton').hide();
		    $('#sp-pay').show();
		    opt.public_token = public_token;
		    opt.account_id = metadata.account_id;
		  },
	});

	// Trigger the Link UI
	document.getElementById( 'linkButton' ).onclick = function() {
	  linkHandler.open();
	};

	// Get token from plaid
	$('#sp-pay').on('click', callPlaid );

	function callPlaid() {

		$('.sp-spinner').css('opacity', 1);
		$('#pay').off('click');

		// format amount
		var amount = $( '#sp-amount' ).val() * 1;
			amount = amount.toFixed( 2 );
			amount = String( amount.replace( '.', '' ) );

		var data = {
			action       : 'call_plaid',
			public_token : opt.public_token,
			account_id   : opt.account_id,
			nonce        : ajax_object.ajax_nonce,
			description  : $('#sp-desc').val(),
			amount       : amount
		};

		$.ajax({
			url     : ajax_object.ajax_url,
			type    : 'POST',
			data    : data,
			success : function( data ){
				$('.sp-spinner').css('opacity', 0);
				if ( data.error ) {
					$('#sp-pay').on('click', callPlaid );
					$('#sp-response').text( data.error.message );
					$('#sp-response').addClass('error');
					$('#sp-response').removeClass('success');
				} else {
					$('#sc-form').fadeTo('fast', 0);
					$('#sp-response').text('Success. Thank you for your payment.');
					$('#sp-response').removeClass('error');
					$('#sp-response').addClass('success');
				}
			}
		});
	}
	
})( jQuery );
