<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_WebHook_Events
 */
abstract class Appmax_Payments_WebHook_Events
{
    /**
     * @var array
     */
    protected array $data;

    /**
     * @var WC_Logger
     */
    protected WC_Logger $log;

    /**
     * @var WC_Order $order
     */
    protected WC_Order $order;

    /**
     * @var object
     */
    protected object $gateway;

    /**
     * @param $data
     */
    public function __construct( $data )
    {
        $this->data = $data;
        $this->log = new WC_Logger();
        $this->order = $this->get_order_by_external_id( $this->data['id'] );
        $this->gateway = (object) $this->get_gateway_by_payment_type( $this->data['payment_type'] );
    }

    /**
     * @param $message
     */
    public function add_log( $message )
    {
        $this->log->add( Appmax_Payments_Helper::replace( "_", "-", "appmax_webhook"), $message );
    }

    public function generate_log_response_webhook()
    {
        $log_content = $this->header_date();
        $log_content .= sprintf( "* IP: %s", Appmax_Payments_Helper::get_ip() ) . PHP_EOL;

        if (! $this->data['upsell_order_id'] ) {
            $log_content .= sprintf( "* Order ID: %d", $this->data['id'] ) . PHP_EOL;
        }

        if ( $this->data['upsell_order_id'] ) {
            $log_content .= sprintf( "* Order Upsell ID: %d", $this->data['id'] ) . PHP_EOL;
        }

        $log_content .= sprintf( "* Payment Method: %s", $this->data['payment_type'] ) . PHP_EOL;
        $log_content .= sprintf( "* Order Origin: %s", $this->data['origin'] ) . PHP_EOL;
        $log_content .= sprintf( "* Order Status: %s", $this->data['status'] ) . PHP_EOL;
        $log_content .= sprintf( "* Total Products: %s", Appmax_Payments_Helper::monetary_format( $this->data['total_products'] ) ) . PHP_EOL;
        $log_content .= sprintf( "* Freight Value: %s", Appmax_Payments_Helper::monetary_format( $this->data['freight_value'] ) ) . PHP_EOL;
        $log_content .= sprintf( "* Discount: %s", Appmax_Payments_Helper::monetary_format( $this->data['discount'] ) ) . PHP_EOL;
        $log_content .= sprintf( "* Interest: %s", Appmax_Payments_Helper::monetary_format( $this->data['interest'] ) ) . PHP_EOL;
        $log_content .= sprintf( "* Order Total: %s", Appmax_Payments_Helper::monetary_format( $this->data['total'] ) ) . PHP_EOL;
        $log_content .= sprintf( "* Created At: %s", Appmax_Payments_Helper::date_time_formatted( $this->data['created_at'] ) ) . PHP_EOL;

        if ( $this->data['paid_at'] ) {
            $log_content .= sprintf( "* Paid At: %s", Appmax_Payments_Helper::date_time_formatted( $this->data['paid_at'] ) ) . PHP_EOL;
        }

        if ( $this->data['integrated_at'] ) {
            $log_content .= sprintf( "* Integrated At: %s", Appmax_Payments_Helper::date_time_formatted( $this->data['integrated_at'] ) ) . PHP_EOL;
        }

        if ( $this->data['refunded_at'] ) {
            $log_content .= sprintf( "* Refunded At: %s", Appmax_Payments_Helper::date_time_formatted( $this->data['refunded_at'] ) ) . PHP_EOL;
        }

        $log_content .= sprintf( "* Response: %s", Appmax_Payments_Helper::encode_object( $this->data ) ) . PHP_EOL;

        $this->add_log($log_content);
    }

    /**
     * @return string
     */
    public function header_date(): string
    {
        $date = Appmax_Payments_Helper::date_time_formatted( date( 'Y-m-d H:i:s' ) );
        return sprintf( "Webhook disparado em %s", $date ) . PHP_EOL;
    }

