<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Card_Expiry
 *
 * @extends Appmax_Payments_Rule
 */
class Appmax_Payments_Card_Expiry extends Appmax_Payments_Rule implements Appmax_Payments_Rule_Contract
{
    /**
     * @param $value
     */
    public function __construct($value)
    {
        parent::__construct($value);
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        list( $month, $year ) = explode( "/", $this->get_value() );

        if ( ! Appmax_Payments_Helper::is_digit( $month ) ) {
            return false;
        }

        if ( $month < '01' || $month > '12') {
            return false;
        }

        if ( ! Appmax_Payments_Helper::is_digit( $year ) ) {
            return false;
        }

        if ( $year < date('Y') || $year > date( 'Y', strtotime( date('Y') . '+ 10 year' ) ) ) {
            return false;
        }

        return true;
    }
}
