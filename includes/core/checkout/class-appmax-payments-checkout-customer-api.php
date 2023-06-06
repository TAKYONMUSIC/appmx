<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Appmax_Payments_Checkout_Customer_Api
 */
class Appmax_Payments_Checkout_Customer_Api
{
	/**
	 * Construct
	 */
	public function __construct()
    {
		// Legacy REST API.
		add_filter( 'woocommerce_api_order_response', array( $this, 'legacy_orders_response' ), 100, 4 );
		add_filter( 'woocommerce_api_customer_response', array( $this, 'legacy_customers_response' ), 100, 4 );

		// WP REST API.
		add_filter( 'woocommerce_rest_prepare_customer', array( $this, 'customers_response' ), 100, 2 );
		add_filter( 'woocommerce_rest_prepare_shop_order', array( $this, 'orders_v1_response' ), 100, 2 );
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'orders_response' ), 100, 2 );
		add_filter( 'woocommerce_rest_customer_schema', array( $this, 'addresses_schema' ), 100 );
		add_filter( 'woocommerce_rest_shop_order_schema', array( $this, 'addresses_schema' ), 100 );
	}

	/**
	 * Add extra fields in legacy order response.
	 *
	 * @param array $order_data Order response data..
	 * @param WC_Order $order      Order object.
	 * @param array $fields     Fields filter.
	 *
	 * @return array
	 */
	public function legacy_orders_response(array $order_data, WC_Order $order, array $fields ): array
    {
		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_meta' ) ) {

			$order_data['billing_address']['number']       = $order->get_meta( '_billing_number' );
			$order_data['billing_address']['neighborhood'] = $order->get_meta( '_billing_neighborhood' );
			$order_data['billing_address']['cellphone']    = $order->get_meta( '_billing_cellphone' );

			if ( 0 === intval( $order->customer_user ) && isset( $order_data['customer'] ) ) {
                $order_data['customer']['billing_address']['number']       = $order->get_meta( '_billing_number' );
				$order_data['customer']['billing_address']['neighborhood'] = $order->get_meta( '_billing_neighborhood' );
				$order_data['customer']['billing_address']['cellphone']    = $order->get_meta( '_billing_cellphone' );
			}
		} else {
			$order_data['billing_address']['number']       = $order->billing_number;
			$order_data['billing_address']['neighborhood'] = $order->billing_neighborhood;
			$order_data['billing_address']['cellphone']    = $order->billing_cellphone;

			if ( 0 === intval( $order->customer_user ) && isset( $order_data['customer'] ) ) {
				$order_data['customer']['billing_address']['number']       = $order->billing_number;
				$order_data['customer']['billing_address']['neighborhood'] = $order->billing_neighborhood;
				$order_data['customer']['billing_address']['cellphone']    = $order->billing_cellphone;
			}
		}

		if ( $fields ) {
			$order_data = WC()->api->WC_API_Customers->filter_response_fields( $order_data, $order, $fields );
		}

		return $order_data;
	}

	/**
	 * Add extra fields in legacy customers response.
	 *
	 * @param array $customer_data Customer response data..
	 * @param WC_Customer $customer      Customer object.
	 * @param array $fields        Fields filter.
	 *
	 * @return array
	 */
	public function legacy_customers_response(array $customer_data, WC_Customer $customer, array $fields ): array
    {
		// WooCommerce 3.0 or later.
		if ( method_exists( $customer, 'get_meta' ) ) {
			$customer_data['billing_address']['number']       = $customer->get_meta( 'billing_number' );
			$customer_data['billing_address']['neighborhood'] = $customer->get_meta( 'billing_neighborhood' );
			$customer_data['billing_address']['cellphone']    = $customer->get_meta( 'billing_cellphone' );
		} else {
			$customer_data['billing_address']['number']       = $customer->billing_number;
			$customer_data['billing_address']['neighborhood'] = $customer->billing_neighborhood;
			$customer_data['billing_address']['cellphone']    = $customer->billing_cellphone;
        }

		if ( $fields ) {
			$customer_data = WC()->api->WC_API_Customers->filter_response_fields( $customer_data, $customer, $fields );
		}

		return $customer_data;
	}

    /**
     * Add extra fields in customers response.
     *
     * @param WP_REST_Response $response The response object.
     * @param WP_User $user User object used to create response.
     *
     * @return WP_REST_Response
     * @throws Exception
     */
	public function customers_response(WP_REST_Response $response, WP_User $user ): WP_REST_Response
    {
		$customer = new WC_Customer( $user->ID );

		// WooCommerce 3.0 or later.
		if ( method_exists( $customer, 'get_meta' ) ) {
            $response->data['billing']['number'] = $customer->get_meta('billing_number');
            $response->data['billing']['neighborhood'] = $customer->get_meta('billing_neighborhood');
            $response->data['billing']['cellphone'] = $customer->get_meta('billing_cellphone');

            return $response;
        }

        $response->data['billing']['number']       = $customer->billing_number;
        $response->data['billing']['neighborhood'] = $customer->billing_neighborhood;
        $response->data['billing']['cellphone']    = $customer->billing_cellphone;

		return $response;
	}

	/**
	 * Add extra fields in orders v1 response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post $post     Post object.
	 *
	 * @return WP_REST_Response
	 */
	public function orders_v1_response(WP_REST_Response $response, WP_Post $post ): WP_REST_Response
    {
		$order = wc_get_order( $post->ID );

		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_meta' ) ) {
			return $this->orders_response( $response, $order );
		}

        $response->data['billing']['number']       = $order->billing_number;
        $response->data['billing']['neighborhood'] = $order->billing_neighborhood;
        $response->data['billing']['cellphone']    = $order->billing_cellphone;

		return $response;
	}

	/**
	 * Add extra fields in orders response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WC_Order $order    Order object.
	 *
	 * @return WP_REST_Response
	 */
	public function orders_response( WP_REST_Response $response, WC_Order $order ): WP_REST_Response
    {
		$response->data['billing']['number']       = $order->get_meta( '_billing_number' );
		$response->data['billing']['neighborhood'] = $order->get_meta( '_billing_neighborhood' );
		$response->data['billing']['cellphone']    = $order->get_meta( '_billing_cellphone' );

		return $response;
	}

	/**
	 * Addresses schena.
	 *
	 * @param array $properties Default schema properties.
	 *
	 * @return array
	 */
	public function addresses_schema( array $properties ): array
    {
		$properties['billing']['properties']['number'] = array(
			'description' => __( 'Number.', 'appmax' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
		);
		$properties['billing']['properties']['neighborhood'] = array(
			'description' => __( 'Neighborhood.', 'appmax' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
		);
		$properties['billing']['properties']['cellphone'] = array(
			'description' => __( 'Cell Phone.', 'appmax' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
		);

		return $properties;
	}
}

new Appmax_Payments_Checkout_Customer_Api();
