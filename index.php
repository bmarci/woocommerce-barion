<?php
/*
Plugin Name: Barion Payment Gateway for WooCommerce and WooCommerce Subscription
Plugin URI: https://github.com/bmarci/woocommerce-barion
Description: Adds the ability to WooCommerce to pay via Barion
Version: 2.6.2
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
    $order = new WC_Subscription($subscription_id); // TODO: code smell
    $order_wrapper = new WC_Gateway_Barion_Order_Wrapper($order); // TODO: code smell

    if (!$order_wrapper->is_payment_method_barion()) {
        return;
    }

    $orders_to_pay = $order_wrapper->get_orders_to_pay();
    $token = $order_wrapper->get_subscription_token();
    $model_creator =  new WC_Gateway_Barion_Model_Creator_Subscription();
    $model_creator->set_token($token);
    $instance = new WC_Gateway_Barion(); // TODO: code smell

    foreach ($orders_to_pay as $order_to_pay) {
        $request = new WC_Gateway_Barion_Request($instance->barion_client, $instance, $model_creator); // TODO: code smell
        WC_Gateway_Barion_Payment_Processor::process_payment($request, $order_to_pay);
    }

}
add_action('woocommerce_scheduled_subscription_payment', 'wcs_barion_scheduled_subscription');



