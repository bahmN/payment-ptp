<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class Table extends Component {
    public $searchTerm;

    public function render() {
        return view('livewire.table', ['orders' => Order::where(function ($sub_query) {
            $sub_query->where('invoice_id', 'like', '%' . $this->searchTerm . '%')
                ->orWhere('email', 'like', '%' . $this->searchTerm . '%')
                ->orWhere('customer_ip', 'like', '%' . $this->searchTerm . '%')
                ->orWhere('operation_id', 'like', '%' . $this->searchTerm . '%');
        })->orderBy('date', 'desc')->cursorPaginate(15)]);
    }
}
