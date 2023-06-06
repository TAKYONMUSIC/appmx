<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Form_Payment
 */
class Appmax_Payments_Form_Payment
{
    /**
     * @param $settings
     * @return string
     * @throws Exception
     */
    public static function display_installments( $settings ): string
    {
        $calculateInstallments = Appmax_Payments_Calculate::calculate_installments(
            Appmax_Payments_Helper::get_total_cart(),
            $settings['installments'],
            $settings['settings_interest']
        );

        $installments = "";

        foreach ($calculateInstallments as $key => $installment) {

            if (($installment / $key) < 5.00) {
                break;
            }

            $installments .= self::make_installments($key, $installment, $settings['show_total_installments']);
        }

        return $installments;
    }

    /**
     * @param $key
     * @param $installment
     * @param $showTotalInstallments
     * @return string
     */
    public static function make_installments($key, $installment, $showTotalInstallments): string
    {
        $installmentAmount = $installment / $key;
        $installmentAmountFormatted = Appmax_Payments_Helper::monetary_format( $installmentAmount );

        $totalAmountInstallment = $installmentAmount * $key;
        $totalAmountInstallmentFormatted = Appmax_Payments_Helper::monetary_format( $totalAmountInstallment );

        if (true == $showTotalInstallments && $key != 1) {
            return vsprintf(
                "<option value='%s'> %s x %s (%s com juros) </option>",
                [$key, $key, $installmentAmountFormatted, $totalAmountInstallmentFormatted]
            );
        }

        return vsprintf(
            "<option value='%s'> %s x %s </option>",
            [$key, $key, $installmentAmountFormatted]
        );
    }
}
