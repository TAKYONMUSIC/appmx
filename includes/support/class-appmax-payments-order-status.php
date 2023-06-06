<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Appmax_Payments_Order_Status
{
    const PENDING = 'pending';
    const FAILED = 'failed';
    const PROCESSING = 'processing';
    const COMPLETED = 'completed';
    const ON_HOLD = 'on-hold';
    const CANCELLED = 'cancelled';
    const REFUNDED = 'refunded';
}