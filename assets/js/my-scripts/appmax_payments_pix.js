/* global appmax_payments_pix_params */
jQuery( function( $ ) {

    var appmax_payments_pix = {
        init: function() {

            appmax_payments_pix.masks();
            appmax_payments_pix.generateQrCode();

            $( document.body )
                .on( 'updated_checkout', function() {
                    appmax_payments_pix.masks();
                    appmax_payments_pix.generateQrCode();
                })
                .trigger( 'updated_checkout' );
        },
        masks: function() {
            $.each(appmax_payments_pix_params.masks, function (field, mask) {
                $('.' + field ).mask(mask);
            });
        },
        formatDate: function(currentMinutes) {
            let arr = currentMinutes.split(':');
            return arr.map((minute) => {
                if (minute < 10) {
                    minute = "0" + minute;
                }
                return minute;
            }).join(':');
        },
        generateQrCode: function() {
            if (document.getElementById("pix-order-id")) {

                const key = appmax_payments_pix_params.key;
                const cluster = appmax_payments_pix_params.cluster;

                const pusher = new Pusher(key, {
                    cluster: cluster,
                    encrypted: true
                });

                pusher.logToConsole = 0;
                const orderId = document.getElementById("pix-order-id").value;
                const channel = pusher.subscribe(`pix.notification.${orderId}`);
                const wrapper = document.getElementById("wrapper");

                if (window.localStorage[`${orderId}`]) {
                    wrapper.innerHTML = "<h2>"+appmax_payments_pix_params.payment_confirmed+"</h2>";
                }

                const copyTxt = document.getElementById("pix_emv");
                const copyBtn = document.getElementById("get-qrcode");
                const countdown = document.getElementById("countdown");

                $("#get-qrcode").on("click", function (event) {
                    event.preventDefault()

                    copyTxt.select();
                    navigator.clipboard.writeText(copyTxt.value);

                    copyBtn.classList.add('disabled');
                    copyBtn.textContent = appmax_payments_pix_params.copy_code;
                })

                if (! window.localStorage[`${orderId}`]) {

                    const expirationDate = document.getElementById('expiration_date').value;
                    let countDownDate = new Date(expirationDate).getTime();

                    const timer = setInterval(function () {

                        let now = new Date().getTime();
                        let distance = countDownDate - now;
                        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        countdown.innerHTML = appmax_payments_pix.formatDate(`${hours}:${minutes}:${seconds}`);

                        if (distance < 0) {
                            clearInterval(timer);
                            document.getElementById("wrapper").innerHTML = "<h2>"+appmax_payments_pix_params.expired_time+"</h2>";
                        }
                    }, 1000);
                }

                const wp_order_id = document.getElementById('wp_order_id').value;

                channel.bind('pix.order-paid', function (data) {

                    window.localStorage[`${orderId}`] = true;
                    wrapper.innerHTML = "<h2>"+appmax_payments_pix_params.payment_confirmed+"</h2>";

                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: woocommerce_params.ajax_url,
                        data: {
                            'action': 'update_order_status',
                            'order_id': wp_order_id,
                        },
                        success: function (response) {
                            console.log(response);
                        }
                    });

                });
            }
        }
    };

    appmax_payments_pix.init();
} );