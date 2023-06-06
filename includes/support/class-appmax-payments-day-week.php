<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Day_Week
 */
class Appmax_Payments_Day_Week
{
    const SUNDAY = 'Sunday';
    const MONDAY = 'Monday';
    const TUESDAY = 'Tuesday';
    const WEDNESDAY = 'Wednesday';
    const THURSDAY = 'Thursday';
    const FRIDAY = 'Friday';
    const SATURDAY = 'Saturday';

    /**
     * @return array
     */
    public static function days_that_need_add_one_day()
    {
        return array(
            self::SUNDAY => self::SUNDAY,
            self::MONDAY => self::MONDAY,
            self::TUESDAY => self::TUESDAY,
            self::WEDNESDAY => self::WEDNESDAY,
            self::THURSDAY => self::THURSDAY,
        );
    }
}