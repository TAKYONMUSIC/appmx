<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Api
 */
class Appmax_Payments_Api
{
    /**
     * @var string
     */
    private string $api_key;

    /**
     * @param string $api_key
     */
    public function __construct( string $api_key )
    {
        $this->api_key = $api_key;
    }

    /**
     * @return string
     */
    public function get_api_url(): string
    {
        return "https://admin.appmax.com.br/api/v3/";
    }

    /**
     * @param $endpoint
     * @return string
     */
    private function get_full_url( $endpoint ): string
    {
        if ( APPMAX_PAYMENTS_URL_API_DOMAIN ) {
            return APPMAX_PAYMENTS_URL_API_DOMAIN . $endpoint;
        }

        return $this->get_api_url() . $endpoint;
    }

    /**
     * @param $method
     * @param $endpoint
     * @param array $data
     * @return array|WP_Error
     */
    public function request( $method, $endpoint, array $data = [] ): WP_Error|array
    {
        if ( 'POST' == strtoupper( $method ) ) {
            return $this->post( $this->get_full_url( $endpoint ), $data );
        }

        return $this->get( $this->get_full_url( $endpoint ) );
    }

    /**
     * @param $url
     * @param array $data
     * @return array|WP_Error
     */
    private function post( $url, array $data ): WP_Error|array
    {
        return wp_remote_post( $url, [
            "headers" => $this->get_header(),
            "body" => Appmax_Payments_Helper::encode_object( $data ),
            "method" => "POST",
            "data_format" => "data"
        ] );
    }

    /**
     * @param $url
     * @return array|WP_Error
     */
    private function get( $url ): WP_Error|array
    {
        return wp_remote_get( $url, [
            "headers" => $this->get_header(),
            "method" => "GET",
        ] );
    }

    /**
     * @return array
     */
    private function get_header(): array
    {
        $headers = array(
            'Content-Type' => 'application/json; charset=utf-8',
            'access-token' => $this->api_key
        );

        if ( APPMAX_PAYMENTS_URL_API_HOST ) {
            return array_merge( $headers, [
                'host' => APPMAX_PAYMENTS_URL_API_HOST
            ] );
        }

        return $headers;
    }
}