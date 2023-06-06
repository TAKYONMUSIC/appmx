<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Check_Response
 */
class Appmax_Payments_Check_Response
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var WC_Logger
     */
    private WC_Logger $logger;

    /**
     * @param string $id
     */
    public function __construct( string $id )
    {
        $this->id = $id;
        $this->logger = new WC_Logger();
    }

    /**
     * @param $message
     */
    public function create_log( $message )
    {
        $this->logger->add( $this->id, $message );
    }

    /**
     * @throws Exception
     */
    public function check_response_customer( $response )
    {
        $this->verify_errors_curl( $response, Appmax_Payments_Errors_Api::MESSAGE_ERROR_CUSTOMER );

        $response_body = Appmax_Payments_Helper::decode_object( wp_remote_retrieve_body( $response ) );

        $this->verify_access_token( $response_body );

        if ( ! $response_body->success and $response_body->text == Appmax_Payments_Errors_Api::VALIDATE_REQUEST ) {

            $message_exception = $this->make_message_exception(
                Appmax_Payments_Errors_Api::MESSAGE_ERROR_CUSTOMER,
                $response_body->data
            );

            throw new \Exception( $message_exception );
        }

        return $response_body;
    }

    /**
     * @param $response
     * @return array|mixed|object
     * @throws Exception
     */
    public function check_response_order( $response )
    {
        $this->verify_errors_curl( $response, Appmax_Payments_Errors_Api::MESSAGE_ERROR_ORDER );

        $response_body = Appmax_Payments_Helper::decode_object( wp_remote_retrieve_body( $response ) );

        $this->verify_access_token( $response_body );

        if ( ! $response_body->success and $response_body->text == Appmax_Payments_Errors_Api::VALIDATE_REQUEST ) {

            $message_exception = $this->make_message_exception(
                Appmax_Payments_Errors_Api::MESSAGE_ERROR_ORDER,
                $response_body->data
            );

            throw new \Exception( $message_exception );
        }

        return $response_body;
    }

    /**
     * @throws Exception
     */
    public function check_response_payment( $response, $order )
    {
        $log_content = "";

        if (is_wp_error($response) && $response instanceof WP_Error) {

            $order->update_status( Appmax_Payments_Order_Status::FAILED, Appmax_Payments_Errors_Api::MESSAGE_ERROR_PAYMENT );

            $log_content .= sprintf( "* Falha na transação %s ...", $order->get_order_number() ) . PHP_EOL;
            $log_content .= sprintf( "* Motivo do cancelamento: %s.", Appmax_Payments_Errors_Api::MESSAGE_ERROR_PAYMENT ) . PHP_EOL;
            $log_content .= sprintf( "* Resposta de servidor: %s.", $response->get_error_message() ) . PHP_EOL;
            $this->create_log( $log_content );

            $message_exception = sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_ERROR_PAYMENT );
            throw new \Exception( $message_exception );
        }

        $response_body = Appmax_Payments_Helper::decode_object( wp_remote_retrieve_body( $response ) );

        $this->verify_access_token( $response_body );

        if ( ! $response_body->success and $response_body->text == Appmax_Payments_Errors_Api::VALIDATE_REQUEST ) {
            $message_exception = $this->make_message_exception(
                Appmax_Payments_Errors_Api::MESSAGE_ERROR_PAYMENT,
                $response_body->data
            );

            throw new \Exception( $message_exception );
        }

        if ( ! $response_body->success ) {

            $order->update_status( Appmax_Payments_Order_Status::FAILED, $response_body->text );

            $log_content .= sprintf( "* Falha na transação %s ...", $order->get_order_number() ) . PHP_EOL;
            $log_content .= sprintf( "* Motivo do cancelamento: %s.", $response_body->text ) . PHP_EOL;
            $this->create_log( $log_content );

            $message_exception = sprintf( "%s - %s", Appmax_Payments_Errors_Api::MESSAGE_ERROR_PAYMENT, $response_body->text );
            throw new \Exception( $message_exception );
        }

        return $response_body;
    }

    /**
     * @param $response
     * @return mixed
     * @throws Exception
     */
    public function check_response_tracking_code( $response ): mixed
    {
        $this->verify_errors_curl( $response, Appmax_Payments_Errors_Api::MESSAGE_ERROR_TRACKING );

        $response_body = Appmax_Payments_Helper::decode_object( wp_remote_retrieve_body( $response ) );

        $this->verify_access_token( $response_body );

        if ($response_body->success and $response_body->text == "Store delivery tracking code" ) {
            return $response_body;
        }

        $message_exception = sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_ERROR_TRACKING );

        if ($response_body->data) {
            foreach ($response_body->data as $item) {
                $message_exception .= $item[0];
            }
        }

        throw new \Exception( $message_exception );
    }

    /**
     * @param $message_error_default
     * @param $response_data
     * @return string
     */
    public function make_message_exception( $message_error_default, $response_data ): string
    {
        $message_exception = sprintf( "%s", $message_error_default );

        if (! $response_data) {
            return $message_exception;
        }

        $message_exception .= "<ul>";

        foreach ($response_data as $item) {
            $message_exception .= "<li>" . $item[0] . "</li>";
        }

        $message_exception .= "</ul>";

        return $message_exception;
    }

    /**
     * @param $response
     * @return bool
     * @throws Exception
     */
    public function verify_server( $response ): bool
    {
        $this->verify_errors_curl( $response );

        $data = $response['headers']->getAll();

        $log_content = "";

        if (array_key_exists('cf-ray', $data) &&
            preg_match('/cloudflare/', $data['server']) &&
            $response['response']['code'] != 200
        ) {
            $log_content .= sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_001 ) . PHP_EOL;
            $log_content .= sprintf( "%s - %s (Cloudflare)", $response['response']['code'], $response['response']['message'] ) . PHP_EOL;
            $this->create_log( $log_content );

            $message_exception = sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_001 );
            throw new Appmax_Payments_Server_Exception( $message_exception );
        }

        if (preg_match('/nginx/', $data['server']) &&
            $response['response']['code'] != 200
        ) {
            $log_content .= sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_002 ) . PHP_EOL;
            $log_content .= sprintf( "%s - %s (Nginx)", $response['response']['code'], $response['response']['message'] ) . PHP_EOL;
            $this->create_log( $log_content );

            $message_exception = sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_002 );
            throw new Appmax_Payments_Server_Exception( $message_exception );
        }

        return true;
    }

    /**
     * @param $response_body
     * @return void
     * @throws Exception
     */
    public function verify_access_token( $response_body ): void
    {
        if ( ! $response_body->success and $response_body->text == Appmax_Payments_Errors_Api::INVALID_ACCESS_TOKEN ) {
            $log_content = sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_004 ) . PHP_EOL;
            $this->create_log( $log_content );

            throw new Appmax_Payments_Invalid_Access_Token_Exception( Appmax_Payments_Errors_Api::MESSAGE_004 );
        }
    }

    /**
     * @param $response
     * @param $type
     * @return void
     * @throws Exception
     */
    public function verify_errors_curl( $response, $type = null ): void
    {
        if (is_wp_error($response) && $response instanceof WP_Error) {

            $log_content = "";

            if (! $type) {
                $log_content .= sprintf( "%s", Appmax_Payments_Errors_Api::MESSAGE_003 ) . PHP_EOL;
            }

            if ($type) {
                $log_content .= sprintf( "%s - %s", $type, Appmax_Payments_Errors_Api::MESSAGE_003 ) . PHP_EOL;
            }

            $log_content .= sprintf( "Motivo: %s", $response->get_error_message() ) . PHP_EOL;
            $this->create_log( $log_content );

            throw new Appmax_Payments_Connection_Gateway_Exception( Appmax_Payments_Errors_Api::MESSAGE_003 );
        }
    }
}