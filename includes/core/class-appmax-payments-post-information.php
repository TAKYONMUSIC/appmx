<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Post_Information
{
    /**
     * @var WC_Order
     */
    private WC_Order $order;

    /**
     * @var Appmax_Payments_Cart_Products
     */
    private Appmax_Payments_Cart_Products $cart_products;

    /**
     * @param WC_Order $order
     */
    public function __construct( WC_Order $order )
    {
        $this->order = $order;
    }

    /**
     * @param Appmax_Payments_Cart_Products $cart_products
     * @return $this
     */
    public function set_cart_products( Appmax_Payments_Cart_Products $cart_products ): static
    {
        $this->cart_products = $cart_products;
        return $this;
    }

    /**
     * @return array
     */
    public function make_body_customer(): array
    {
        list( $street, $number ) = explode( ", ", $this->order->data['billing']['address_1'] );

        if ( ! $number ) {
            $number = get_post_meta( $this->order->get_id(), '_billing_number', true );
        }

        return [
            "firstname" => $this->order->data['billing']['first_name'],
            "lastname" => $this->order->data['billing']['last_name'],
            "email" => $this->order->data['billing']['email'],
            "telephone" => $this->order->data['billing']['phone'],
            "postcode" => $this->order->data['billing']['postcode'],
            "address_street" => $street,
            "address_street_number" => $number,
            "address_street_complement" => $this->order->data['billing']['address_2'],
            "address_street_district" => get_post_meta( $this->order->get_id(), '_billing_neighborhood', true ),
            "address_city" => $this->order->data['billing']['city'],
            "address_state" => $this->order->data['billing']['state'],
            "ip" => Appmax_Payments_Helper::get_ip()
        ];
    }

    /**
     * @param array $information_order
     * @return array
     * @throws Exception
     */
    public function make_body_order( array $information_order ): array
    {
        return [
            "customer_id" => $information_order['customer_id'],
            "products" => $this->cart_products->get_products_cart( $information_order['interest_total'] ),
            "shipping" => $this->order->get_shipping_total(),
            "discount" => number_format( Appmax_Payments_Helper::get_discount_total(), 2 ),
            "freight_type" => $this->order->get_shipping_method(),
            "ip" => Appmax_Payments_Helper::get_ip(),
        ];
    }

    /**
     * @param array $information_payment
     * @return array
     */
    public function make_body_payment_credit_card( array $information_payment ): array
    {
        return [
            "cart" => [
                "order_id" => $information_payment['order_id'],
            ],
            "customer" => [
                "customer_id" => $information_payment['customer_id'],
            ],
            "payment" => [
                "CreditCard" => [
                    "number" => $information_payment['payment']['card_number'],
                    "cvv" => $information_payment['payment']['card_security_code'],
                    "month" => $information_payment['payment']['card_month'],
                    "year" => $information_payment['payment']['card_year'],
                    "document_number" => $information_payment['payment']['card_cpf'],
                    "name" => $information_payment['payment']['card_name'],
                    "installments" => $information_payment['payment']['installments'],
                ],
            ],
        ];
    }

    /**
     * @param array $information_payment
     * @return array
     */
    public function make_body_payment_boleto( array $information_payment ): array
    {
        return [
            "cart" => [
                "order_id" => $information_payment['order_id'],
            ],
            "customer" => [
                "customer_id" => $information_payment['customer_id'],
            ],
            "payment" => [
                "Boleto" => [
                    "document_number" => $information_payment['payment']['cpf_billet'],
                    "due_date" => $information_payment['payment']['due_date'],
                ],
            ],
        ];
    }

    /**
     * @param array $information_payment
     * @return array
     */
    public function make_body_payment_pix( array $information_payment ): array
    {
        return [
            "cart" => [
                "order_id" => $information_payment['order_id'],
            ],
            "customer" => [
                "customer_id" => $information_payment['customer_id'],
            ],
            "payment" => [
                "pix" => [
                    "document_number" => $information_payment['payment']['cpf_pix'],
                ],
            ],
        ];
    }

    /**
     * @param $order_id
     * @param $tracking_code
     * @return array
     */
    public function make_body_tracking_code( $order_id, $tracking_code ): array
    {
        return [
            'order_id' => $order_id,
            'delivery_tracking_code' => $tracking_code
        ];
    }
}