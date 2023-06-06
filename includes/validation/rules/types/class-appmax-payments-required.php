<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_required
 *
 * @extends Appmax_Payments_Rule
 */
class Appmax_Payments_Required extends Appmax_Payments_Rule implements Appmax_Payments_Rule_Contract
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
        return ! empty($this->get_value());
    }
}
