<?php
/*
Plugin Name: Barion Payment Gateway for WooCommerce and WooCommerce Subscription
Plugin URI: https://github.com/bmarci/woocommerce-barion
Description: Adds the ability to WooCommerce to pay via Barion
Version: 2.6.0
Author: <a href="http://szelpeter.hu">Peter Szel</a>, <a href="https://blummarci.com">Marton Blum</a>
Author URI: http://szelpeter.hu
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Text Domain: pay-via-barion-for-woocommerce
Domain Path: /languages

*/

add_action('plugins_loaded', 'woocommerce_gateway_barion_init', 0);


function woocommerce_gateway_barion_init() {
    if (!class_exists('WC_Payment_Gateway'))
        return;

    load_plugin_textdomain('pay-via-barion-for-woocommerce', false, plugin_basename(dirname(__FILE__)) . "/languages");

    init_gateway();

}

function init_gateway() {
    require_once('class-wc-gateway-barion.php');
    require_once 'includes/class-wc-gateway-barion-payment-processor.php';
    require_once 'includes/class-wc-gateway-barion-order-helper.php';

    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_gateway_barion_gateway($methods) {
        $methods[] = 'WC_Gateway_Barion';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_barion_gateway' );
}

function wcs_barion_scheduled_subscription($subscription_id) { // TODO: refactor me
    $order = new WC_Subscription($subscription_id);

    WC_Gateway_Barion::log('b1');

    if (!WC_Gateway_Barion_Order_Helper::is_payment_method_barion($order)) {
        return;
    }

    WC_Gateway_Barion::log('b2');


    $instance = new WC_Gateway_Barion();
    $orders_to_pay = WC_Gateway_Barion_Order_Helper::get_orders_to_pay($order);
    WC_Gateway_Barion::log('b3');

    $token = $order->get_parent()->get_meta('barion_order_token');
    WC_Gateway_Barion::log('b4 token: '.$token);

    foreach ($orders_to_pay as $order_to_pay) {
        $request = new WC_Gateway_Barion_Request($instance->barion_client, $instance);
        WC_Gateway_Barion_Payment_Processor::process_payment($request, $order_to_pay, $token, false);
    }
    WC_Gateway_Barion::log('b5');

}
add_action('woocommerce_scheduled_subscription_payment', 'wcs_barion_scheduled_subscription');



