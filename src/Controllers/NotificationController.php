<?php
namespace Grechanyuk\Comepay\Controllers;

use Grechanyuk\Comepay\Events\ComepayPaymentResult;
use Grechanyuk\Comepay\Models\ComepayPayment;
use Illuminate\Http\Request;

class NotificationController {
    public function notificate(Request $request) {
        $payment = ComepayPayment::wherePaymentId($request->input('bill_id'))->firstOrFail();

        $payment->update([
            'status' => $request->input('status')
        ]);

        event(new ComepayPaymentResult($payment->order_id, $payment->status));

        return response('<?xml version="1.0"?> <result><result_code>0</result_code></result>', 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}