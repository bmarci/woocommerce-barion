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

    /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_gateway_barion_gateway($methods) {
        $methods[] = 'WC_Gateway_Barion';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_barion_gateway' );
}


function filter_orders_to_pay($element) {
    return $element->get_status() == 'pending'; // TODO: check dates
}

function wcs_barion_scheduled_subscription($subscription_id) { // TODO: refactor me
    $instance = new WC_Gateway_Barion();

    $order = new WC_Subscription($subscription_id);

    $related_orders = array_filter($order->get_related_orders( 'all', 'renewal' ), 'filter_orders_to_pay');

    $token = $order->get_parent()->get_meta('barion_order_token');

    foreach ($related_orders as $related_order) {
        $request = new WC_Gateway_Barion_Request($instance->barion_client, $instance);
        $request->prepare_payment($related_order, false, $token);
        $redirectUrl = $request->get_redirect_url();
        $related_order->add_order_note(__('User redirected to the Barion payment page.', 'pay-via-barion-for-woocommerce') . ' redirectUrl: "' . $redirectUrl . '"');
    }

}

add_action('woocommerce_scheduled_subscription_payment', 'wcs_barion_scheduled_subscription');

