<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Tracking_Code
 */
abstract class Appmax_Payments_Tracking_Code
{
    /**
     * @var string
     */
    protected string $api_key;

    /**
     * @var string
     */
    protected string $id;

    /**
     * @var WC_Logger
     */
    protected WC_Logger $logger;

    /**
     * @var Appmax_payments_Check_Response
     */
    protected Appmax_payments_Check_Response $check_response;

    /**
     * @var Appmax_Payments_Api
     */
    protected Appmax_Payments_Api $api;

    /**
     * @param string $api_key
     */
    public function __construct( string $api_key )
    {
        $this->api = new Appmax_Payments_Api( $api_key );
        $this->id = 'appmax_tracking_code';
        $this->logger = new WC_Logger();
        $this->check_response = new Appmax_payments_Check_Response( $this->id );
    }

    /**
     * @param $message
     */
    public function create_log( $message )
    {
        $this->logger->add( Appmax_Payments_Helper::replace( "_", "-", $this->id), $message );
    }

    /**
     * @param $metas
     * @return mixed
     */
    public function get_tracking_code( $metas ): mixed
    {
        foreach ( $metas as $meta ) {
            if ( $meta['key'] == "appmax_tracking_code" ) {
                return $meta['value'];
            }
        }

        return null;
    }

    /**
     * @param $request
     * @return bool
     */
    public function validate_correios_tracking_code($request): bool
    {
        if ( ! isset( $request['action'] ) ) {
            return false;
        }

        if ( $request['action'] != 'woocommerce_correios_add_tracking_code' ) {
            return false;
        }

        return isset( $request['tracking_code'] );
    }
}