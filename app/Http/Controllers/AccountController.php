<?php

namespace App\Http\Controllers;

use Livewire\Component;

class AccountController extends Component {
    public function index() {
        return view('admin.account');
    }
}
