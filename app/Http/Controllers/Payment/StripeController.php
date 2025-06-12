<?php

namespace App\Http\Controllers\Payment;

use Stripe\Stripe;
use App\Helper\Reply;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Traits\MakePaymentTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\PaymentGatewayCredentials;
use App\Models\GlobalSetting;

class StripeController extends Controller
{
    use MakePaymentTrait;

    public function __construct()
    {
        parent::__construct();

        // Only initialize Stripe if app is installed and credentials exist
        if (env('APP_INSTALLED', false)) {
            $stripeCredentials = PaymentGatewayCredentials::first();

            if ($stripeCredentials && $stripeCredentials->stripe_mode) {
                $secret = $stripeCredentials->stripe_mode === 'test'
                    ? $stripeCredentials->test_stripe_secret
                    : $stripeCredentials->live_stripe_secret;

                if ($secret) {
                    Stripe::setApiKey($secret);
                }
            }
        }

        $this->pageTitle = __('app.stripe');
    }

    public function paymentWithStripe(Request $request, $id)
    {
        $redirectRoute = 'invoices.show';
        $param = 'invoice';

        $invoice = Invoice::find($id);
        if (!$invoice) {
            return Reply::error(__('messages.invoiceNotFound'));
        }

        $paymentIntentId = $request->paymentIntentId;

        if ($request->type === 'order') {
            $redirectRoute = 'orders.show';
            $param = 'order';
            $invoice = Invoice::where('order_id', $id)->latest()->first();

            if (!$invoice) {
                return Reply::error(__('messages.invoiceNotFound'));
            }
        }

        $this->makePayment('Stripe', $invoice->amountDue(), $invoice, $paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();

        return $this->makeStripePayment($redirectRoute, $id, $param);
    }

    public function paymentWithStripePublic(Request $request, $hash)
    {
        $redirectRoute = 'front.invoice';
        $paymentIntentId = $request->paymentIntentId;

        $invoice = Invoice::where('hash', $hash)->first();

        if (!$invoice) {
            return Reply::error(__('messages.invoiceNotFound'));
        }

        $this->makePayment('Stripe', $invoice->amountDue(), $invoice, $paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();

        return $this->makeStripePayment($redirectRoute, $hash, 'hash');
    }

    private function makeStripePayment($redirectRoute, $id, $param = null)
    {
        $param = $param ?? 'invoice';

        $expiry = defined('App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY')
            ? GlobalSetting::SIGNED_ROUTE_EXPIRY
            : 7; // Default to 7 days if not defined

        $signedUrl = url()->temporarySignedRoute(
            $redirectRoute,
            now()->addDays($expiry),
            [$param => $id]
        );

        Session::put('success', __('messages.paymentSuccessful'));

        return Reply::redirect($signedUrl, __('messages.paymentSuccessful'));
    }
}
