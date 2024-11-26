<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller {
    public function saveNotifyOptions(Request $request) {
        return response()->json('ok', 200);
    }
}
