<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Payment-PTP | Message</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @livewireStyles
</head>

<body>
    <header>
        <div class="tabs">
            <a href="{{ route('account') }}">История операций</a>
            <a href="{{ route('message') }}">Сообщения</a>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <input type="image" src="{{ asset('img/logout.png') }}" id="logout" name="submit"></button>
        </form>
    </header>


    @include('livewire.form-message')
</body>

@livewireScripts

<script src="{{ asset('js/app.js') }}"></script>

</html>
