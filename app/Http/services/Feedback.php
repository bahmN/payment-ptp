<?php

namespace App\Http\Services;

use App\Models\Notification;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Feedback {
    private $token;

    public function __construct() {
        $tokenService = new Token(0);
        $this->token = $tokenService->get();
    }

    public function check($invoice_id) {
        $response = Http::retry(3, 3000)->withHeader('Accept', 'application/json')->withUrlParameters(
            [
                'endpoint' => 'https://api.digiseller.com/api/purchase/info',
                'invoice_id' => $invoice_id,
                'token' => $this->token
            ]
        )->get('{+endpoint}/{invoice_id}?token={token}');

        if (isset($response['content']['feedback'])) {
            try {
                $notification = Notification::where('invoice_id', $invoice_id)
                    ->first();
                $notification->delete();
                return true;
            } catch (Exception $e) {
                Log::error('Ошибка удаления записи в Notification. Feedback Service', ['invoice_id' => $invoice_id, $e]);
            }
        }

        return false;
    }
}
