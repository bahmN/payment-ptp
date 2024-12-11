<?php

namespace App\Http\Services;

use App\Models\Token as ModelsToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Token {
    private $token;
    private $digisellerKey;

    public function __construct($is_notify) {
        $this->digisellerKey = $is_notify ? config('digiseller.request_notify_key') : config('digiseller.request_key');
        $this->token = ModelsToken::where('is_notify', $is_notify)->first();
    }

    protected function checkRelevance() {
        $token = $this->token;
        $timestamp = time();

        if ($token->end_life < $timestamp) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.digiseller.com/api/apilogin', [
                'seller_id' => config('digiseller.seller_id'),
                'timestamp' => $timestamp,
                'sign' =>  hash('sha256', $this->digisellerKey . $timestamp)
            ]);

            if ($response['token']) {
                $token->id = $response['token'];
                $token->end_life = strtotime($response['valid_thru']);
                $token->save();

                return $token->id;
            }

            Log::error('Ошибка генерации токена.', [$response]);

            return response()->json('Ошибка генерации токена');
        }

        return $token->id;
    }

    public function get() {
        return $this->checkRelevance();
    }
}
