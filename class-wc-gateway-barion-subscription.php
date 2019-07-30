<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'barion-library/library/BarionClient.php';
require_once 'includes/class-wc-gateway-barion-ipn-handler.php';
require_once 'includes/class-wc-gateway-barion-return-from-payment.php';
require_once('includes/class-wc-gateway-barion-request.php');

class WC_Gateway_Barion_Subscription extends WC_Gateway_Barion {

    public function __construct() {
        $this->supports = array(
            'products',
            'refunds',
            'subscriptions'
        );
    }
}
