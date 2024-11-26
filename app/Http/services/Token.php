<?php

namespace App\Http\Services;

use App\Models\Token as ModelsToken;
use Illuminate\Support\Facades\Http;

class Token {
    private $token;

    public function __construct() {
        $this->token = ModelsToken::first();
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
                'sign' =>  hash('sha256', config('digiseller.request_key') . $timestamp)
            ]);

            $token->id = $response['token'];
            $token->end_life = strtotime($response['valid_thru']);
            $token->save();
        }

        return $token->id;
    }

    public function get() {
        return $this->checkRelevance();
    }
}
