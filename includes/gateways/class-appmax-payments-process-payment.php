<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Process_Payment
 */
abstract class Appmax_Payments_Process_Payment
{
    /**
     * @var WC_Payment_Gateway
     */
    protected WC_Payment_Gateway $gateway;

    /**
     * @var Appmax_Payments_Api
     */
    protected Appmax_Payments_Api $appmax;

    /**
     * @param WC_Payment_Gateway $gateway Gateway instance.
     */
    public function __construct( WC_Payment_Gateway $gateway )
    {
        $this->gateway = $gateway;
        $this->appmax = new Appmax_Payments_Api( $gateway->get_option('api_key') );
    }

    /**
     * @param $order_id
     * @param $payment_data
     */
    public function save_order_meta_fields( $order_id, $payment_data )
    {
        $meta_data = array(
            '_appmax_transaction_data' => $payment_data,
            '_appmax_transaction_id'   => intval( $order_id )
        );

        if ( $this->gateway->enable_debug() ) {
            $log_content = sprintf( "Appmax - %s", Appmax_Payments_Helper::date_time_formatted( date( 'Y-m-d H:i:s' ) ) ) . PHP_EOL;
            $log_content .= sprintf( "* Meta Datas inseridas: %s", Appmax_Payments_Helper::encode_object( $meta_data ) ) . PHP_EOL;
            $log_content .= PHP_EOL;
            $log_content .= "============================================================";
            $this->gateway->add_log( $log_content );
        }

        $order = wc_get_order( $order_id );

        if ( ! method_exists( $order, 'update_meta_data' ) ) {
            foreach ( $meta_data as $key => $value ) {
                update_post_meta( $order_id, $key, $value );
            }
        } else {
            foreach ( $meta_data as $key => $value ) {
                $order->update_meta_data( $key, $value );
            }

            $order->save();
        }
    }
}