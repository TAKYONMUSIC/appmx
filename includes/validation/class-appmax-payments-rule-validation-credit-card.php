<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Rule_Validation_Credit_Card
 */
class Appmax_Payments_Rule_Validation_Credit_Card extends Appmax_Payments_Validator
{
    /**
     * @param array $post
     */
    public function __construct(array $post)
    {
        parent::__construct( $post );
    }

    /**
     * @return string[]
     */
    public static function fields(): array
    {
        return array(
            'card_number',
            'card_name',
            'card_cpf',
            'card_expiry',
            'card_security_code'
        );
    }

    protected function rules(): array
    {
        return [
            'card_number' => 'required|card-number',
            'card_name' => 'required',
            'card_cpf' => 'required|cpf',
            'card_expiry' => 'required|card-expiry',
            'card_security_code' => 'required|number|cvv',
        ];
    }

    protected function messages(): array
    {
        return [
            'card_number.required' => 'Necessário preencher o número do cartão!',
            'card_number.card-number' => 'Número do Cartão inválido!',
            'card_name.required' => 'Necessário preencher o nome do titular do cartão!',
            'card_cpf.required' => 'Necessário preencher o CPF do titular do cartão!',
            'card_cpf.cpf' => 'CPF do titular do cartão é inválido!',
            'card_expiry.required' => 'Necessário preencher a data de expiração do cartão!',
            'card_expiry.card-expiry' => 'Data de expiração do cartão inválida!',
            'card_security_code.required' => 'Necessário preencher o código de segurança do cartão!',
            'card_security_code.number' => 'Deve ser informado somente números!',
            'card_security_code.cvv' => 'O código de segurança deve ser informado com no mínimo 3 dígitos e no máximo 4 dígitos!',
        ];
    }
}