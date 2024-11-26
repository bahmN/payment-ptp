<?php

namespace App\Http\Services;

use App\Models\Notification as ModelsNotification;
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

        if (!empty($optionsNotify)) {
            $notification = ModelsNotification::where('invoice_id', $id_i)
                ->where('is_stopped', false)
                ->where('is_notificated', false)
                ->first();

            if ($notification) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json'
                ])
                    ->withBody(json_encode(['message' => $optionsNotify->message], JSON_UNESCAPED_UNICODE))
                    ->post('https://api.digiseller.com/api/debates/v2/?token=' . $this->token . '&id_i=' . $id_i);

                if ($response->ok()) {
                    if (!$is_options) {
                        $notification->is_notificated = true;
                        $notification->save();
                    }

                    Log::error('Отправка уведомления. УСПЕХ.');

                    return 'ok';
                } else {
                    return $response->json();
                    Log::error('Отправка уведомления. ОШИБКА.', ['Response: ' => $response->json()]);
                }
            }
        }
    }
}
