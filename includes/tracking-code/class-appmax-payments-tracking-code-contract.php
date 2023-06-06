<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Tracking_Code_Contract
 */
interface Appmax_Payments_Tracking_Code_Contract
{
    public function send_tracking_code();
}
