<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_WebHook_Events_Contract
 */
interface Appmax_Payments_WebHook_Events_Contract
{
    public function process();
}