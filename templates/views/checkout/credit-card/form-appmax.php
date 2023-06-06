<?php

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

?>

<fieldset>
    <p class="form-row">
        <label for="card_number">
            <?php esc_html_e(  'Número do Cartão', 'appmax' ); ?>
            <span class="required">*</span>
        </label>
        <input id="card_number" name="card_number"
               class="input-text wc-credit-card-form-card-number"
               type="text" maxlength="20" autocomplete="off"
               placeholder="<?php _e( "&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;", 'appmax' ); ?>"
               style="font-size: 1.0em; padding: 8px;" />
    </p>

    <div class="clear"></div>

    <p class="form-row form-row-first">
        <label for="card_cpf">
            <?php esc_html_e(  'CPF do Titular', 'appmax' ); ?>
            <span class="required">*</span>
        </label>
        <input id="card_cpf" name="card_cpf"
               class="input-text appmax-credit-card-form-card-cpf"
               type="tel" autocomplete="off" maxlength="14"
               placeholder="<?php esc_html_e( 'CPF do Titular', 'appmax' ); ?>"
               style="font-size: 1.0em; padding: 8px;" />
    </p>

    <p class="form-row form-row-last">
        <label for="card_name">
            <?php esc_html_e(  'Nome do Titular', 'appmax' ); ?>
            <span class="required">*</span>
        </label>
        <input id="card_name" name="card_name"
               class="input-text"
               type="text" autocomplete="off"
               placeholder="<?php esc_html_e( 'Nome do Titular', 'appmax' ); ?>"
               style="font-size: 1.0em; padding: 8px;" />
    </p>

    <div class="clear"></div>

    <p class="form-row form-row-first">
        <label for="card_expiry">
            <?php esc_html_e(  'Card Expiry', 'appmax' ); ?>
            <span class="required">*</span>
        </label>
        <input id="card_expiry" name="card_expiry"
               class="input-text appmax-credit-card-form-card-expiry"
               type="tel" autocomplete="off" maxlength="4"
               placeholder="MM/YYYY"
               style="font-size: 1.0em; padding: 8px;">
    </p>

    <p class="form-row form-row-last">
        <label for="card_security_code">
            <?php esc_html_e(  'Cód de Segurança', 'appmax' ); ?>
            <span class="required">*</span>
        </label>
        <input id="card_security_code" name="card_security_code"
               class="input-text"
               type="tel" autocomplete="off" maxlength="4"
               placeholder="<?php _e("&bull;&bull;&bull;&bull;", 'appmax'); ?>"
               style="font-size: 1.0em; padding: 8px;">
    </p>

    <div class="clear"></div>

    <p class="form-row form-row-wide">
        <label for="installments">
            <?php esc_html_e(  'Parcelamento', 'appmax' ); ?>
            <span class="required">*</span>
        </label>
        <select id="installments" name="installments" style="font-size: 1.0em; padding: 8px; width: 100%;">
            <?php _e( $installments, 'appmax' ); ?>
        </select>
    </p>

    <div class="clear"></div>

</fieldset>