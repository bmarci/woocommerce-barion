<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WC_Gateway_Barion_Payment_Processor A helper class to make the Barion payment which can be done from various places
 */
class WC_Gateway_Barion_Payment_Processor
{

    private function __construct()
    {
    }

    public static function process_payment($request, $order)
    {
        $request->prepare_payment($order);

        if (!$request->is_prepared) {
            return array(
                'result' => 'failure'
            );
        }

        $redirectUrl = $request->get_redirect_url();
        $order->add_order_note(__('User redirected to the Barion payment page.', 'pay-via-barion-for-woocommerce') . ' redirectUrl: "' . $redirectUrl . '"');

        return array(
            'result' => 'success',
            'redirect' => $redirectUrl
        );
    }
}
