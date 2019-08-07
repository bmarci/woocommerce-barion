<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WC_Gateway_Barion_Order_Helper A class to wrap some common order functions.
 */
class WC_Gateway_Barion_Order_Wrapper
{

    private $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function filter_orders_to_pay($element)
    {
        return $element->get_status() == 'pending'; // TODO: check dates
    }

    /**
     * @param WC_Subscription $order
     * @return array|filter_orders_to_pay
     */
    public function get_orders_to_pay()
    {
        return class_exists('WC_Subscriptions_Order')
            ? array_filter($this->order->get_related_orders('all', 'renewal', 'resubscribe'), 'self::filter_orders_to_pay')
            : array();
    }

    /**
     * @param WC_Subscription $order
     * @return bool
     */
    public function is_payment_method_barion()
    {
        return $this->order->get_payment_method() == 'barion';
    }

    /**
     * @param $order_id
     * @return bool
     */
    public function is_resubscribe()
    {
        return self::is_subscription($this->order->get_id()) && wcs_order_contains_resubscribe($this->order->get_id());
    }

    /**
     * @param $order_id
     * @return bool True if the order contains a subscription.
     */
    public function is_subscription()
    {
        return class_exists('WC_Subscriptions_Order')
            ? WC_Subscriptions_Order::order_contains_subscription($this->order->get_id()) : false;
    }

    /**
     * @param WC_Order $order
     * @param $token_string
     */
    public function update_order_token($token_string)
    {
        $this->order->update_meta_data('barion_order_token', $token_string);
        $this->order->save();
    }

    /**
     * @param $order_id
     * @return mixed
     */
    public function get_initial_subscription_order()
    {
        return self::get_first_element_of_array(wcs_get_subscriptions_for_resubscribe_order($this->order->get_id()))->get_parent(); // It is not a zero index array...
    }

    /**
     * @param $order_id
     * @return mixed
     */
    public function get_first_element_of_array($array)
    {
        return array_pop(array_reverse($array));
    }

    public function get_subscription_token()
    {
        return class_exists('WC_Subscriptions_Order')
            ?   ($this->order->get_parent()->get_meta('barion_order_token') ?: '')
            : '';
    }

}
