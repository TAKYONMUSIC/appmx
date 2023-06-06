<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Status_Appmax
{
    const AUTHORIZED = 'autorizado';
    const APPROVED = 'aprovado';
    const INTEGRATED = 'integrado';

    public static function approved()
    {
        return [
            self::APPROVED      => self::APPROVED,
            self::AUTHORIZED    => self::AUTHORIZED,
        ];
    }
}