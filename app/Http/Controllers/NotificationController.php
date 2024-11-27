<?php

namespace App\Http\Controllers;

use App\Http\Services\Notification as ServicesNotification;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller {
    public function saveNotificationBotWebhook(Request $request) {
        if ($request->has('invoice_id', 'email', 'options')) {
            $notification = Notification::firstOrCreate(
                ['invoice_id' => $request->invoice_id],
                [
                    'email' => $request->email,
                    'is_options' => $request->options,
                    'time_of_purchase' => $request->time,
                ]
            );

            if ($notification->is_options) {
                $sNotify = new ServicesNotification();
                $sNotify->sendMessage(1, $notification->invoice_id);
            }

            if (!empty($notification)) {
                return response()->json('saved', 200);
            }
        }

        return response()->json('ok', 200);
    }
}
