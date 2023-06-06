<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Appmax_Payments_Gateway_Credit_Card class.
 *
 * @extends Appmax_Payments_Gateway
 */
class Appmax_Payments_Gateway_Credit_Card extends Appmax_Payments_Gateway
{
    /**
     * @var string
     */
    public string $api_key;

    /**
     * @var int
     */
    public int $installments_credit_card;

    /**
     * @var object
     */
    public object $settings_interest;

    /**
     * @var string
     */
    public string $show_total_installments;

    /**
     * @var string
     */
    public string $order_call_center;

    /**
     * @var string
     */
    public string $order_authorized;

    /**
     * @var string
     */
    public string $status_order_created;

    /**
     * @var string
     */
    public string $debug;

    /**
     * @var WC_Logger
     */
    public WC_Logger $log;

    /**
     * @var Appmax_Payments_Process_Payment_Credit_Card
     */
    private Appmax_Payments_Process_Payment_Credit_Card $process_payment;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->id = Appmax_Payments_Gateways::APPMAX_CREDIT_CARD;
        $this->has_fields = true;
        $this->method_title = __( 'Appmax - Cartão de Crédito', 'appmax' );
        $this->method_description = __( 'Plataforma de vendas online para produtores e afiliados.', 'appmax' );

        $this->supports = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->api_key = $this->get_option( 'api_key' );
        $this->installments_credit_card = $this->get_option( 'installments_credit_card' );
        $this->show_total_installments = $this->get_option( 'show_total_installments' );
        $this->order_call_center = $this->get_option( 'order_call_center' );
        $this->order_authorized = $this->get_option( 'order_authorized' );
        $this->status_order_created = $this->get_option( 'status_order_created' );
        $this->debug = $this->get_option( 'debug' );

        if ( $this->debug === 'yes' ) {
            $this->log = new WC_Logger();
        }

        $this->process_payment = new Appmax_Payments_Process_Payment_Credit_Card( $this );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
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

    /**
     * Setting fields plugin.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Habilitar/Desabilitar', 'appmax' ),
                'type' => 'checkbox',
                'label' => __( 'Ativar Appmax - Cartão de Crédito ', 'appmax' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Título', 'appmax' ),
                'type' => 'text',
                'description' => __( 'Título que irá aparecer no gateway no checkout da sua loja.', 'appmax' ),
                'desc_tip' => true,
                'default' => __( 'Appmax - Cartão de Crédito', 'appmax' ),
            ),
            'description' => array(
                'title' => __( 'Descrição', 'appmax' ),
                'type' => 'textarea',
                'description' => __( 'Descrição que irá aparecer no gateway no checkout da sua loja.', 'appmax' ),
                'desc_tip' => true,
                'default' => __( 'Pagamento com cartão de crédito', 'appmax' ),
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
            'installments_credit_card' => array(
                'title' => __( 'Número de parcelas', 'appmax' ),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => __( 'Selecione o número de parcelas.', 'appmax' ),
                'default' => 1,
                'custom_attributes' => array(
                    'required' => 'required',
                ),
                'options' => array(
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                    6 => 6,
                    7 => 7,
                    8 => 8,
                    9 => 9,
                    10 => 10,
                    11 => 11,
                    12 => 12,
                ),
            ),
            'show_total_installments' => array(
                'title' => __( 'Exibir total na parcela', 'appmax' ),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 0,
                'custom_attributes' => array(
                    'required' => 'required',
                ),
                'options' => array(
                    0 => 'Não',
                    1 => 'Sim',
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
            'order_authorized' => array(
                'title' => __( 'Status dos pedidos em análise antifraude', 'appmax' ),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => __( 'Status dos pedidos no WooCommerce quando o pedido se encontra em análise de fraude na Appmax.', 'appmax' ),
                'desc_tip' => true,
                'default' => 'processing',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
                'options' => array(
                    'processing' => 'Em processamento',
                    'on-hold' => 'Aguardando',
                ),
            ),
            'status_order_created' => array(
                'title' => __( 'Criar pedido na loja com status', 'appmax' ),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => 'processing',
                'custom_attributes' => array(
                    'required' => 'required',
                ),
                'options' => array(
                    'processing' => 'Em processamento',
                    'pending' => 'Pagamento pendente',
                ),
            ),
            'debug' => array(
                'title' => __( 'Debug Log', 'appmax' ),
                'type' => 'checkbox',
                'label' => __( 'Habilitar log', 'appmax' ),
                'default' => 'yes',
                'description' => sprintf( __( 'Log Appmax - Cartão de Crédito. Você pode verificar o log em %s', 'appmax' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'appmax' ) . '</a>' ),
            ),
        );
    }

    /**
     * Payment fields plugin.
     * @throws Exception
     */
    public function payment_fields()
    {
        if ( $this->get_description() ) {
            echo wp_kses_post( wpautop( wptexturize( $this->get_description() ) ) );
        }

        wp_enqueue_script('wc-credit-card-form');
        wp_enqueue_script('appmax-payments-credit-card');

        try {

            $this->settings_interest = $this->get_settings_interest()->data;

        } catch (Exception $e) {

            $settings_interest = new stdClass();
            $settings_interest->cc_interest = 2.99;
            $settings_interest->settings = null;

            $this->settings_interest = $settings_interest;
        }

        $args = array(
            'installments' => Appmax_Payments_Form_Payment::display_installments( array(
                'installments' => $this->installments_credit_card,
                'settings_interest' => $this->settings_interest,
                'show_total_installments' => $this->show_total_installments
            ) )
        );

        Appmax_Payments_Helper::get_template( 'views/checkout/credit-card/form-appmax.php', $args );
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
        Appmax_Payments_Helper::unset_variables_post( Appmax_Payments_Rule_Validation_Pix::fields(), $_POST );

        $validator = new Appmax_Payments_Rule_Validation_Credit_Card( $_POST );
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
     * @throws Exception
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
