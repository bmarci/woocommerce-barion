<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WC_Gateway_Barion_Payment_Processor A helper class to make the Barion payment which can be done from various places
 */
class WC_Gateway_Barion_Payment_Processor {

    private function __construct()
    {
    }

    public static function process_payment($request, $order, $token, $register_token)
    {
        WC_Gateway_Barion::log('8');
        $request->prepare_payment($order, $register_token, $token);
        WC_Gateway_Barion::log('9');

        if (!$request->is_prepared) {
            return array(
                'result' => 'failure'
            );
        }
        WC_Gateway_Barion::log('10');

        $redirectUrl = $request->get_redirect_url();

        WC_Gateway_Barion::log('11');


        $order->add_order_note(__('User redirected to the Barion payment page.', 'pay-via-barion-for-woocommerce') . ' redirectUrl: "' . $redirectUrl . '"');

        WC_Gateway_Barion::log('12');


        return array(
            'result' => 'success',
            'redirect' => $redirectUrl
        );
    }
}
