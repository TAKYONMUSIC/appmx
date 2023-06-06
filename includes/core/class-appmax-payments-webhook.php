<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_WebHook
 */
class Appmax_Payments_WebHook
{
    /**
     * Construct
     */
    public function __construct()
    {
        add_action( 'rest_api_init', fn( $server ) =>
            $server->register_route( 'webhook-system', '/webhook-system', [
                'methods' => 'POST',
                'callback' => fn( WP_REST_Request $request ) => $this->process_event( $request )
            ] )
        );
    }

    /**
     * @param WP_REST_Request $request
     */
    public function process_event( WP_REST_Request $request )
    {
        $content = $request->get_params();
        $event_class = Appmax_Payments_Helper::current_class_event( Appmax_Payments_Helper::trim_event( $content['event'] ) );
        (new $event_class($content['data']))->process();
    }
}

new Appmax_Payments_WebHook();