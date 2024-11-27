<section class="messages">
    <form class="messages_general" method="POST">
        @csrf

        <input type="hidden" name="id" value="{{ $optionsNotification[1]['id'] }}">

        <h3 class="messages_label">
            Основные сообщения
        </h3>

        <h3>Текст сообщения:</h3>
        <textarea type="text" name="message">{{ $optionsNotification[1]['message'] }}</textarea>

        <div class="messages_time">
            <h3>Отправлять сообщения через</h3>
            <input type="number" name="time_of_sending" value="{{ $optionsNotification[1]['time_of_sending'] }}">
            <h3>секунд</h3>
        </div>

        <label class="label">
            <div class="toggle">
                <input class="toggle-state" type="checkbox" name="is_active" value="1"
                    @if ($optionsNotification[1]['is_active']) checked @endif />
                <div class="toggle-inner">
                    <div class="indicator"></div>
                </div>
                <div class="active-bg"></div>
            </div>
            <h3>Активировать</h3>
        </label>

        <button class="message_submit" formaction="{{ route('saveOptions') }}">Сохранить</button>
    </form>

    <form class="messages_options" method="POST">
        @csrf

        <input type="hidden" name="id" value="{{ $optionsNotification[0]['id'] }}">

        <h3 class="messages_label">
            Опциональные сообщения
        </h3>

        <h3>Текст сообщения:</h3>
        <textarea type="number" name="message">{{ $optionsNotification[0]['message'] }}</textarea>


        <div style="height: 30px"></div>

        <label class="label">
            <div class="toggle">
                <input class="toggle-state" type="checkbox" name="is_active" value="1"
                    @if ($optionsNotification[0]['is_active']) checked @endif />
                <div class="toggle-inner">
                    <div class="indicator"></div>
                </div>
                <div class="active-bg"></div>
            </div>
            <h3>Активировать</h3>
        </label>

        <button class="message_submit" formaction="{{ route('saveOptions') }}">Сохранить</button>
    </form>
</section>

@if (session('status'))
    <div class="alert alert-success" id="alert-success">
        @if (session('status'))
            <h3>Данные сохранены</h3>
        @endif
    </div>

    <script type="text/javascript">
        setTimeout(function() {
            document.getElementById('alert-success').style.display = 'none';
        }, 3000);
    </script>
@endif
