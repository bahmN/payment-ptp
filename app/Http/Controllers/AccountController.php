<?php

namespace App\Http\Controllers;

use Livewire\Component;

use App\Models\Order;

class AccountController extends Component {
    public function index() {
        return view('admin.account');
    }
}
