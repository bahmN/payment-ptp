<?php

namespace App\Http\Controllers;

use App\Models\BlackList;
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
            'is_active' => $request->is_active ?? false,
            'uri_picture' => $request->uri_picture ?? null
        ]);

        return back()->with('status', true);
    }

    public function saveBlacklist(Request $request) {
        $request->validate([
            'email' => ['required', 'string'],
        ], [
            'email.required' => 'Поле Email обязательно для заполнения'
        ]);

        $bl = BlackList::where('email', $request->email)->first();
        if ($bl) {
            return back()->with('statusBlackList', false);
        }

        BlackList::create(['email' => $request->email]);

        return back()->with('statusBlackList', true);
    }

    public function deleteBlacklist(Request $request) {
        $request->validate([
            'email' => ['required', 'string'],
        ], [
            'email.required' => 'Поле Email обязательно для заполнения'
        ]);

        $bl = BlackList::where('email', $request->email)->delete();

        if ($bl) {
            return back()->with('statusBlackListDelete', true);
        }

        return back()->with('statusBlackList', false);
    }
}
