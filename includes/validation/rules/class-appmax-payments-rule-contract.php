<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Rule_Contract
 */
interface Appmax_Payments_Rule_Contract
{
    public function validate();
}
