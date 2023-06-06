<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Rule_Validation_Boleto
 */
class Appmax_Payments_Rule_Validation_Boleto extends Appmax_Payments_Validator
{
    /**
     * @param array $post
     */
    public function __construct(array $post)
    {
        parent::__construct( $post );
    }

    public static function fields(): array
    {
        return array(
            'cpf_billet'
        );
    }

    protected function rules(): array
    {
        return [
            'cpf_billet' => 'required|cpf'
        ];
    }

    protected function messages(): array
    {
        return [
            'cpf_billet.required' => 'Necessário preencher o CPF!',
            'cpf_billet.cpf' => 'CPF inválido!'
        ];
    }
}