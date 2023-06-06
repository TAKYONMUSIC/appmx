<?php

if ( ! defined('ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Order_Integrated
 *
 * @extends Appmax_Payments_WebHook_Events
 */
class Appmax_Payments_Order_Integrated extends Appmax_Payments_WebHook_Events implements Appmax_Payments_WebHook_Events_Contract
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

        if ( in_array( $this->data['origin'], Appmax_Payments_Origin_Order::callCenter() ) && $this->order ) {
            $order_note = $this->register_order_note(
                "Status atual do pedido #%d na plataforma Appmax: %s",
                array(
                    $this->data['id'],
                    Appmax_Payments_Helper::first_character_in_upper_case( $this->data['status'] )
                )
            );

            $log_content = $this->header_date();
            $log_content .= $order_note . PHP_EOL;
            $this->add_log( $log_content );

            if ($this->data['status'] == Appmax_Payments_Status_Appmax::INTEGRATED) {
                $status = $this->change_status_order_to_processing();
                $order_note = $this->register_order_note(
                    "Status do pedido #%d alterado para %s.",
                    array(
                        $this->order->get_order_number(), Appmax_Payments_Helper::get_translate_status( $status )
                    )
                );
            }

            if ($this->data['status'] != Appmax_Payments_Status_Appmax::INTEGRATED) {
                $order_note = $this->register_order_note(
                    "O pedido #%d permanecerá com o status atual (%s).",
                    array(
                        $this->order->get_order_number(),
                        Appmax_Payments_Helper::get_translate_status( $this->order->get_status() )
                    )
                );
            }

            $log_content = $this->header_date();
            $log_content .= $order_note . PHP_EOL;

            $this->add_log( $log_content );
            return true;
        }

        if ( in_array( $this->data['origin'], Appmax_Payments_Origin_Order::callCenter() ) &&
            ! $this->order &&
            $this->gateway->order_call_center == Appmax_Payments_Events::ORDER_INTEGRATED
        ) {
            $this->create_order();
            return true;
        }

        $order_note = $this->register_order_note(
            "Status atual do pedido #%d na plataforma Appmax: %s",
            array(
                $this->data['id'],
                Appmax_Payments_Helper::first_character_in_upper_case( $this->data['status'] )
            )
        );

        $log_content = $this->header_date();
        $log_content .= $order_note . PHP_EOL;
        $this->add_log( $log_content );

        if ($this->data['status'] == Appmax_Payments_Status_Appmax::INTEGRATED) {
            $status = $this->change_status_order_to_processing();
            $order_note = $this->register_order_note(
                "Status do pedido #%d alterado para %s.",
                array(
                    $this->order->get_order_number(),
                    Appmax_Payments_Helper::get_translate_status( $status )
                )
            );
        }

        if ($this->data['status'] != Appmax_Payments_Status_Appmax::INTEGRATED) {
            $order_note = $this->register_order_note(
                "O pedido #%d permanecerá com o status atual (%s).",
                array(
                    $this->order->get_order_number(),
                    Appmax_Payments_Helper::get_translate_status( $this->order->get_status() )
                )
            );
        }

        $log_content = $this->header_date();
        $log_content .= $order_note . PHP_EOL;

        $this->add_log( $log_content );
        return true;
    }
}