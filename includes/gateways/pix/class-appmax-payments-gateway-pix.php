<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Appmax_Payments_Gateway_Pix class.
 *
 * @extends Appmax_Payments_Gateway
 */
class Appmax_Payments_Gateway_Pix extends Appmax_Payments_Gateway
{
    /**
     * @var string
     */
    public string $api_key;

    /**
     * @var string
     */
    public string $order_call_center;

    /**
     * @var string
     */
    public string $debug;

    /**
     * @var WC_Logger
     */
    public WC_Logger $log;

    /**
     * @var Appmax_Payments_Process_Payment_Pix
     */
    private Appmax_Payments_Process_Payment_Pix $process_payment;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->id = Appmax_Payments_Gateways::APPMAX_PIX;
        $this->has_fields = true;
        $this->method_title = __( 'Appmax - Pix', 'appmax' );
        $this->method_description = __( 'Plataforma de vendas online para produtores e afiliados.', 'appmax' );

        $this->supports = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->api_key = $this->get_option( 'api_key' );
        $this->order_call_center = $this->get_option( 'order_call_center' );
        $this->debug = $this->get_option( 'debug' );

        if ( $this->debug === 'yes' ) {
            $this->log = new WC_Logger();
        }

        $this->process_payment = new Appmax_Payments_Process_Payment_Pix( $this );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
    }

    /**
     * Check if the gateway is available to take payments.
     *
     * @return bool
     */
    public function is_available(): bool
    {
        return parent::is_available() && ! empty( $this->api_key );
    }

    public function frontend_scripts()
    {
        if ( is_checkout() ) {
            wp_enqueue_script('appmax-payments-pix');
        }
    }

    /**
     * Setting fields plugin.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Habilitar/Desabilitar', 'appmax' ),
                'type' => 'checkbox',
                'label' => __( 'Ativar Appmax - Pix', 'appmax' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Título', 'appmax' ),
                'type' => 'text',
                'description' => __( 'Título que irá aparecer no gateway no checkout da sua loja.', 'appmax' ),
                'desc_tip' => true,
                'default' => __( 'Appmax - Pix', 'appmax' ),
            ),
            'description' => array(
                'title' => __( 'Descrição', 'appmax' ),
                'type' => 'textarea',
                'description' => __( 'Descrição que irá aparecer no gateway no checkout da sua loja.', 'appmax' ),
                'desc_tip' => true,
                'default' => __( 'Pagamento com Pix', 'appmax' ),
            ),
            'settings' => array(
                'title' => __( 'Configurações', 'appmax' ),
                'type' => 'title',
            ),
            'api_key' => array(
                'title' => __( 'Appmax API Key', 'appmax' ),
                'type' => 'text',
                'description' => __( 'Por favor digite sua chave de API da APPMAX. Esta chave é necessária para processar os pagamentos e notificações.', 'appmax' ),
                'default' => '',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
            ),
            'order_call_center' => array(
                'title' => __( 'Receber Pedidos de CallCenter', 'appmax' ),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'OrderIntegrated',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
                'options' => array(
                    'OrderIntegrated' => 'Quando estiver integrado',
                    'OrderPaid' => 'Quando estiver pago',
                ),
            ),
            'debug' => array(
                'title' => __( 'Debug Log', 'appmax' ),
                'type' => 'checkbox',
                'label' => __( 'Habilitar log', 'appmax' ),
                'default' => 'yes',
                'description' => sprintf( __( 'Log Appmax - Pix. Você pode verificar o log em %s', 'appmax' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'appmax' ) . '</a>' ),
            ),
        );
    }

    /**
     * Payment fields plugin.
     */
    public function payment_fields()
    {
        if ( $this->get_description() ) {
            echo wp_kses_post( wpautop( wptexturize( $this->get_description() ) ) );
        }

        wp_enqueue_script('appmax-payments-pix');

        Appmax_Payments_Helper::get_template( 'views/checkout/pix/form-appmax.php' );
    }

    /**
     * @return bool
     */
    public function validate_fields(): bool
    {
        foreach ($_POST as $key => $item) {
            $_POST[$key] = Appmax_Payments_Helper::clear_input( $item );
        }

        Appmax_Payments_Helper::unset_variables_post( Appmax_Payments_Rule_Validation_Boleto::fields(), $_POST );
        Appmax_Payments_Helper::unset_variables_post( Appmax_Payments_Rule_Validation_Credit_Card::fields(), $_POST );

        $validator = new Appmax_Payments_Rule_Validation_Pix( $_POST );
        $validator->check_fields();

        if ( ! $validator->has_fails() ) {
            return true;
        }

        wc_add_notice( $validator->first_fail()['message'], 'error' );
        return false;
    }

    /**
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ): array
    {
        return $this->process_payment->process_payment( $order_id );
    }

    /**
     * @return bool
     */
    public function enable_debug(): bool
    {
        return $this->debug === 'yes';
    }

    /**
     * @param $message
     */
    public function add_log( $message )
    {
        $this->log->add( Appmax_Payments_Helper::replace( "_", "-", $this->id), $message );
    }
}