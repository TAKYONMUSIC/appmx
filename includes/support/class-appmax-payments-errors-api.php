<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Errors_Api
{
    const MESSAGE_001 = "Solicite a liberação de  IP.";
    const MESSAGE_002 = "Erro interno. Para mais informações entre em contato.";
    const MESSAGE_003 = "Há um problema de conexão com o gateway de pagamento. Desculpe pela inconveniência.";
    const MESSAGE_004 = "Chave de API inválida. Contate o gateway de pagamento.";

    const MESSAGE_ERROR_CUSTOMER = "Erro no processamento de informações do cliente.";
    const MESSAGE_ERROR_ORDER = "Erro no processamento de informações do pedido.";
    const MESSAGE_ERROR_PAYMENT = "Erro no processamento de informações do pagamento.";
    const MESSAGE_ERROR_TRACKING = "Erro no processamento de informações do tracking.";

    const INVALID_ACCESS_TOKEN = "Invalid Access Token";
    const VALIDATE_REQUEST = "The given data failed to pass validation.";
}