    /**
     * @throws WC_Data_Exception
     */
    public function create_order()
    {
        $address_street = $this->data['customer']['address_street'];
        $address_street_number = $this->data['customer']['address_street_number'];
        $address_street_district = $this->data['customer']['address_street_district'];

        $address = array(
            "first_name" => $this->data['customer']['firstname'],
            "last_name" => $this->data['customer']['lastname'],
            "email" => $this->data['customer']['email'],
            "phone" => Appmax_Payments_Helper::phone_formatted( $this->data['customer']['telephone'] ),
            "address_1" => sprintf( "%s, %s - %s", $address_street, $address_street_number, $address_street_district ),
            "address_2" => $this->data['customer']['address_street_complement'],
            "city" => $this->data['customer']['address_city'],
            "state" => $this->data['customer']['address_state'],
            "postcode" => Appmax_Payments_Helper::cep_formatted( $this->data['customer']['postcode'] ),
            "country" => "BR",
        );

        $order = wc_create_order();

        $order_note = "Processado por Appmax" . PHP_EOL;

        if (! $this->data['upsell_order_id'] ) {
            $order_note .= sprintf( "Pedido #%d", $this->data['id'] ) . PHP_EOL;
        }

        if ( $this->data['upsell_order_id'] ) {
            $order_note .= sprintf( "Pedido de Upsell #%d para o pedido #%d", $this->data['id'], $this->data['upsell_order_id'] ) . PHP_EOL;
        }

        $order->add_order_note( $order_note );

        $order->set_address( $address, "billing" );
        $order->set_address( $address, "shipping" );

        $order->update_meta_content( "_billing_cpf", Appmax_Payments_Helper::cpf_formatted( $this->data['customer']['document_number'] ) );

        $order->set_total( Appmax_Payments_Helper::number_format( $this->data['total'] ) );
        $order->set_billing_phone( Appmax_Payments_Helper::phone_formatted( $this->data['customer']['telephone'] ) );

        $status = Appmax_Payments_Order_Status::PROCESSING;

        if ($this->gateway->status_order_created == Appmax_Payments_Order_Status::PENDING) {
            $status = Appmax_Payments_Order_Status::PENDING;
        }

        if ($this->data['status'] == Appmax_Payments_Status_Appmax::AUTHORIZED) {
            $status = match ( $this->gateway->order_authorized )
            {
                Appmax_Payments_Order_Status::PROCESSING => Appmax_Payments_Order_Status::PROCESSING,
                Appmax_Payments_Order_Status::ON_HOLD => Appmax_Payments_Order_Status::ON_HOLD
            };
        }

        $order_note = sprintf( "Total de produtos: %s", Appmax_Payments_Helper::monetary_format( $this->data['total_products'] ) ) . PHP_EOL;
        $order_note .= sprintf( "Valor de frete: %s", Appmax_Payments_Helper::monetary_format( $this->data['freight_value'] ) ) . PHP_EOL;
        $order_note .= sprintf( "Desconto: %s", Appmax_Payments_Helper::monetary_format( $this->data['discount'] ) ) . PHP_EOL;
        $order_note .= sprintf( "Juros: %s", Appmax_Payments_Helper::monetary_format( $this->data['interest'] ) ) . PHP_EOL;
        $order_note .= sprintf( "Total do pedido: %s", Appmax_Payments_Helper::monetary_format( $this->data['total'] ) ) . PHP_EOL;

        $order->add_order_note( $order_note );

        $order->update_status( $status );

        $log_content = sprintf( "* Adicionando produtos ao pedido #%d", $order->get_id() ) . PHP_EOL;

        foreach ($this->data['bundles'] as $bundle) {

            $log_content .= sprintf( "* Produtos do pacote %s", $bundle['name'] ) . PHP_EOL;

            foreach ($bundle['products'] as $product) {

                $product_woo_commerce = $this->get_product_by_sku( Appmax_Payments_Helper::get_sku( $product['sku'] ) );

                if ( $product_woo_commerce ) {
                    $order->add_product( $product_woo_commerce, $product['quantity'] );
                    $log_content .= sprintf( "* %d x \"%s\" adicionado ao pedido #%d" , $product['quantity'], $product['name'], $order->get_id() ) . PHP_EOL;
                }

                if (! $product_woo_commerce ) {
                    $log_content .= sprintf( "* ATENÇÃO! Produto %s não foi encontrado.", $product['name'] ) . PHP_EOL;
                    $log_content .= sprintf( "* Cadastrando o produto %s no WooCommerce.", $product['name'] ) . PHP_EOL;

                    $new_product = new WC_Product();
                    $new_product->set_sku( $product['sku'] );
                    $new_product->set_name( $product['name'] );
                    $new_product->set_description( $product['description'] );
                    $new_product->set_short_description( $product['description'] );
                    $new_product->set_price( $product['price'] );
                    $new_product->set_regular_price( $product['price'] );
                    $new_product->set_status( 'pending' );
                    $new_product->save();

                    $log_content .= sprintf( "* Produto %s salvo com sucesso.", $product['name'] ) . PHP_EOL;

                    $order->add_product( $new_product, $product['quantity'] );

                    $log_content .= sprintf( "* %d x \"%s\" adicionado ao pedido #%d" , $product['quantity'], $product['name'], $order->get_id() ) . PHP_EOL;
                }
            }
        }

        $log_content .= sprintf( "* Pedido #%d salvo com sucesso.", $order->get_id() ) . PHP_EOL;

        $this->add_log( $log_content );

        $order_raw_data = wc_get_order( $order );
        $order_data = json_decode( $order_raw_data, true );
        $order_id = $order_data['id'];

        update_post_meta( $order_id,'appmax_order_id', $this->data['id'] );
        update_post_meta( $order_id,'appmax_upsell_parent_id', $this->data['upsell_order_id'] );
    }

