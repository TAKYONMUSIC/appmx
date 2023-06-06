<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Send_Appmax
 *
 * @extends Appmax_Payments_Tracking_Code
 */
class Appmax_Payments_Send_Appmax extends Appmax_Payments_Tracking_Code implements Appmax_Payments_Tracking_Code_Contract
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
        if ( ! isset( $_POST['post_id'] ) && ! isset( $_POST['meta'] ) ) {
            return;
        }

        $meta = array_map('sanitize_text_field', $_POST['meta']);
        $tracking_code = $this->get_tracking_code( $meta );

        if ( empty( $tracking_code ) ) {
            return;
        }

        if ( ! is_int($_POST['post_id']) || $_POST['post_id'] < 1) {
            return;
        }

        $order = wc_get_order( $_POST['post_id'] );

        $external_order_id = $order->get_meta('appmax_order_id');

        if ( empty( $external_order_id ) ) {
            return;
        }

        $response = $this->api->request(
            'POST',
            Appmax_Payments_Endpoints_Api::ENDPOINT_TRACKING_CODE,
            (new Appmax_Payments_Post_Information( $order ))->make_body_tracking_code(
                $external_order_id, $tracking_code
            )
        );

        $response_tracking_body = $this->check_response->check_response_tracking_code( $response );

        $log_content = sprintf( "* Endpoint Response Tracking Code: %s", Appmax_Payments_Helper::encode_object( $response_tracking_body->data ) ) . PHP_EOL;
        $log_content .= PHP_EOL;
        $this->create_log( $log_content );

        if ( $response_tracking_body ) {
            $order->add_order_note( sprintf( "Adicionado o código de rastreamento: %s", $tracking_code ), true );
        }
    }
}