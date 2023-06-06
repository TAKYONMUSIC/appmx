<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Origin_Order
{
    const API = 'API';
    const SITE = 'Site';
    const RECUPERATION = 'Recuperação';
    const TEAM_PRODUCER = 'Equipe Parceiro';
    const CALL_CENTER = 'Call Center';
    const NONE = null;

    public static function callCenter()
    {
        return [
            self::RECUPERATION  => self::RECUPERATION,
            self::TEAM_PRODUCER => self::TEAM_PRODUCER,
            self::CALL_CENTER   => self::CALL_CENTER
        ];
    }
}