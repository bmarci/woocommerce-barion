<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Barion_Model_Creator
{

    public function create_payment_request_model($order, $transaction)
    {
        $paymentRequest = new PreparePaymentRequestModel(); // Creating the model
        $paymentRequest->GuestCheckout = true;
        $paymentRequest->PaymentType = PaymentType::Immediate;
        $paymentRequest->FundingSources = array(FundingSourceType::All);
        $paymentRequest->PaymentRequestId = $order->get_id();
        $paymentRequest->PayerHint = $order->get_billing_email();
        $paymentRequest->Locale = $this->get_barion_locale();
        $paymentRequest->OrderNumber = $order->get_order_number();
        $paymentRequest->ShippingAddress = $order->get_formatted_shipping_address();
        $paymentRequest->RedirectUrl = add_query_arg('order-id', $order->get_id(), WC()->api_request_url('WC_Gateway_Barion_Return_From_Payment'));
        $paymentRequest->CallbackUrl = WC()->api_request_url('WC_Gateway_Barion');
        $paymentRequest->Currency = $order->get_currency();
        $paymentRequest->AddTransaction($transaction);
        return $paymentRequest;
    }

    protected function get_barion_locale()
    {
        switch (get_locale()) {
            case "hu_HU":
                return UILocale::HU;
            case "de_DE":
                return UILocale::DE;
            case "sl_SI":
                // This doesn't work due to a bug in the Barion library
                //return UILocale::SL;
                return "sl-SI";
            case "sk_SK":
                return UILocale::SK;
            case "fr_FR":
                return UILocale::FR;
            case "cs_CZ":
                return UILocale::CZ;
            default:
                return UILocale::EN;
        }
    }

    public function create_transaction_model($order, $payee)
    {
        $transaction = new PaymentTransactionModel();
        $transaction->POSTransactionId = $order->get_id();
        $transaction->Payee = $payee;
        $transaction->Total = $this->round($order->get_total(), $order->get_currency());
        $transaction->Comment = "";
        return $transaction;
    }

    /**
     * Round prices.
     * @param string $price
     * @param string $currency
     * @return string
     */
    protected function round($price, $currency)
    {
        $precision = 2;
        if (!$this->currency_has_decimals($currency)) {
            $precision = 0;
        }

        return number_format(round($price, $precision), $precision);
    }

    /**
     * Check if currency has decimals.
     * @param string $currency
     * @return bool
     */
    protected function currency_has_decimals($currency)
    {
        if (in_array($currency, array('HUF'))) {
            return false;
        }

        return true;
    }

}
