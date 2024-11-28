<?php

use App\Http\Services\Feedback;
use App\Http\Services\Notification as ServicesNotification;
use App\Models\Notification;
use App\Models\OptionNotification;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Database\Eloquent\Collection;


Schedule::call(function () {
    $optNotification = OptionNotification::where('is_active', true)->where('is_options', false)->first();
    $fbChecker = new Feedback();

    if (isset($optNotification->is_active) && $optNotification->is_active == true) {
        Notification::where('is_notificated', false)->chunk(300, function (Collection $notificationList) use ($optNotification, $fbChecker) {
            foreach ($notificationList as $notification) {
                //Проверим: есть отзыв в заказе или нет. Если false, то отсылаем сообщение, иначе пропускаем.
                if ($fbChecker->check($notification->invoice_id) === false) {
                    $timeNotification = $notification->time_of_purchase + $optNotification->time_of_sending;

                    if ($timeNotification < time()) {
                        $notificationService = new ServicesNotification();
                        $notificationService->sendMessage(false, $notification->invoice_id);
                    }
                }

                $notification->is_notificated = true;
                $notification->save();
                continue;
            }
        });
    }
})->everyTenSeconds();
