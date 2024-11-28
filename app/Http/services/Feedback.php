<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class Feedback {
    private $token;

    public function __construct() {
        $tokenService = new Token(0);
        $this->token = $tokenService->get();
    }

    public function check($invoice_id) {
        $response = Http::withHeader('Accept', 'application/json')->withUrlParameters(
            [
                'endpoint' => 'https://api.digiseller.com/api/purchase/info',
                'invoice_id' => $invoice_id,
                'token' => $this->token
            ]
        )->get('{+endpoint}/{invoice_id}?token={token}');

        if ($response->json('content')['feedback']) {
            return true;
        }

        return false;
    }
}