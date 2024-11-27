<?php

namespace App\Http\Controllers;

use App\Models\OptionNotification;
use Illuminate\Http\Request;

class AccountController extends Controller {
    public function index() {
        return view('admin.account');
    }

    public function message() {
        $optionsNotification = OptionNotification::all();
        return view('admin.message', [
            'optionsNotification' => $optionsNotification
        ]);
    }

    public function saveOptionsNotification(Request $request) {
        OptionNotification::where('id', $request->id)->update([
            'message' => $request->message,
            'time_of_sending' => $request->time_of_sending ?? 1,
            'is_active' => $request->is_active ?? false
        ]);

        return back()->with('status', true);
    }
}
