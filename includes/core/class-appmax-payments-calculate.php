<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Calculate
{
    /**
     * @param float $total_value
     * @param int $max_installments
     * @param $settings_interest
     * @return array
     * @throws Exception
     */
    public static function calculate_installments( float $total_value, int $max_installments, $settings_interest ): array
    {
        if ( $max_installments < 1 OR $max_installments > 12 ) {
            throw new \Exception('Invalid installments, min 1, max 12');
        }

        $installments = [];

        $interest = $settings_interest->cc_interest > 0 ? $settings_interest->cc_interest / 100 : 0;

        foreach ( range( 1, $max_installments ) as $installment ) {

            if ($installment == 1) {
                $installmentValue = $total_value;
                $installments[$installment] = (float) number_format($installmentValue, 2, ".", "");
                continue;
            }

            $interestInstallment = Appmax_Payments_Calculate::interestFromInstallment( (array) $settings_interest->settings, $installment, $interest );

            if ($interestInstallment > 0) {
                $installmentValue = ($total_value * $interestInstallment / (1 - pow(1 + $interestInstallment, -$installment)) * $installment);
                $installments[$installment] = (float)number_format($installmentValue, 2, ".", "");
                continue;
            }

            $installments[$installment] = (float) number_format( $total_value, 2, ".", "" );
        }

        return $installments;
    }

    /**
     * @param array $interestedSettings
     * @param $installment
     * @param $defaultInterest
     * @return mixed
     */
    public static function interestFromInstallment( array $interestedSettings, $installment, $defaultInterest): mixed
    {
        if (! $interestedSettings || ! isset($interestedSettings[$installment])) {
            return $defaultInterest;
        }

        if ($interestedSettings[$installment] > 0) {
            return $interestedSettings[$installment] / 100;
        }

        return $interestedSettings[$installment];
    }

    /**
     * @throws Exception
     */
    public static function calculate_total_interest($total, $installments = 12, $interest = 0)
    {
        $totalInterest = Appmax_Payments_Calculate::calculate_installments($total, $installments, $interest);
        return $totalInterest[$installments] - $total;
    }
}
