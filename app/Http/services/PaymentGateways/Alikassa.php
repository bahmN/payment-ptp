<?php

namespace App\Http\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Alikassa {
    const DIGISELLER_API_CALLBACK_URI = 'https://digiseller.market/callback/api';

    public function getPaymentLink($data) {
        $body = [
            'amount' => round($data->amount, 2),
            'order_id' => (string) $data->invoice_id,
            'service' => config('alikassa.service')[$data->payment_id],
            'desc' => 'Оплата заказа №' . $data->invoice_id,
            'customer_ip' => $data->ip(),
            'customer_email' => $data->email,
            'customer_browser_user_agent' => $data->header('User-Agent'),
            'success_redirect_url' => urldecode($data->return_url),
            'notification_endpoint_id' => 749,
            'notification_endpoint_url' => route('payments.gateway.alikassa.callback')
        ];

        $privateKey = openssl_pkey_get_private(
            Storage::disk('local')->get('alikassa/payment/private.pem'),
            Storage::disk('local')->get('alikassa/payment/password.txt'),
        );

        $body = json_encode($body, JSON_UNESCAPED_UNICODE);

        openssl_sign($body, $sign, $privateKey);
        $sign = base64_encode($sign);

        $response = Http::withHeaders([
            'Account' => config('alikassa.account_id'),
            'Sign' => $sign
        ])->withBody($body)->post('https://api-merchant.alikassa.com/v1/payment');

        if (null !== $response->json('url')) {
            Log::info('Оплата через Alikassa.', [$response->json()]);
            return $response->json('url');
        }

        Log::info('ОШИБКА. Оплата через Alikassa.', [$response->json()]);

        return response()->json($response->json());
    }

    public function callback(Request $request) {
        if (isset($request->payment_status) && $request->payment_status == 'paid') {
            $verify = openssl_verify(
                json_encode([
                    'type' => $request->type,
                    'id' => (int) $request->id,
                    'order_id' => $request->order_id,
                    'payment_status' => $request->payment_status,
                    'amount' => $request->amount,
                    'payment_amount' => $request->payment_amount,
                    'is_partial_payment' => $request->is_partial_payment,
                    'account' => $request->account,
                    'service' => $request->service,
                    'desc' => $request->desc,
                ]),
                base64_decode($request->sign),
                Storage::disk('local')->get('alikassa/notification/public.pem'),
            );

            if ($verify) {
                Order::where('invoice_id', $request->order_id)
                    ->where('status', 'N')
                    ->update([
                        'status' => 'P',
                        'operation_id' => $request->id
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

                Log::info('Alikassa Callback.', ['Параметры Alikassa' => $request->all(), 'Ответ Digiseller' => $response->body()]);

                return response()->json('200', 200);
            }

            return response()->json('Bad signature', 400);
        }

        return response()->json('Bad status', 400);
    }
}
