<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WC_Gateway_Barion_Order_Helper A helper class to wrap some order functions.
 */
class WC_Gateway_Barion_Order_Helper {

    private function __construct()
    {
    }

    public static function filter_orders_to_pay($element) {
        return $element->get_status() == 'pending'; // TODO: check dates
    }

    /**
     * @param WC_Subscription $order
     * @return array|filter_orders_to_pay
     */
    public static function get_orders_to_pay($order)
    {
        return class_exists('WC_Subscriptions_Order')
            ? array_filter($order->get_related_orders('all', 'renewal', 'resubscribe'), 'self::filter_orders_to_pay')
            : array();
    }

    /**
     * @param WC_Subscription $order
     * @return bool
     */
    public static function is_payment_method_barion($order)
    {
        return $order->get_payment_method() == 'barion';
    }

    /**
     * @param $order_id
     * @return bool True if the order contains a subscription.
     */
    public static function is_subscription($order_id)
    {
        return class_exists('WC_Subscriptions_Order')
            ? WC_Subscriptions_Order::order_contains_subscription($order_id) : false;
    }


    /**
     * @param $order_id
     * @return bool
     */
    public static function isResubscribe($order_id)
    {
        return self::is_subscription($order_id) && wcs_order_contains_resubscribe($order_id);
    }

    /**
     * @param $order_id
     * @return mixed
     */
    public static function getFirstElementOfArray($array)
    {
        return array_pop(array_reverse($array));
    }

    /**
     * @param WC_Order $order
     * @param $token_string
     */
    public static function updateOrderToken($order, $token_string)
    {
        $order->update_meta_data('barion_order_token', $token_string);
        $order->save();
    }

    /**
     * @param $order_id
     * @return mixed
     */
    public static function getInitialSubscriptionOrder($order_id)
    {
        return self::getFirstElementOfArray(wcs_get_subscriptions_for_resubscribe_order($order_id))->get_parent(); // It is not a zero index array...
    }

}
