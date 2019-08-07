<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once 'class-wc-gateway-barion-model-creator.php';

class WC_Gateway_Barion_Request
{

    protected $model_creator;

    private $barion_client;
    private $gateway;


    public function __construct($barion_client, $gateway, $model_creator)
    {
        $this->barion_client = $barion_client;
        $this->gateway = $gateway;
        $this->model_creator = $model_creator ?: new WC_Gateway_Barion_Model_Creator();
    }

    /**
     * @param WC_Order $order
     */
    public function prepare_payment($order)
    { // TODO: refactor me
        $this->order = $order;
        $payee = $this->gateway->payee;
        $transaction = $this->model_creator->create_transaction_model($order, $payee);

        $this->prepare_items($order, $transaction); // ??

        $paymentRequest = $this->model_creator->create_payment_request_model($order, $transaction);


        apply_filters('woocommerce_barion_prepare_payment', $paymentRequest, $order);

        $this->payment = $this->barion_client->PreparePayment($paymentRequest);

        do_action('woocommerce_barion_prepare_payment_called', $this->payment, $order);

        if ($this->payment->RequestSuccessful) {
            $this->gateway->set_barion_payment_id($order, $this->payment->PaymentId);
            $this->is_prepared = true;
        } else {
        }
    }

    protected function prepare_items($order, $transaction)
    {
        $calculated_total = 0;

        foreach ($order->get_items(array('line_item', 'fee', 'shipping', 'coupon')) as $item_id => $item) {
            $itemModel = new ItemModel();
            $itemModel->Name = $item['name'];
            $itemModel->Description = $itemModel->Name;
            $itemModel->Unit = __('piece', 'pay-via-barion-for-woocommerce');
            $itemModel->Quantity = empty($item['qty']) ? 1 : $item['qty'];

            $itemModel->UnitPrice = $order->get_item_subtotal($item, true);
            $itemModel->ItemTotal = $order->get_line_subtotal($item, true);

            if ('coupon' === $item['type']) {
                $itemModel->Name = __('Coupon', 'woocommerce') . ' (' . $item['name'] . ')';

                $discount_amount = wc_get_order_item_meta($item_id, 'discount_amount');
                $discount_amount_tax = wc_get_order_item_meta($item_id, 'discount_amount_tax');

                if (!empty($discount_amount_tax)) {
                    $discount_amount += $discount_amount_tax;
                }

                $itemModel->UnitPrice = -1 * $discount_amount;
                $itemModel->ItemTotal = -1 * $discount_amount;
                $itemModel->SKU = '';
            } elseif ('shipping' === $item['type']) {
                $shipping_cost = wc_get_order_item_meta($item_id, 'cost');
                $shipping_taxes = wc_get_order_item_meta($item_id, 'taxes');
                if (!empty($shipping_taxes)) {
                    $shipping_cost += array_sum($shipping_taxes);
                }
                $itemModel->UnitPrice = $shipping_cost;
                $itemModel->ItemTotal = $shipping_cost;
                $itemModel->SKU = '';
            } elseif ('fee' === $item['type']) {
                $itemModel->UnitPrice = $order->get_item_total($item, true);
                $itemModel->ItemTotal = $order->get_line_total($item, true);
                $itemModel->SKU = '';
            } else {
                $product = $order->get_product_from_item($item);

                if ($product) {
                    if ($product->is_type('variable')) {
                        $itemModel->Name .= ' (' . $product->get_formatted_variation_attributes(true) . ')';
                    }

                    $itemModel->SKU = $product->get_sku();
                }
            }

            $transaction->AddItem($itemModel);
        }
    }

    public function get_redirect_url()
    {
        if (!$this->is_prepared)
            throw new Exception('`prepare_payment` should have been called before `get_redirect_url`.');

        return apply_filters('woocommerce_barion_get_redirect_url', $this->payment->PaymentRedirectUrl, $this->order, $this->payment);
    }
}
