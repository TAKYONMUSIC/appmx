<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Events
{
    const ORDER_APPROVED = 'OrderApproved';
    const ORDER_AUTHORIZED = 'OrderAuthorized';
    const ORDER_AUTHORIZED_DELAY = 'OrderAuthorizedWithDelay';
    const ORDER_BILLET_CREATED = 'OrderBilletCreated';
    const ORDER_BILLET_OVERDUE = 'OrderBilletOverdue';
    const ORDER_INTEGRATED = 'OrderIntegrated';
    const ORDER_PAID = 'OrderPaid';
    const ORDER_PENDING_INTEGRATION = 'OrderPendingIntegration';
    const ORDER_REFUND = 'OrderRefund';
    const PAYMENT_NOT_AUTHORIZED = 'PaymentNotAuthorized';
    const PAYMENT_NOT_AUTHORIZED_WITH_DELAY = 'PaymentNotAuthorizedWithDelay';
}