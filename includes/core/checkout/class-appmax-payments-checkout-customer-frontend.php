<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appmax_Payments_Checkout_Customer_Frontend class.
 */
class Appmax_Payments_Checkout_Customer_Frontend
{
    /**
     * Construct
     */
	public function __construct()
    {
		add_action( 'woocommerce_after_edit_account_address_form', array( $this, 'load_scripts' ) );
		add_action( 'woocommerce_after_checkout_form', array( $this, 'load_scripts' ) );

		add_filter( 'woocommerce_billing_fields', array( $this, 'checkout_billing_fields' ), 10 );
		add_filter( 'woocommerce_get_country_locale', array( $this, 'address_fields_priority' ), 10 );

		add_filter( 'woocommerce_localisation_address_formats', array( $this, 'localisation_address_formats' ) );
		add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'formatted_address_replacements' ), 1, 2 );
		add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'order_formatted_billing_address' ), 1, 2 );
		add_filter( 'woocommerce_order_formatted_shipping_address', array( $this, 'order_formatted_shipping_address' ), 1, 2 );
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'my_account_my_address_formatted_address' ), 1, 3 );

		add_filter( 'woocommerce_get_order_address', array( $this, 'order_address' ), 10, 3 );

        add_filter('kses_allowed_protocols', function ($protocols) {
            $protocols[] = 'data';
            return $protocols;
        });
	}

	/**
	 * Load scripts.
	 */
	public function load_scripts()
    {
		wp_enqueue_script( 'appmax-payments-fields-checkout-customer' );
	}

	/**
	 * New checkout billing fields.
	 *
	 * @param array $fields Default fields.
	 *
	 * @return array
	 */
	public function checkout_billing_fields(array $fields ): array
    {
		$new_fields = array();

		if ( isset( $fields['billing_first_name'] ) ) {
			$new_fields['billing_first_name'] = $fields['billing_first_name'];
			$new_fields['billing_first_name']['class'] = array( 'form-row-first' );
		}

		if ( isset( $fields['billing_last_name'] ) ) {
			$new_fields['billing_last_name'] = $fields['billing_last_name'];
			$new_fields['billing_last_name']['class'] = array( 'form-row-last' );
		}

		if ( isset( $fields['billing_country'] ) ) {
			$new_fields['billing_country'] = $fields['billing_country'];
			$new_fields['billing_country']['class'] = array( 'form-row-wide', 'address-field', 'update_totals_on_change' );
		}

		if ( isset( $fields['billing_postcode'] ) ) {
			$new_fields['billing_postcode'] = $fields['billing_postcode'];
			$new_fields['billing_postcode']['class'] = array( 'form-row-first', 'address-field' );
			$new_fields['billing_postcode']['priority'] = 45;
		}

		if ( isset( $fields['billing_address_1'] ) ) {
			$new_fields['billing_address_1'] = $fields['billing_address_1'];
			$new_fields['billing_address_1']['class'] = array( 'form-row-last', 'address-field' );
		}

		$new_fields['billing_number'] = array(
			'label'    => __( 'Number', 'appmax' ),
			'class'    => array( 'form-row-first', 'address-field' ),
			'clear'    => true,
			'required' => true,
			'priority' => 55,
		);

		if ( isset( $fields['billing_address_2'] ) ) {
			$new_fields['billing_address_2'] = $fields['billing_address_2'];
			$new_fields['billing_address_2']['label'] = __( 'Address line 2', 'appmax' );
			$new_fields['billing_address_2']['class'] = array( 'form-row-last', 'address-field' );
		}

		$new_fields['billing_neighborhood'] = array(
			'label'    => __( 'Neighborhood', 'appmax' ),
			'class'    => array( 'form-row-first', 'address-field' ),
			'clear'    => true,
			'priority' => 65,
		);

		if ( isset( $fields['billing_city'] ) ) {
			$new_fields['billing_city'] = $fields['billing_city'];
			$new_fields['billing_city']['class'] = array( 'form-row-last', 'address-field' );
		}

		if ( isset( $fields['billing_state'] ) ) {
			$new_fields['billing_state'] = $fields['billing_state'];
			$new_fields['billing_state']['class'] = array( 'form-row-wide', 'address-field' );
			$new_fields['billing_state']['clear'] = true;
		}

        if ( isset( $fields['billing_phone'] ) ) {
            $new_fields['billing_phone'] = $fields['billing_phone'];
            $new_fields['billing_phone']['class'] = array( 'form-row-wide' );
            $new_fields['billing_phone']['clear'] = true;
        }

        if ( isset( $fields['billing_email'] ) ) {
            $new_fields['billing_email'] = $fields['billing_email'];
            $new_fields['billing_email']['class'] = array( 'form-row-wide' );
            $new_fields['billing_email']['clear'] = true;
            $new_fields['billing_email']['type'] = 'email';
        }

		return apply_filters( 'wcbcf_billing_fields', $new_fields );
	}

	/**
	 * Update address fields priority.
	 *
	 * @param array $locales Default WooCommerce locales.
	 * @return array
	 */
	public function address_fields_priority(array $locales ): array
    {
		$locales['BR'] = array(
			'postcode' => array(
				'priority' => 45,
			),
		);

		return $locales;
	}


	/**
	 * Custom country address formats.
	 *
	 * @param array $formats Defaul formats.
	 *
	 * @return array          New BR format.
	 */
	public function localisation_address_formats(array $formats ): array
    {
		$formats['BR'] = "{name}\n{address_1}, {number}\n{address_2}\n{neighborhood}\n{city}\n{state}\n{postcode}\n{country}";

		return $formats;
	}

	/**
	 * Custom country address format.
	 *
	 * @param array $replacements Default replacements.
	 * @param array $args         Arguments to replace.
	 *
	 * @return array               New replacements.
	 */
	public function formatted_address_replacements(array $replacements, array $args ): array
    {
		$args = wp_parse_args( $args, array(
			'number'       => '',
			'neighborhood' => '',
		) );

		$replacements['{number}']       = $args['number'];
		$replacements['{neighborhood}'] = $args['neighborhood'];

		return $replacements;
	}

	/**
	 * Custom order formatted billing address.
	 *
	 * @param array $address Default address.
	 * @param object $order   Order data.
	 *
	 * @return array           New address format.
	 */
	public function order_formatted_billing_address(array $address, object $order ): array
    {
		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_meta' ) ) {
			$address['number']       = $order->get_meta( '_billing_number' );
			$address['neighborhood'] = $order->get_meta( '_billing_neighborhood' );
		} else {
			$address['number']       = $order->billing_number;
			$address['neighborhood'] = $order->billing_neighborhood;
		}

		return $address;
	}

	/**
	 * Custom order formatted shipping address.
	 *
	 * @param array $address Default address.
	 * @param object $order   Order data.
	 *
	 * @return array           New address format.
	 */
	public function order_formatted_shipping_address(array $address, object $order ): array
    {
		if ( ! is_array( $address ) ) {
			return $address;
		}

		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_meta' ) ) {
			$address['number']       = $order->get_meta( '_shipping_number' );
			$address['neighborhood'] = $order->get_meta( '_shipping_neighborhood' );
		} else {
			$address['number']       = $order->shipping_number;
			$address['neighborhood'] = $order->shipping_neighborhood;
		}

		return $address;
	}

	/**
	 * Custom my address formatted address.
	 *
	 * @param array $address     Default address.
	 * @param int $customer_id Customer ID.
	 * @param string $name        Field name (billing or shipping).
	 *
	 * @return array               New address format.
	 */
	public function my_account_my_address_formatted_address(array $address, int $customer_id, string $name ): array
    {
		$address['number']       = get_user_meta( $customer_id, $name . '_number', true );
		$address['neighborhood'] = get_user_meta( $customer_id, $name . '_neighborhood', true );

		return $address;
	}

	/**
	 * Order address.
	 *
	 * @param array $address Address data.
	 * @param string $type    Address type.
	 * @param WC_Order $order   Order object.
	 * @return array
	 */
	public function order_address(array $address, string $type, WC_Order $order ): array
    {
		$number       = $type . '_number';
		$neighborhood = $type . '_neighborhood';

		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_meta' ) ) {
			$address['number']       = $order->get_meta( '_' . $number );
			$address['neighborhood'] = $order->get_meta( '_' . $neighborhood );
		} else {
			$address['number']       = $order->$number;
			$address['neighborhood'] = $order->$neighborhood;
		}

		return $address;
	}
}

new Appmax_Payments_Checkout_Customer_Frontend();
