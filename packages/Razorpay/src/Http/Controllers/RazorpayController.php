<?php

namespace Razorpay\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Checkout\Facades\Cart;
use App\Exceptions\Handler;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;


class RazorpayController extends Controller
{

    /**
     * InvoiceRepository object
     *
     * @var object
     */
    protected $invoiceRepository;

    /**
     * OrderRepository object
     *
     * @var array
     */
    protected $orderRepository;

    /**
     * Razorpay Key ID.
     *
     * @var string
     */
    protected $keyId;

    /**
     * Razorpay Key secret.
     *
     * @var string
     */
    protected $keySecret;

    public function __construct(
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository
    )
    {
        $this->orderRepository = $orderRepository;

        $this->invoiceRepository = $invoiceRepository;

        $this->keyId = core()->getConfigData('sales.paymentmethods.razorpay.razorpay_key_id');


        $this->keySecret = core()->getConfigData('sales.paymentmethods.razorpay.razorpay_key_secret');
    }

    /**
     * Redirects to the Razorpay payment page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect()
    {   
        $fields = $this -> getFormFields();
        return view('razorpay::razorpay-ui', compact('fields'));
    }

    /**
     * Return form field array
     *
     * @return array
     */
    public function getFormFields()
    {
        $cart = Cart::getCart();

        $api = new Api($this->keyId, $this->keySecret);

        //
        // We create a razorpay order using orders api
        //
        $orderData = [
            'amount'          => $cart->base_grand_total * 100, //Amount is in currency subunits. rupees in paise
            'currency'        => $cart->cart_currency_code,
            'payment_capture' => 1
        ];

        $razorpayOrder = $api->order->create($orderData);

        $fields = [
            'key'             => $this->keyId,
            'amount'          => $orderData['amount'],
            'currency_code'   => $orderData['currency'],
            'name'            => core()->getConfigData('sales.paymentmethods.razorpay.razorpay_merchant_name'),
            "prefill"         => [
            "email"           => $cart->billing_address->email,
            "contact"         => $cart->billing_address->phone,
            ],
            "order_id"        => $razorpayOrder['id'],
        ];

        return $fields;
    }

    /**
     * Perform the transaction
     *
     * @return response
     */

    public function transaction(Request $request){
        $input = $request->all();
        $success = true;
        $error = "Payment Failed";

        if (empty($input['razorpay_payment_id']) === false)
        {
            $api = new Api($this->keyId, $this->keySecret);

            try
            {
                $attributes = array(
                    'razorpay_order_id' => $input['razorpay_order_id'],
                    'razorpay_payment_id' => $input['razorpay_payment_id'],
                    'razorpay_signature' => $input['razorpay_signature']
                );

                $api->utility->verifyPaymentSignature($attributes);
            }
            catch(SignatureVerificationError $e)
            {
                $success = false;
                $error = 'Razorpay Error : ' . $e->getMessage();
            }
        }

        if($success == 'true') {

            $order = $this->orderRepository->create(Cart::prepareDataForOrder());

            $this->order = $this->orderRepository->findOneWhere([
                'cart_id' => Cart::getCart()->id
                ]);

            $this->orderRepository->update(['status' => 'processing'], $this->order->id);

            Cart::deActivateCart();

            session()->flash('order', $order);

            $this->invoiceRepository->create($this->prepareInvoiceData());

            session()->flash('success', trans('razorpay::app.payment-successfull'));

        }
        return redirect()->route('shop.checkout.success');

    }

    /**
     * Prepares order's invoice data for creation
     *
     * @return array
     */
    protected function prepareInvoiceData()
    {
        $invoiceData = [
            "order_id" => $this->order->id
        ];

        foreach ($this->order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

}