    /**
     * @param $external_id
     * @return WC_Order|bool
     */
    private function get_order_by_external_id( $external_id ): WC_Order|bool
    {
        global $wpdb;
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "select * from {$wpdb->postmeta} where `meta_key` = %s and `meta_value` = %s limit 1",
                'appmax_order_id', $external_id
            )
        );
        return wc_get_order( $result->post_id );
    }

    /**
     * @param $sku
     * @return false|WC_Product|null
     */
    private function get_product_by_sku( $sku ): bool|WC_Product|null
    {
        global $wpdb;
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "select * from {$wpdb->postmeta} where `meta_key` = %s and `meta_value` = %s limit 1",
                '_sku', $sku
            )
        );
        return wc_get_product( $result->post_id );
    }

    /**
     * @param $payment_type
     * @return mixed
     */
    public function get_gateway_by_payment_type( $payment_type ): mixed
    {
        $gateway_id = match ( $payment_type )
        {
            Appmax_Payments_Payment_Type::BILLET => Appmax_Payments_Gateways::APPMAX_BOLETO,
            Appmax_Payments_Payment_Type::CREDIT_CARD => Appmax_Payments_Gateways::APPMAX_CREDIT_CARD,
            Appmax_Payments_Payment_Type::PIX => Appmax_Payments_Gateways::APPMAX_PIX
        };

        global $wpdb;
        $option_name = "woocommerce_{$gateway_id}_settings";
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "select option_value from {$wpdb->options} where `option_name` = %s limit 1",
                $option_name
            )
        );
        return unserialize( $result->option_value );
    }

    /**
     * @return string
     */
    public function change_status_order_to_processing(): string
    {
        $status = Appmax_Payments_Order_Status::PROCESSING;
        $this->order->update_status($status);
        return $status;
    }

    /**
     * @param string $message
     * @param array $args
     * @return string
     */
    public function register_order_note(string $message, array $args): string
    {
        $order_note = Appmax_Payments_Helper::string_formatted( $message, $args );
        $this->order->add_order_note($order_note);
        return $order_note;
    }
}