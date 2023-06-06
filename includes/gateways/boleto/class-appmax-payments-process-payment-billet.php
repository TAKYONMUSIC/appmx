<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Process_Payment_Billet
 *
 * @extends Appmax_Payments_Process_Payment
 */
class Appmax_Payments_Process_Payment_Billet extends Appmax_Payments_Process_Payment
{
    /**
     * @var Appmax_payments_Check_Response
     */
    private Appmax_payments_Check_Response $check_response;

    /**
     * @param WC_Payment_Gateway $gateway
     */
    public function __construct( WC_Payment_Gateway $gateway )
    {
        parent::__construct( $gateway );
        $this->check_response = new Appmax_payments_Check_Response( $gateway->id );
    }

    /**
     * @param $order_id
     * @return array
     * @throws Exception
     */
    public function process_payment( $order_id ): array
    {
        $order = wc_get_order( $order_id );

        $order->add_meta_data( '_appmax_type_payment', Appmax_Payments_Payment_Type::BILLET );

        if ( $this->gateway->enable_debug() ) {
            $log_content = "============================================================" . PHP_EOL;
            $log_content .= sprintf( "* Appmax - Boleto - #%s - %s", $order->get_order_number(), Appmax_Payments_Helper::date_time_formatted( date( 'Y-m-d H:i:s' ) ) ) . PHP_EOL;
            $this->gateway->add_log( $log_content );
        }

        $post_information = new Appmax_Payments_Post_Information( $order );

        $response_customer = $this->appmax->request(
            'POST',
            Appmax_Payments_Endpoints_Api::ENDPOINT_CUSTOMER,
            $post_information->make_body_customer()
        );

        $this->check_response->verify_server( $response_customer );

        $response_customer_body = $this->check_response->check_response_customer( $response_customer );

        if ( $this->gateway->enable_debug() ) {
            $log_content = sprintf( "* Endpoint Response Customer: %s", Appmax_Payments_Helper::encode_object( $response_customer_body->data ) ) . PHP_EOL;
            $this->gateway->add_log( $log_content );
        }

        $post_information->set_cart_products( new Appmax_Payments_Cart_Products() );

        $response_order = $this->appmax->request(
            'POST',
            Appmax_Payments_Endpoints_Api::ENDPOINT_ORDER,
            $post_information->make_body_order( [
                'customer_id' => $response_customer_body->data->id,
                'interest_total' => 0
            ] )
        );

        $response_order_body = $this->check_response->check_response_order( $response_order );

        $order->add_order_note( sprintf( "Appmax Order ID: %s", $response_order_body->data->id ), true );
        $order->add_meta_data( '_appmax_order_id', $response_order_body->data->id );
        $order->add_meta_data( '_appmax_tracking_code','' );
        update_post_meta( $order->get_order_number(),'appmax_tracking_code', '' );
        update_post_meta( $order->get_order_number(),'appmax_order_id', $response_order_body->data->id );

        if ( $this->gateway->enable_debug() ) {
            $log_content = sprintf( "* Endpoint Response Order: %s", Appmax_Payments_Helper::encode_object( $response_order_body->data ) ) . PHP_EOL;
            $this->gateway->add_log( $log_content );
        }

        $generator = new Appmax_Payments_Due_Date_Generator( $this->gateway->settings['due_days'] );
        $generator->generate();

        $response_payment = $this->appmax->request(
            'POST',
            Appmax_Payments_Endpoints_Api::ENDPOINT_PAYMENT_BILLET,
            $post_information->make_body_payment_boleto( [
                'order_id' => $response_order_body->data->id,
                'customer_id' => $response_customer_body->data->id,
                'payment' => array_merge(
                    $this->make_post_payment_billet(),
                    [
                        'due_date' => $generator->get_due_date()
                    ]
                )
            ] )
        );

        $response_payment_body = $this->check_response->check_response_payment( $response_payment, $order );

        $order->update_status( Appmax_Payments_Order_Status::PENDING );

        $order->add_order_note( sprintf( "Boleto: %s", $response_payment_body->data->pdf ), true );
        $order->add_order_note( sprintf( "Pay Reference: %s", $response_payment_body->data->pay_reference ), true );
        $order->add_order_note( sprintf( "Digitable Line: %s", $response_payment_body->data->digitable_line ), true );

        $order->add_meta_data( '_appmax_link_billet', $response_payment_body->data->pdf );
        $order->add_meta_data( '_appmax_pay_reference', $response_payment_body->data->pay_reference );
        $order->add_meta_data( '_appmax_digitable_line', $response_payment_body->data->digitable_line );

        update_post_meta( $order->get_order_number(),'appmax_link_billet', $response_payment_body->data->pdf );
        update_post_meta( $order->get_order_number(),'appmax_pay_reference', $response_payment_body->data->pay_reference );
        update_post_meta( $order->get_order_number(),'appmax_digitable_line', $response_payment_body->data->digitable_line );

        if ( $this->gateway->enable_debug() ) {
            $log_content = sprintf( "* Endpoint Response Payment: %s", Appmax_Payments_Helper::encode_object( $response_payment_body ) ) . PHP_EOL;
            $this->gateway->add_log( $log_content );
        }

        $post_payment['link_billet'] = $response_payment_body->data->pdf;
        $post_payment['pay_reference'] = $response_payment_body->data->pay_reference;
        $post_payment['digitable_line'] = $response_payment_body->data->digitable_line;

        $this->save_order_meta_fields( $order->get_order_number(), [
            'type_payment' => Appmax_Payments_Payment_Type::BILLET,
            'post_payment' => $post_payment,
        ] );

        WC()->cart->empty_cart();

        return array(
            'result' => 'success',
            'redirect' => $this->gateway->get_return_url( $order ),
        );
    }

    /**
     * @return array
     */
    private function make_post_payment_billet(): array
    {
        return array(
            'cpf_billet' => Appmax_Payments_Helper::cpf_unformatted( Appmax_Payments_Helper::clear_input( $_POST['cpf_billet'] ) ),
        );
    }
}