<?php

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

?>

<fieldset>
    <p>
        <?php esc_html_e( "1. Boleto (somente à vista).", 'appmax' ); ?><br/>
        <?php esc_html_e( "2. Pagamentos com Boleto Bancário levam até 3 dias úteis para serem compensados e então terem os produtos liberados.", 'appmax' ); ?><br/>
        <?php esc_html_e( "3. O Boleto será gerado por meio da plataforma APPMAX.", 'appmax' ); ?><br/>
        <?php esc_html_e( "4. Depois do pagamento, fique atento ao seu e-mail para acompanhar o envio do seu pedido (verifique também a caixa de SPAM).", 'appmax' ); ?>
    </p>

    <div class="clear"></div>

    <p class="form-row form-row-wide">
        <label for="cpf_billet">
            <?php esc_html_e(  'CPF (Para emissão da Nota Fiscal)', 'appmax' ); ?>
            <span class="required">*</span>
        </label>
        <input id="cpf_billet" name="cpf_billet"
               class="input-text appmax-boleto-form-card-cpf"
               type="tel" autocomplete="off" maxlength="14"
               placeholder="<?php esc_html_e( 'CPF (Para emissão da Nota Fiscal)', 'appmax' ); ?>"
               style="font-size: 1.0em; padding: 8px;" />
    </p>

    <div class="clear"></div>

</fieldset>