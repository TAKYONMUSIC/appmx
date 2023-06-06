<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Due_Date_Generator
 */
class Appmax_Payments_Due_Date_Generator
{
    /**
     * @var int
     */
    private int $due_days;

    /**
     * @var string
     */
    private string $due_date;

    /**
     * @param int|null $due_days
     */
    public function __construct(int $due_days = null)
    {
        $this->due_days = $due_days ?: APPMAX_PAYMENTS_DUE_DAYS;
    }

    /**
     * @return int
     */
    public function get_due_days(): int
    {
        return $this->due_days;
    }

    /**
     * @return string
     */
    public function get_due_date(): string
    {
        return $this->due_date;
    }

    /**
     * @return $this
     */
    public function generate(): static
    {
        $due_date = date( "Y-m-d", strtotime( date( "Y-m-d" ) . sprintf( "+ %d days", $this->get_due_days() ) ) );

        $this->due_date = $this->adjust_business_days_from_due_date( $due_date );

        return $this;
    }

    /**
     * @param $due_date
     * @return string
     */
    private function adjust_business_days_from_due_date( $due_date ): string
    {
        $holidays = $this->get_holidays();

        $dates = array_column( $holidays, 'date' );

        if ( ! in_array( $due_date, $dates ) ) {
            return $this->verify_days_that_not_holidays( $due_date );
        }

        $holiday = $holidays[ array_search( $due_date, $dates ) ];
        $add_days = "{$due_date} + 3 days";

        if ( in_array( $holiday['day_week'], Appmax_Payments_Day_Week::days_that_need_add_one_day() ) ) {
            $add_days = "{$due_date} + 1 days";
        }

        if ( $holiday['day_week'] == Appmax_Payments_Day_Week::SATURDAY ) {
            $add_days = "{$due_date} + 2 days";
        }

        return date( 'Y-m-d', strtotime( $add_days ) );
    }

    /**
     * @param $due_date
     * @return string
     */
    private function verify_days_that_not_holidays( $due_date ): string
    {
        $add_days = null;
        $day_week = Appmax_Payments_Helper::get_day_week_textual( $due_date );

        if ( $day_week == Appmax_Payments_Day_Week::SATURDAY ) {
            $add_days = "{$due_date} + 2 days";
        }

        if ( $day_week == Appmax_Payments_Day_Week::SUNDAY ) {
            $add_days = "{$due_date} + 1 days";
        }

        if ( $add_days ) {
            return date( 'Y-m-d', strtotime( $add_days ) );
        }

        return $due_date;
    }

    /**
     * @return array[]
     */
    private function get_holidays(): array
    {
        return [
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 1, 1 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 1, 1 ) ),
            ],
            [
                'date' => date( 'Y-m-d', $this->get_date_easter() - ( 2 * 60 * 60 * 24 ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( date( 'Y-m-d', $this->get_date_easter() - ( 2 * 60 * 60 * 24 ) ) ),
            ],
            [
                'date' => date( 'Y-m-d', $this->get_date_easter() ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( date( 'Y-m-d', $this->get_date_easter() ) ),
            ],
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 4, 21 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 4, 21 ) ),
            ],
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 5, 1 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 5, 1 ) ),
            ],
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 9, 7 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 9, 7 ) ),
            ],
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 10, 12 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 10, 12 ) ),
            ],
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 11, 2 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 11, 2 ) ),
            ],
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 11, 15 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 11, 15 ) ),
            ],
            [
                'date' => date( 'Y-m-d', strtotime( sprintf( '%d-%d-%d', date('Y'), 12, 25 ) ) ),
                'day_week' => Appmax_Payments_Helper::get_day_week_textual( sprintf( '%d-%d-%d', date('Y'), 12, 25 ) ),
            ],
        ];
    }

    /**
     * @return int
     */
    private function get_date_easter(): int
    {
        $year = date('Y');
        $days = easter_days( $year );

        return (new DateTime("{$year}-03-21"))
            ->add(new DateInterval("P{$days}D"))
            ->getTimestamp();
    }
}