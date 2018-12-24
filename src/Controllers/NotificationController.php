<?php
namespace Grechanyuk\Comepay\Controllers;

use Grechanyuk\Comepay\Events\ComepayPaymentResult;
use Grechanyuk\Comepay\Models\ComepayPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController {
    public function notificate(Request $request) {
        Log::debug('Произведен доступ к контроллеру уведомлений Comepay. Полученные данные:', [
            'authorization' => \Illuminate\Support\Facades\Request::header('Authorization'),
            'data' => $request->all()
        ]);

        $payment = ComepayPayment::wherePaymentId($request->input('bill_id'))->firstOrFail();

        if($payment->status != $request->input('status')) {
            $payment->update([
                'status' => $request->input('status')
            ]);

            event(new ComepayPaymentResult($payment->order_id, $payment->status));
        }

        return response('<?xml version="1.0"?> <result><result_code>0</result_code></result>', 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}