<?php

namespace App\Http\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Antilopay {
    const DIGISELLER_API_CALLBACK_URI = 'https://digiseller.market/callback/api';

    public function getPaymentLink($data) {
        $body = [
            'project_identificator' => config('antilopay.id'),
            'amount' =>  round($data->amount, 2),
            'order_id' => $data->invoice_id,
            'currency' => $data->currency,
            'product_name' => $data->description,
            'product_type' => 'goods',
            'product_type' => 1,
            'description' => 'Оплата заказа №' . $data->invoice_id,
            'customer' => [
                'email' => $data->email
            ],
            'prefer_methods' => [config('antilopay.payment_id')[$data->payment_id]],
            'success_url' => urldecode($data->return_url)
        ];

        $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        $secretKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $secretKey .= config('antilopay.secret_key');
        $secretKey .= "\n-----END RSA PRIVATE KEY-----";

        $rawSignature = '';

        openssl_sign($body, $rawSignature, $secretKey, OPENSSL_ALGO_SHA256);

        $signature = base64_encode($rawSignature);

        $headers = [
            'X-Apay-Secret-Id' => config('antilopay.secret_id'),
            'X-Apay-Sign-Version' => config('antilopay.api_version'),
            'X-Apay-Sign' => $signature
        ];

        $response = Http::retry(3, 3000)->withHeaders($headers)->withBody($body)->post(config('antilopay.uri') . 'create');

        if (null !== $response->json('payment_url')) {
            Log::info('Оплата через Antilopay.', [$response->json()]);
            return $response->json('payment_url');
        }

        Log::info('ОШИБКА. Оплата через Antilopay.', [$response->json()]);

        return response()->json($response->json());
    }

    public function callback(Request $request) {
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

        return response()->json('Bad request', 200);
    }
}
