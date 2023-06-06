<?php

if ( ! defined('ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Order_Paid
 *
 * @extends Appmax_Payments_WebHook_Events
 */
class Appmax_Payments_Order_Paid extends Appmax_Payments_WebHook_Events implements Appmax_Payments_WebHook_Events_Contract
{
    /**
     * @param $content
     */
    public function __construct( $content )
    {
        parent::__construct( $content );
    }

    /**
     * @throws WC_Data_Exception
     */
    public function process(): bool
    {
        $this->generate_log_response_webhook();

        if ( $this->data['origin'] == Appmax_Payments_Origin_Order::API && $this->order ) {

            $message = "Status atual do pedido #%d na plataforma Appmax: %s";
            $args = array(
                $this->data['id'],
                Appmax_Payments_Helper::first_character_in_upper_case( $this->data['status'] )
            );

            $log_content = $this->header_date();
            $log_content .= Appmax_Payments_Helper::string_formatted( $message, $args ) . PHP_EOL;

            $this->add_log( $log_content );

            $message = "O pedido #%d permanecerá com o status atual (%s).";
            $args = array(
                $this->order->get_order_number(),
                Appmax_Payments_Helper::get_translate_status( $this->order->get_status() )
            );

            $status = Appmax_Payments_Order_Status::PROCESSING;

            if ($this->data['status'] == Appmax_Payments_Status_Appmax::AUTHORIZED) {

                if ($this->gateway->order_authorized == Appmax_Payments_Order_Status::ON_HOLD) {
                    $status = Appmax_Payments_Order_Status::ON_HOLD;
                }

                $this->order->update_status( $status );

                $message = "Status do pedido #%d alterado para %s.";
                $args = array(
                    $this->order->get_order_number(),
                    Appmax_Payments_Helper::get_translate_status( $status )
                );
            }

            if ($this->data['status'] == Appmax_Payments_Status_Appmax::APPROVED) {
                $this->order->update_status( $status );

                $message = "Status do pedido #%d alterado para %s.";
                $args = array(
                    $this->order->get_order_number(),
                    Appmax_Payments_Helper::get_translate_status( $status )
                );
            }

            $log_content = $this->header_date();
            $log_content .= Appmax_Payments_Helper::string_formatted( $message, $args ) . PHP_EOL;

            $this->add_log( $log_content );
            return true;
        }

        $this->create_order();
        return true;
    }
}