<?php
/**
 * Plugin Name: Appmax
 * Description: Maximize suas vendas com as opções de pagamento da Appmax.
 * Version: 1.0.3
 * License: GPLv2 or later
 * Author: Appmax
 * Author URI: https://appmax.com.br
 * Text Domain: appmax
 * Requires PHP: 8.0
 *
 * @package Appmax-Payments
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'APPMAX_PAYMENTS_PLUGIN_NAME' ) ) {
    define( 'APPMAX_PAYMENTS_PLUGIN_NAME', __FILE__ );
}

if ( ! class_exists( 'Appmax_Payments' ) ) :

    /**
     * Main Appmax_Payments Class.
     *
     * @class Appmax_Payments
     */
    class Appmax_Payments
    {
        const VERSION = '1.0.3';

        /**
         * @var null
         */
        protected static $_instance = null;

        /**
         * @return Appmax_Payments|null
         */
        public static function instance(): ?Appmax_Payments
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Appmax_Payments constructor.
         * @throws Exception
         */
        public function __construct()
        {
            if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
                $this->deactivate_plugin( plugin_basename( __FILE__ ) );
                add_action( 'admin_notices', array( $this, 'php_not_supported' ) );
                return;
            }

            if ( ! extension_loaded('calendar') ) {
                $this->deactivate_plugin( plugin_basename( __FILE__ ) );
                add_action( 'admin_notices', array( $this, 'lib_calendar_not_installed' ) );
                return;
            }

            if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
                $this->deactivate_plugin( plugin_basename( __FILE__ ) );
                add_action( 'admin_notices', array( $this, 'woocommerce_not_installed' ) );
                return;
            }

            $this->define_constants();
            $this->includes();

            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

            add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
            add_filter( 'woocommerce_checkout_fields', array( $this, 'remove_checkout_fields' ) );

            $this->dispatch_tracking_code();

            add_action( 'wp_ajax_update_order_status', array($this, 'update_order_status'));
            add_action( 'wp_ajax_nopriv_update_order_status', array($this, 'update_order_status'));

            add_action( 'woocommerce_order_details_after_order_table', array( $this, 'show_link_billet' ) );
            add_action( 'woocommerce_order_details_after_order_table', array( $this, 'show_pix_qrcode' ) );
        }

        /**
         * Include php-not-supported.php
         */
        public function php_not_supported()
        {
            include dirname( __FILE__ ) . '/templates/views/admin/php-not-supported.php';
        }

        /**
         * Include lib-calendar-not-installed.php
         */
        public function lib_calendar_not_installed()
        {
            include dirname( __FILE__ ) . '/templates/views/admin/lib-calendar-not-installed.php';
        }

        /**
         * Include woocommerce-not-installed.php
         */
        public function woocommerce_not_installed()
        {
            include dirname( __FILE__ ) . '/templates/views/admin/woocommerce-not-installed.php';
        }

        /**
         * @param $plugin
         * @return bool
         */
        public function deactivate_plugin( $plugin ): bool
        {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

            deactivate_plugins( $plugin );

            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }

            return true;
        }

        /**
         * Define Constants of Gateway
         */
        private function define_constants()
        {
            $this->define( 'APPMAX_PAYMENTS_URL_API_DOMAIN', getenv('URL_API_DOMAIN') );
            $this->define( 'APPMAX_PAYMENTS_URL_API_HOST', getenv('URL_API_HOST') );
            $this->define( 'APPMAX_PAYMENTS_DUE_DAYS', 3 );
            $this->define( 'APPMAX_PAYMENTS_KEY', 'c8db72fbdb177a2a4a8a' );
            $this->define( 'APPMAX_PAYMENTS_CLUSTER', 'us2' );
        }

        /**
         * Define constants
         *
         * @param $name
         * @param $value
         */
        private function define( $name, $value )
        {
            if ( ! defined( $name ) ) {
                define( $name, $value );
            }
        }

        /**
         * Includes
         */
        private function includes()
        {
            /**
             * Core
             */
            include_once dirname(__FILE__) . '/includes/core/checkout/class-appmax-payments-checkout-customer-api.php';
            include_once dirname(__FILE__) . '/includes/core/checkout/class-appmax-payments-checkout-customer-frontend.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-api.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-calculate.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-cart-products.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-check-response.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-due-date-generator.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-form-payment.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-helper.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-interest.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-post-information.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-scripts.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-tax.php';
            include_once dirname(__FILE__) . '/includes/core/class-appmax-payments-webhook.php';

            /**
             * Exceptions
             */
            include_once dirname(__FILE__) . '/includes/exceptions/class-appmax-payments-connection-gateway-exception.php';
            include_once dirname(__FILE__) . '/includes/exceptions/class-appmax-payments-invalid-access-token-exception.php';
            include_once dirname(__FILE__) . '/includes/exceptions/class-appmax-payments-server-exception.php';
            include_once dirname(__FILE__) . '/includes/exceptions/class-appmax-payments-undefined-product-sku-exception.php';

            /**
             * Gateways
             */
            include_once dirname(__FILE__) . '/includes/gateways/class-appmax-payments-process-payment.php';
            include_once dirname(__FILE__) . '/includes/gateways/class-appmax-payments-gateway.php';
            include_once dirname(__FILE__) . '/includes/gateways/boleto/class-appmax-payments-gateway-billet.php';
            include_once dirname(__FILE__) . '/includes/gateways/boleto/class-appmax-payments-process-payment-billet.php';
            include_once dirname(__FILE__) . '/includes/gateways/credit-card/class-appmax-payments-gateway-credit-card.php';
            include_once dirname(__FILE__) . '/includes/gateways/credit-card/class-appmax-payments-process-payment-credit-card.php';
            include_once dirname(__FILE__) . '/includes/gateways/pix/class-appmax-payments-gateway-pix.php';
            include_once dirname(__FILE__) . '/includes/gateways/pix/class-appmax-payments-process-payment-pix.php';

            /**
             * Support
             */
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-day-week.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-errors-api.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-events.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-gateways.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-order-status.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-origin-order.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-payment-type.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-status-appmax.php';
            include_once dirname(__FILE__) . '/includes/support/class-appmax-payments-endpoints-api.php';

            /**
             * Tracking Code
             */
            include_once dirname(__FILE__) . '/includes/tracking-code/class-appmax-payments-tracking-code.php';
            include_once dirname(__FILE__) . '/includes/tracking-code/class-appmax-payments-tracking-code-contract.php';
            include_once dirname(__FILE__) . '/includes/tracking-code/types/class-appmax-payments-correios-send-appmax.php';
            include_once dirname(__FILE__) . '/includes/tracking-code/types/class-appmax-payments-send-appmax.php';

            /**
             * Validation
             */
            include_once dirname(__FILE__) . '/includes/validation/rules/class-appmax-payments-rule.php';
            include_once dirname(__FILE__) . '/includes/validation/rules/class-appmax-payments-rule-contract.php';
            include_once dirname(__FILE__) . '/includes/validation/rules/types/class-appmax-payments-card-expiry.php';
            include_once dirname(__FILE__) . '/includes/validation/rules/types/class-appmax-payments-card-number.php';
            include_once dirname(__FILE__) . '/includes/validation/rules/types/class-appmax-payments-cpf.php';
            include_once dirname(__FILE__) . '/includes/validation/rules/types/class-appmax-payments-cvv.php';
            include_once dirname(__FILE__) . '/includes/validation/rules/types/class-appmax-payments-number.php';
            include_once dirname(__FILE__) . '/includes/validation/rules/types/class-appmax-payments-required.php';
            include_once dirname(__FILE__) . '/includes/validation/class-appmax-payments-validator.php';
            include_once dirname(__FILE__) . '/includes/validation/class-appmax-payments-rule-validation-boleto.php';
            include_once dirname(__FILE__) . '/includes/validation/class-appmax-payments-rule-validation-credit-card.php';
            include_once dirname(__FILE__) . '/includes/validation/class-appmax-payments-rule-validation-pix.php';

            /**
             * Webhooks
             */
            include_once dirname(__FILE__) . '/includes/webhooks/class-appmax-payments-webhook-events.php';
            include_once dirname(__FILE__) . '/includes/webhooks/class-appmax-payments-webhook-events-contract.php';
            include_once dirname(__FILE__) . '/includes/webhooks/types/class-appmax-payments-order-billet-overdue.php';
            include_once dirname(__FILE__) . '/includes/webhooks/types/class-appmax-payments-order-integrated.php';
            include_once dirname(__FILE__) . '/includes/webhooks/types/class-appmax-payments-order-paid.php';
            include_once dirname(__FILE__) . '/includes/webhooks/types/class-appmax-payments-order-refund.php';
            include_once dirname(__FILE__) . '/includes/webhooks/types/class-appmax-payments-payment-not-authorized.php';
        }


        /**
         * @param $links
         * @return array
         */
        public function plugin_action_links( $links ): array
        {
            $plugin_links = array();
            $plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '">' . __( 'Configurações', 'appmax' ) . '</a>';
            $plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=appmax_boleto' ) ) . '">' . __( 'Boleto', 'appmax' ) . '</a>';
            $plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=appmax_credit_card' ) ) . '">' . __( 'Cartão de Crédito', 'appmax' ) . '</a>';
            $plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=appmax_pix' ) ) . '">' . __( 'Pix', 'appmax' ) . '</a>';
            return array_merge( $plugin_links, $links );
        }

        /**
         * update_order_status
         */
        public function update_order_status()
        {
            if ( isset($_POST['order_id']) && is_int($_POST['order_id']) && $_POST['order_id'] > 0 ) {
                $order = wc_get_order($_POST['order_id']);
                $order->update_status( Appmax_Payments_Order_Status::COMPLETED );
                die();
            }
        }

        /**
         * @throws Exception
         */
        public function dispatch_tracking_code()
        {
            global $wpdb;

            $api_key = '';

            foreach (Appmax_Payments_Payment_Type::all() as $gateway) {
                $gateway_id = match ( $gateway ) {
                    Appmax_Payments_Payment_Type::BILLET => Appmax_Payments_Gateways::APPMAX_BOLETO,
                    Appmax_Payments_Payment_Type::CREDIT_CARD => Appmax_Payments_Gateways::APPMAX_CREDIT_CARD,
                    Appmax_Payments_Payment_Type::PIX => Appmax_Payments_Gateways::APPMAX_PIX
                };

                $option_name = "woocommerce_{$gateway_id}_settings";

                $result = $wpdb->get_row(
                    $wpdb->prepare( "select option_value from {$wpdb->options} where `option_name` = %s limit 1" , $option_name)
                );

                $gateway_captured = unserialize( $result->option_value );

                if ('no' === $gateway_captured['enabled']) {
                    continue;
                }

                $api_key = $gateway_captured['api_key'];
                break;
            }

            add_action( 'wp_ajax_add-meta', fn() => (new Appmax_Payments_Send_Appmax( $api_key ))->send_tracking_code(), 1 );

            if ( class_exists( 'WC_Correios' ) ) {
                add_action( 'wp_ajax_woocommerce_correios_add_tracking_code', fn() => (new Appmax_Payments_Correios_Send_Appmax( $api_key ))->send_tracking_code(), 1);
            }
        }

        /**
         * Add the gateway to WooCommerce.
         *
         * @param array $methods
         * @return array
         */
        public function register_gateway( array $methods ): array
        {
            $methods[] = 'Appmax_Payments_Gateway_Credit_Card';
            $methods[] = 'Appmax_Payments_Gateway_Billet';
            $methods[] = 'Appmax_Payments_Gateway_Pix';
            return $methods;
        }

        /**
         * Remove some fields
         *
         * @param $fields
         * @return mixed
         */
        public function remove_checkout_fields( $fields ): mixed
        {
            unset( $fields['billing']['billing_company'] );
            unset( $fields['shipping']['shipping_company'] );
            unset( $fields['order']['order_comments'] );
            return $fields;
        }

        /**
         * Add the billet link when the payment is billet
         *
         * @param $order
         */
        public function show_link_billet( $order )
        {
            if ( $order->get_meta( '_appmax_type_payment' ) == Appmax_Payments_Payment_Type::BILLET ) {
                $html = "<a href='%s' target='_blank' class='button-view-boleto button-test'>Exibir Boleto</a>";

                if ($order->get_meta('_appmax_digitable_line')) {
                    $html .= "<p><strong>Linha digitável: </strong> %s</p>";
                }

                echo sprintf( $html, esc_url($order->get_meta( 'appmax_link_billet' )), $order->get_meta('_appmax_digitable_line') );
            }
        }

        /**
         * Add pix template when the payment is by pix
         *
         * @param $order
         */
        public function show_pix_qrcode( $order )
        {
            if ( $order->get_meta( '_appmax_type_payment' ) == Appmax_Payments_Payment_Type::PIX &&
                $order->get_meta('_appmax_transaction_data')
            ) {
                include dirname( __FILE__ ) . '/templates/views/checkout/pix/pix-payment.php';
            }
        }

        /**
         * Get templates path.
         *
         * @return string
         */
        public static function get_templates_path(): string
        {
            return plugin_dir_path( __FILE__ ) . 'templates/';
        }
    }

    add_action( 'plugins_loaded', array( 'Appmax_Payments', 'instance' ) );

endif;
