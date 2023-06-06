<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Endpoints_Api
{
    const ENDPOINT_CUSTOMER = "wordpress/customer/";
    const ENDPOINT_ORDER = "wordpress/order/";
    const ENDPOINT_PAYMENT_BILLET = "wordpress/payment/boleto/";
    const ENDPOINT_PAYMENT_CREDIT_CARD = "wordpress/payment/credit-card/";
    const ENDPOINT_PAYMENT_PIX = "wordpress/payment/pix/";
    const ENDPOINT_TRACKING_CODE = "order/delivery-tracking-code";
    const ENDPOINT_FLEXIBLE_INTEREST = "wordpress/flexible-interest";
}