jQuery( function( $ ) {

	var appmax_payments_fields_checkout_customer = {

		init: function() {
			$( document.body ).on( 'change', '#billing_country', function() {
				if ( 'BR' === $( this ).val() ) {
					appmax_payments_fields_checkout_customer.maskBilling();
				} else {
					appmax_payments_fields_checkout_customer.unmaskBilling();
				}
			});

			if ( $().select2 ) {
				$( '.wc-ecfb-select' ).select2();
			}
			if ($('#billing_country').val() === 'BR' ) {
				appmax_payments_fields_checkout_customer.maskBilling();
			}
		},

		maskBilling: function() {
			appmax_payments_fields_checkout_customer.maskPhone( '#billing_phone' );
			$( '#billing_postcode' ).mask( '00000-000' );
			$( '#billing_phone, #billing_postcode' ).attr( 'type', 'tel' );
		},

		unmaskBilling: function() {
			$( '#billing_phone, #billing_postcode' ).unmask().attr( 'type', 'text' );
		},

		maskPhone: function(selector) {
			var $element = $(selector),
				MaskBehavior = function(val) {
					return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
				},
				maskOptions = {
					onKeyPress: function(val, e, field, options) {
						field.mask(MaskBehavior.apply({}, arguments), options);
					}
				};

			$element.mask(MaskBehavior, maskOptions);
		},
	};

	appmax_payments_fields_checkout_customer.init();
});
