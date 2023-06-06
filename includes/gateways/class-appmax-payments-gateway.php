<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Gateway
 *
 * @extends WC_Payment_Gateway
 */
abstract class Appmax_Payments_Gateway extends WC_Payment_Gateway
{
    /**
     * @var Appmax_Payments_Check_Response
     */
    protected Appmax_Payments_Check_Response $check_response;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->check_response = new Appmax_Payments_Check_Response( $this->id );
    }

    /**
     * @return void
     */
    public function process_admin_options(): void
    {
        parent::process_admin_options();

        try {

            $response = ( new Appmax_Payments_Api( $this->get_option('api_key') ) )
                ->request('GET', 'wordpress');

            $this->check_status( $response );

        } catch (\Exception $exception) {
            add_action( 'admin_notices', fn() =>
                wc_get_template(
                    'views/admin/validate-message.php',
                    [ 'message' => $exception->getMessage() ],
                    'appmax/',
                    Appmax_Payments::get_templates_path()
                )
            );
        }
    }

    /**
     * @throws Exception
     */
    public function get_settings_interest()
    {
        $response = ( new Appmax_Payments_Api( $this->get_option('api_key') ) )
            ->request('GET', Appmax_Payments_Endpoints_Api::ENDPOINT_FLEXIBLE_INTEREST);

        $this->check_status( $response );

        return Appmax_Payments_Helper::decode_object( wp_remote_retrieve_body( $response ) );
    }

    /**
     * @param $response
     * @throws Exception
     */
    public function check_status( $response )
    {
        $this->check_response->verify_server( $response );
        $this->check_response->verify_errors_curl( $response );
        $this->check_response->verify_access_token( Appmax_Payments_Helper::decode_object( wp_remote_retrieve_body( $response ) ) );
    }
}