<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_Scripts
 */
class Appmax_Payments_Scripts
{
    /**
     * @var array
     */
    private static array $scripts = array();

    /**
     * @var array
     */
    private static array $localize_scripts = array();

    /**
     * Hook in methods.
     */
    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
        add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
        add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
    }

    /**
     * Register/queue frontend scripts.
     */
    public static function load_scripts()
    {
        self::register_scripts();
    }

    /**
     * Register all WC scripts.
     */
    private static function register_scripts()
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        $register_scripts = array(
            'jquery-mask' => array(
                'src' => self::get_asset_url( 'assets/js/jquery.mask' . $suffix . '.js' ),
                'deps' => array( 'jquery' ),
                'version' => '1.14.16'
            ),
            'pusher' => array(
                'src' => self::get_asset_url( 'assets/js/pusher' . $suffix . '.js' ),
                'deps' => array('jquery'),
                'version' => '7.0.3'
            ),
            'appmax-payments-credit-card' => array(
                'src' => self::get_asset_url( 'assets/js/my-scripts/appmax_payments_credit_card' . $suffix . '.js' ),
                'deps' => array( 'jquery', 'jquery-mask' ),
                'version' => Appmax_Payments::VERSION,
            ),
            'appmax-payments-boleto' => array(
                'src' => self::get_asset_url( 'assets/js/my-scripts/appmax_payments_boleto' . $suffix . '.js' ),
                'deps' => array( 'jquery', 'jquery-mask' ),
                'version' => Appmax_Payments::VERSION,
            ),
            'appmax-payments-pix' => array(
                'src' => self::get_asset_url( 'assets/js/my-scripts/appmax_payments_pix' . $suffix . '.js' ),
                'deps' => array( 'jquery', 'jquery-mask', 'pusher' ),
                'version' => Appmax_Payments::VERSION,
            ),
            'appmax-payments-fields-checkout-customer' => array(
                'src' => self::get_asset_url( 'assets/js/my-scripts/appmax_payments_fields_checkout_customer' . $suffix . '.js' ),
                'deps' => array( 'jquery', 'jquery-mask' ),
                'version' => Appmax_Payments::VERSION,
            ),
        );

        foreach ( $register_scripts as $name => $props ) {
            self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
        }
    }

    /**
     * @param string $path
     * @return mixed|void
     */
    private static function get_asset_url( string $path )
    {
        return apply_filters( 'woocommerce_get_asset_url', plugins_url( $path, APPMAX_PAYMENTS_PLUGIN_NAME ), $path );
    }

    /**
     * @param string $handle
     * @param string $path
     * @param string[] $deps
     * @param string $version
     * @param bool $in_footer
     */
    private static function register_script( string $handle, string $path, array $deps = array( 'jquery' ), string $version = APPMAX_PAYMENTS_PLUGIN_NAME, bool $in_footer = true )
    {
        self::$scripts[] = $handle;
        wp_register_script( $handle, $path, $deps, $version, $in_footer );
    }

    /**
     * localize_printed_scripts
     */
    public static function localize_printed_scripts()
    {
        foreach ( self::$scripts as $handle ) {
            self::localize_script( $handle );
        }
    }

    /**
     * @param string $handle
     */
    private static function localize_script( string $handle )
    {
        if ( ! in_array( $handle, self::$localize_scripts, true ) && wp_script_is( $handle ) ) {
            $data = self::get_script_data( $handle );

            if ( ! $data ) {
                return;
            }

            $name = str_replace( '-', '_', $handle ) . '_params';
            self::$localize_scripts[] = $handle;
            wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
        }
    }

    /**
     * @param string $handle
     * @return mixed|void
     */
    private static function get_script_data( string $handle )
    {
        $params = match ($handle)
        {
            'appmax-payments-credit-card' => array(
                'masks' => array(
                    'appmax-credit-card-form-card-cpf' => '999.999.999-99',
                    'appmax-credit-card-form-card-expiry' => '99/9999',
                )
            ),
            'appmax-payments-boleto' => array(
                'masks' => array(
                    'appmax-boleto-form-card-cpf' => '999.999.999-99'
                )
            ),
            'appmax-payments-pix' => array(
                'masks' => array(
                    'appmax-pix-form-card-cpf' => '999.999.999-99'
                ),
                'key' => APPMAX_PAYMENTS_KEY,
                'cluster' => APPMAX_PAYMENTS_CLUSTER,
                'copy_code' => __( 'Código copiado com sucesso!', 'appmax' ),
                'payment_confirmed' => __( 'Pagamento confirmado!', 'appmax' ),
                'expired_time' => __( 'Atingido o tempo limite para pagamento.', 'appmax' ),

            ),
            default => false,
        };

        $params = apply_filters_deprecated( $handle . '_params', array( $params ), '3.0.0', 'woocommerce_get_script_data' );

        return apply_filters( 'woocommerce_get_script_data', $params, $handle );
    }
}

Appmax_Payments_Scripts::init();