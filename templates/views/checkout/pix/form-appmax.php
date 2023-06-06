<?php

if ( ! defined( "ABSPATH" ) )
{
    exit;
}

?>

<fieldset>
    <p>
        <?php esc_html_e( "1. Clique no botão abaixo para gerar o Qr-Code", 'appmax' ); ?><br/>
        <?php esc_html_e( "2. Abra o aplicativo do seu banco ou instituição financeira e entre na opção Pix", 'appmax' ); ?><br/>
        <?php esc_html_e( "3. Na opção Pix Copia e Cola, insira o código copiado no passo anterior", 'appmax' ); ?><br/>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <br>
        <label for="cpf_pix">
            <?php esc_html_e('CPF', 'appmax'); ?>
            <span class="required">*</span>
        </label>
        <input id="cpf_pix" name="cpf_pix"
               class="input-text appmax-pix-form-card-cpf"
               type="tel" autocomplete="off" maxlength="14"
               placeholder="<?php esc_html_e('CPF', 'appmax'); ?>"
               style="font-size: 1.0em; padding: 8px;">
        <br><br>
    </p>

    <div class="clear"></div>

</fieldset>