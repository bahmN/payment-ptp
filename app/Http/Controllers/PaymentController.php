<?php

namespace App\Http\Controllers;

use App\Http\Services\PaymentGateways\Alikassa;
use App\Http\Services\PaymentGateways\Antilopay;
use App\Http\Services\Token;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller {
    const ANTILOPAY_SIGN_VERSION = 1;

    const DIGISELLER_API_CALLBACK_URI = 'https://digiseller.market/callback/api';

    const CURRENCY_WM = [
        'WMZ' => 'USD',
        'WMR' => 'RUB',
        'WME' => 'EUR'
    ];

    public function init(Request $request) {
        if ($request->has('invoice_id', 'amount', 'currency', 'description', 'lang', 'email', 'payment_id', 'return_url')) {
            if (!$this->checkAmount($request)) {
                return response()->json('Неверная сумма или валюта заказа', 403);
            }

            if (isset(config('antilopay.payment_id')[$request->payment_id])) {
                Order::firstOrCreate(
                    ['invoice_id' => $request->invoice_id],
                    [
                        'amount' => round($request->amount, 2),
                        'currency' => $request->currency,
                        'description' => $request->description,
                        'lang' => $request->lang,
                        'email' => $request->email,
                        'payment_id' => $request->payment_id,
                        'return_url' => urldecode($request->return_url),
                        'status' => 'N',
                        'date' => date('Y-m-d H:i:s')
                    ]
                );

                $antilopay = new Antilopay();
                return redirect()->to($antilopay->getPaymentLink($request));
            } elseif (isset(config('alikassa.service')[$request->payment_id])) {
                Order::firstOrCreate(
                    ['invoice_id' => $request->invoice_id],
                    [
                        'amount' => round($request->amount, 2),
                        'currency' => $request->currency,
                        'description' => $request->description,
                        'lang' => $request->lang,
                        'email' => $request->email,
                        'payment_id' => $request->payment_id,
                        'return_url' => urldecode($request->return_url),
                        'status' => 'N',
                        'date' => date('Y-m-d H:i:s'),
                        'customer_ip' =>  $request->ip()
                    ]
                );

                $alikassa = new Alikassa();

                return redirect()->to($alikassa->getPaymentLink($request));
            } else {
                return view('sellergames', ['request' => $request->all()]);
            }
        } else {
            return response()->json(['Bad request'], 200);
        }
    }

    public function antilopayCallback(Request $request) {
        if (isset($request->status) && $request->status == 'SUCCESS') {
            $sign = $request->header('X-Apay-Callback');
            $publicKey = "-----BEGIN PUBLIC KEY-----\n" . config('antilopay.callback_key') . "\n-----END PUBLIC KEY-----";
            $jsonData = file_get_contents('php://input');
            $rawSign = base64_decode($sign);

            if (openssl_verify($jsonData, $rawSign, $publicKey, OPENSSL_ALGO_SHA256) != 1) {
                Log::error('Antilopay Callback. НЕПРАВИЛЬНАЯ СИГНАТУРА', ['ПАРАМЕТРЫ АНТИЛОПЫ' => $request->all()]);

                return response()->json(['Wrong signature'], 403);
            };

            Order::where('invoice_id', $request->order_id)
                ->where('status', 'N')
                ->update([
                    'status' => 'P',
                    'customer_ip' => $request->customer_ip,
                    'operation_id' => $request->payment_id
                ]);

            $signData = [
                'invoice_id' => $request->order_id,
                'amount' =>  number_format($request->amount, 2, '.', ''),
                'currency' => 'RUB',
                'status' => 'paid'
            ];

            ksort($signData);

            $stringToSign = '';
            foreach ($signData as $key => $value) {
                $stringToSign .= "$key:$value;";
            }

            $signature = hash_hmac('SHA256', $stringToSign, config('digiseller.callback_key'), false);

            $body = [
                'invoice_id' => $request->order_id,
                'amount' =>  number_format($request->amount, 2, '.', ''),
                'currency' => 'RUB',
                'status' => 'paid',
                'signature' => strtoupper($signature),
            ];

            $response = Http::asForm()->get(self::DIGISELLER_API_CALLBACK_URI, $body);
            Log::info('Antilopay Callback.', ['ПАРАМЕТРЫ АНТИЛОПЫ' => $request->all(), 'Ответ дигги' => $response->body()]);
        }

        return response()->json('Bad request', 400);
    }

    public function digisellerCallback(Request $request) {
        if ($request->has('invoice_id', 'seller_id', 'amount')) {

            Log::info('Запрос на подтверждение оплаты от Дигги.', [$request->all()]);

            $order = Order::where('invoice_id', $request->invoice_id)->first();

            if (isset($order)) {
                $status = $order->status == 'N' ? 'wait' : 'paid';

                $signData = [
                    'invoice_id' => $request->invoice_id,
                    'status' => $status,
                    'amount' =>  number_format($request->amount, 2, '.', ''),
                    'currency' => $request->currency,
                ];

                ksort($signData);

                $stringToSign = '';
                foreach ($signData as $key => $value) {
                    $stringToSign .= "$key:$value;";
                }

                $signature = hash_hmac('SHA256', $stringToSign, config('digiseller.callback_key'), false);

                $body = [
                    'invoice_id' => $request->invoice_id,
                    'amount' =>  number_format($request->amount, 2, '.', ''),
                    'currency' => $request->currency,
                    'status' => $status,
                    'signature' => strtoupper($signature),
                    'error' => ''
                ];

                Log::info('Ответ для дигги. Подтверждаем оплату.', [$body]);

                return response()->json($body);
            } else {
                return Http::asForm()->get('https://seller.games/status/3dc63474-e4df-48b7-80cd-b566f9b43066/', $request->all());
            }
        } else {
            return response()->json(['Bad request'], 400);
        }
    }

    protected function checkAmount($request) {
        $token = new Token(0);

        $order = Http::get("https://api.digiseller.com/api/purchase/info/{$request->invoice_id}?token=" . $token->get());

        if (
            !isset($order['content']['amount']) ||
            $order['content']['amount'] != $request->amount ||
            !isset(self::CURRENCY_WM[$order['content']['currency_type']]) ||
            self::CURRENCY_WM[$order['content']['currency_type']] != $request->currency
        ) {
            $orderAmount = $order['content']['amount'] ?? 'nothing';
            $orderCurrency = $order['content']['currency_type'] ?? 'nothing';

            Log::error('Инциализация платежа. СУММА или Валюта НЕ РАВНЫ', ['Сумма и валюта заказа' => [
                $orderAmount,
                $orderCurrency
            ], 'Сумма и валюта переданная в запросе' => [
                $request->amount,
                $request->currency
            ]]);

            return false;
        }

        return true;
    }
}
