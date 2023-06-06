<fieldset>
    <input id="pix-order-id" type="hidden" value="<?php echo esc_attr($order->get_meta('_appmax_transaction_data')["post_payment"]["order_id"]); ?>">
    <input id="wp_order_id" type="hidden" value="<?php echo esc_attr($order->get_meta('_appmax_transaction_id')); ?>">
    <input id="expiration_date" type="hidden" value="<?php echo esc_attr($order->get_meta('_appmax_transaction_data')["post_payment"]["pix_expiration_date"]); ?>">
    <div id="wrapper">
        <div class="text-header">
            <div>
                <h2><?php esc_html_e( "Seu pedido foi reservado!", 'appmax' ); ?></h2>
                <h2><?php esc_html_e( "Efetue o pagamento dentro de ", 'appmax' ); ?><span id="countdown"></span></h2>
            </div>
        </div>

        <div class="col-md-6">
            <p><?php esc_html_e( "1 - Clique no botão abaixo para copiar o código.", 'appmax' ); ?></p>
            <p><?php esc_html_e( "2 - Abra o aplicativo do seu banco ou instituição financeira e entre na opção Pix.", 'appmax' ); ?></p>
            <p><?php esc_html_e( "3 - Na opção Pix Copia e Cola, insira o código copiado no passo anterior.", 'appmax' ); ?></p>
        </div>

        <div style="display: grid; place-items: center;">
            <div>
                <img
                    src="<?php echo "data:image/png;base64,".esc_attr($order->get_meta('_appmax_transaction_data')['post_payment']['pix_qrcode']); ?>" width="240">
                <p id="demo"></p>
            </div>
            <div class="text-center">
                <input type="text" id="pix_emv" style="display: none;" value="<?php echo esc_attr($order->get_meta("_appmax_transaction_data")["post_payment"]["pix_emv"]); ?>">
                <button id="get-qrcode"><?php esc_html_e( "Copiar código", 'appmax' ); ?></button>
            </div>
        </div>
    </div>
</fieldset>