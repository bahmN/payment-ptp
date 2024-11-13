<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Payment-PTP</title>
</head>

<body>

    <form id="seller.games_form" action="https://seller.games/payment/3dc63474-e4df-48b7-80cd-b566f9b43066/"
        method="POST">
        <input type="hidden" name="invoice_id" value="{{ $request['invoice_id'] }}">
        <input type="hidden" name="amount" value="{{ $request['amount'] }}">
        <input type="hidden" name="currency" value="{{ $request['currency'] }}">
        <input type="hidden" name="description" value="{{ $request['description'] }}">
        <input type="hidden" name="lang" value="{{ $request['lang'] }}">
        <input type="hidden" name="payment_id" value="{{ $request['payment_id'] }}">
        <input type="hidden" name="return_url" value="{{ $request['return_url'] }}">
        <input type="hidden" name="email" value="{{ $request['email'] }}">
    </form>

    <script type="text/javascript">
        function formAutoSubmit() {
            var frm = document.getElementById("seller.games_form");
            frm.submit();
        }

        window.onload = formAutoSubmit;
    </script>
</body>

</html>
