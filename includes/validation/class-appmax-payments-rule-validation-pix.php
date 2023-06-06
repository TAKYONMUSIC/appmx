<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Rule_Validation_Pix
 */
class Appmax_Payments_Rule_Validation_Pix extends Appmax_Payments_Validator
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
            'cpf_pix'
        );
    }

    protected function rules(): array
    {
        return [
            'cpf_pix' => 'required|cpf'
        ];
    }

    protected function messages(): array
    {
        return [
            'cpf_pix.required' => 'Necessário preencher o CPF!',
            'cpf_pix.cpf' => 'CPF inválido!'
        ];
    }
}