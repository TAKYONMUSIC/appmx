<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Payment_Type
{
    const BILLET = 'Billet';
    const CREDIT_CARD = 'CreditCard';
    const PIX = 'Pix';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return array(
            self::BILLET => self::BILLET,
            self::CREDIT_CARD => self::CREDIT_CARD,
            self::PIX => self::PIX,
        );
    }
}