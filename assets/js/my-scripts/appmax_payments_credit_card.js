/* global appmax_payments_credit_card_params */
jQuery( function( $ ) {

    var appmax_payments_credit_card = {
        init: function() {

            appmax_payments_credit_card.masks();

            $( document.body )
                .on( 'updated_checkout', function() {
                    appmax_payments_credit_card.masks();
                })
                .trigger( 'updated_checkout' );
        },
        masks: function() {
            $.each(appmax_payments_credit_card_params.masks, function (field, mask) {
                $('.' + field ).mask(mask);
            });
        }
    };

    appmax_payments_credit_card.init();
} );
