<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


require_once ('class-wc-gateway-barion.php');
require_once 'barion-library/library/BarionClient.php';
require_once 'includes/class-wc-gateway-barion-ipn-handler.php';
require_once 'includes/class-wc-gateway-barion-return-from-payment.php';
require_once('includes/class-wc-gateway-barion-request.php');

class WC_Gateway_Barion_Subscription extends WC_Gateway_Barion {

    public function __construct() {
        parent::__construct();
        $this->id = 'barion_subscription';
        $this->supports = array_merge($this->supports,
            array('subscriptions', 'subscription_suspension', 'subscription_reactivation', 'tokenization'));
    }

    static function log($message) {
        $date = new DateTime();
        $date = $date->format("r");
        error_log($date.': '.$message."\n", 3, "/Users/martonblum/off/log/wcs_barion_fork_info2.log");
    }

    function process_payment($order_id) {
        return parent::process_payment($order_id, true);
    }

}

