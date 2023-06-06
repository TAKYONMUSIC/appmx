<?php

if ( ! defined('ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Payment_Not_Authorized
 *
 * @extends Appmax_Payments_WebHook_Events
 */
class Appmax_Payments_Payment_Not_Authorized extends Appmax_Payments_WebHook_Events implements Appmax_Payments_WebHook_Events_Contract
{
    /**
     * @param $content
     */
    public function __construct( $content )
    {
        parent::__construct( $content );
    }

    public function process()
    {
        $this->generate_log_response_webhook();

        $this->order->update_status( Appmax_Payments_Order_Status::CANCELLED );

        $log_content = $this->header_date();
        $log_content .= Appmax_Payments_Helper::string_formatted(
            "* Status do pedido #%d alterado para %s.",
            array(
                $this->order->get_order_number(),
                Appmax_Payments_Helper::get_translate_status( Appmax_Payments_Order_Status::CANCELLED )
            )
        );

        $this->add_log( $log_content );
    }
}