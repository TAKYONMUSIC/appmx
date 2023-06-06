<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Rule
 */
abstract class Appmax_Payments_Rule
{
    /**
     * @var string $value
     */
    private string $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function get_value(): string
    {
        return $this->value;
    }
}
