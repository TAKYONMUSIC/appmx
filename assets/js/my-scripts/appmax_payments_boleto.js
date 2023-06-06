/* global appmax_payments_boleto_params */
jQuery( function( $ ) {

    var appmax_payments_boleto = {
        init: function() {

            appmax_payments_boleto.masks();

            $( document.body )
                .on( 'updated_checkout', function() {
                    appmax_payments_boleto.masks();
                })
                .trigger( 'updated_checkout' );
        },
        masks: function() {
            $.each(appmax_payments_boleto_params.masks, function (field, mask) {
                $('.' + field ).mask(mask);
            });
        }
    };

    appmax_payments_boleto.init();
} );