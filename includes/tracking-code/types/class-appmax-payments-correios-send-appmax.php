<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Correios_Send_Appmax
 *
 * @extends Appmax_Payments_Tracking_Code
 */
class Appmax_Payments_Correios_Send_Appmax extends Appmax_Payments_Tracking_Code implements Appmax_Payments_Tracking_Code_Contract
{
    /**
     * @param string $api_key
     */
    public function __construct( string $api_key )
    {
        parent::__construct( $api_key );
    }

    /**
     * @throws Exception
     */
    public function send_tracking_code()
    {
        if ( ! $this->validate_correios_tracking_code( $_POST ) ) {
            return;
        }

        if ( ! is_int($_POST['order_id']) || $_POST['order_id'] < 1) {
            return;
        }

        $order = wc_get_order( $_POST['order_id'] );

        $external_order_id = $order->get_meta( 'appmax_order_id' );

        if ( empty( $external_order_id ) ) {
            return;
        }

        update_post_meta( $order->get_order_number(), 'appmax_tracking_code', Appmax_Payments_Helper::clear_input( $_POST['tracking_code'] ) );

        $response = $this->api->request(
            'POST',
            Appmax_Payments_Endpoints_Api::ENDPOINT_TRACKING_CODE,
            (new Appmax_Payments_Post_Information( $order ))->make_body_tracking_code(
                $external_order_id, Appmax_Payments_Helper::clear_input( $_POST['tracking_code'] )
            )
        );

        $response_tracking_body = $this->check_response->check_response_tracking_code( $response );

        $log_content = sprintf( "* Endpoint Response Tracking Code: %s", Appmax_Payments_Helper::encode_object( $response_tracking_body->data ) ) . PHP_EOL;
        $log_content .= PHP_EOL;
        $this->create_log( $log_content );

    }
}