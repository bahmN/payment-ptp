@php
    $dataMethod = [
        20212 => [
            'title' => 'СБП',
            'pathIcon' => 'img/paymentMethods/logo_sbp.svg',
            'paymentGateway' => 'Antilopay',
        ],
        20216 => [
            'title' => 'СберПей',
            'pathIcon' => 'img/paymentMethods/logo_sber.svg',
            'paymentGateway' => 'Antilopay',
        ],
        20301 => [
            'title' => 'Банковская карта',
            'pathIcon' => 'img/paymentMethods/logo_bankC.png',
            'paymentGateway' => 'Antilopay',
        ],
        20578 => [
            'title' => 'СБП',
            'pathIcon' => 'img/paymentMethods/logo_sbp.svg',
            'paymentGateway' => 'Alikassa',
        ],
    ];

    $titleStatus = [
        'P' => 'Оплачен',
        'N' => 'Ожидает оплаты',
    ];
@endphp
<section class="all_data">
    <div class="title_table">
        <h3 id="invoiceC">Номер заказа\<br>Номер операции\<br>Дата заказа</h3>

        <h3 id="customerC">Email, ip адрес</h3>

        <h3 id="methodC">Способ оплаты</h3>

        <h3 id="statusC">Статус заказа</h3>

        <h3 id="amountC">Сумма заказа</h3>
    </div>

    @foreach ($orders as $order)
        <section class="data">
            <div class="data_invoice @if ($order->status == 'N') waiting @endif">
                <h3 class="data_invoice-id">{{ $order->invoice_id }}</h3>
                <h3 class="data_invoice-operation--id">{{ $order->operation_id ?? '' }}</h3>
                <h5 class="data_invoice-date">{{ $order->date }}</h5>
            </div>

            <div class="data_customer @if ($order->status == 'N') waiting @endif">
                <h3 class="data_customer-email">{{ $order->email }}</h3>
                <h5 class="data_customer-ip">{{ $order->customer_ip ?? '' }}</h5>
            </div>

            <div class="data_method @if ($order->status == 'N') waiting @endif">
                <div class="data_method-groupe--img @if ($order->status == 'N') img_overlay @endif">
                    <img src="{{ asset($dataMethod[$order->payment_id]['pathIcon']) }}" alt="icon">
                </div>
                <div class="data_method-groupe--info">
                    <h3 class="data_method-title">{{ $dataMethod[$order->payment_id]['title'] }}</h3>
                    <h5 class="data_method-gateway">{{ $dataMethod[$order->payment_id]['paymentGateway'] }}</h5>
                </div>
            </div>

            <div class="data_status @if ($order->status == 'N') waiting stat @endif">
                <h3 class="data_status-title">{{ $titleStatus[$order->status] }}</h3>
            </div>

            <div class="data_amount @if ($order->status == 'N') waiting @endif">
                <h5 class="data_amount-title">В digiseller</h5>
                <h3 class="data_amount-sum">{{ $order->amount }}₽</h3>
            </div>
        </section>
    @endforeach
    <hr>
    <section class="footer_table">
        <div class="footer_table__search">
            <h3 class="footer_table-title">Поиск: </h3>
            <input id="search" type="text" onkeyup="updatePage()" wire:model.live="searchTerm">
        </div>

        {{ $orders->links() }}
    </section>
</section>
