<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once 'class-wc-gateway-barion-model-creator.php';
require_once 'class-wc-gateway-barion-order-wrapper.php';
require_once 'class-wc-gateway-barion-token.php';


class WC_Gateway_Barion_Model_Creator_Subscription extends WC_Gateway_Barion_Model_Creator
{
    protected $token;

    public function create_payment_request_model($order, $transaction)
    {
        $paymentRequest = parent::create_payment_request_model($order, $transaction);

        $order_wrapper = new WC_Gateway_Barion_Order_Wrapper($order); // TODO: code smell
        $register_token = $order_wrapper->is_subscription();

        if ($register_token) {
            $this->token = WC_Gateway_Barion_Token::generate_token();
            $order_wrapper->update_order_token($this->token);
        }

        $paymentRequest->InitiateRecurrence = $register_token;
        $paymentRequest->RecurrenceId = $this->get_token();

        return $paymentRequest;
    }

    /**
     * @return mixed
     */
    public function get_token()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function set_token($token)
    {
        $this->token = $token;
    }


}

