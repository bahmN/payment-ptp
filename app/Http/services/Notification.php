<?php

namespace App\Http\Services;

use App\Models\OptionNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Notification {
    private $token;

    public function __construct() {
        $tokenService = new Token(1);
        $this->token = $tokenService->get();
    }

    public function sendMessage($is_options = false, $id_i) {
        $optionsNotify = OptionNotification::where('is_options', $is_options)
            ->where('is_active', true)->first();

        if (isset($optionsNotify->is_active) && $optionsNotify->is_active) {

            $body = [
                'message' => $optionsNotify->message
            ];

            if (isset($optionsNotify->uri_picture)) {
                $uploadPict = Http::withHeaders([
                    'Accept' => 'application/json'
                ])
                    ->attach('photo', file_get_contents($optionsNotify->uri_picture), 'feedback.png',  ['Content-Type' => 'image/jpeg'])
                    ->post("https://api.digiseller.com/api/debates/v2/upload-preview?token={$this->token}&lang=ru-RU");

                if ($uploadPict->ok()) {
                    $body['files'] = [
                        [
                            'newid' => $uploadPict->json('files')[0]['newid'],
                            'name' => $uploadPict->json('files')[0]['name'],
                            'type' => $uploadPict->json('files')[0]['type']
                        ]
                    ];
                }
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
                ->withBody(json_encode($body, JSON_UNESCAPED_UNICODE))
                ->post('https://api.digiseller.com/api/debates/v2/?token=' . $this->token . '&id_i=' . $id_i);

            if ($response->ok()) {
                return 'ok';
            } else {
                return $response->json();
                Log::error('Отправка уведомления. ОШИБКА.', ['Response: ' => $response->json()]);
            }
        }
    }

    public function checkSeenMessage($id_i) {
        $response = Http::withHeader('Accept', 'application/json')->withUrlParameters(
            [
                'endpoint' => 'https://api.digiseller.com/api/debates/v2',
                'id_i' => $id_i,
                'token' => $this->token
            ]
        )->get('{+endpoint}?token={token}&id_i={id_i}');

        $sendMessage = true; // TODO: ПОМЕНЯТЬ НА FALSE после того как решится проблема с запросами

        if (empty($response->json())) {
            $sendMessage = true;
        }

        foreach ($response->json() as $m) {
            if (
                isset($m['buyer'])
                && $m['buyer']
                && isset($m['date_seen'])
                && $m['date_seen']
            ) {
                $sendMessage = true;
            }
        }

        return $sendMessage;
    }
}
