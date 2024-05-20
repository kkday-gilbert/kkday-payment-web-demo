<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Demo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
@csrf
<div class="bg-white p-6 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4"> Select Payment Method </h2>
    <form id="payment-form">
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input id="credit-card-option" type="radio" class="form-radio" name="option" value="credit-card" checked>
                <span class="ml-2"> Credit Card </span>
            </label>

        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input id="line-pay-option" type="radio" class="form-radio" name="option" value="line-pay">
                <span class="ml-2"> Line Pay </span>
            </label>
        </div>
    </form>
    <div>
        <div id="credit-card" class="component bg-blue-100 p-4 rounded">
            <div class="mb-4">
                <label class="block">
                    <span class="text-gray-700 font-bold">Card Number</span>
                    <input type="text" name="card_number" class="form-input mt-1 block w-full"
                           placeholder="1234 5678 9012 3456">
                </label>
            </div>
            <div class="mb-4">
                <label class="block">
                    <span class="text-gray-700 font-bold">Expiry Date</span>
                    <input type="text" name="expiry_date" class="form-input mt-1 block w-full" placeholder="MM/YY">
                </label>
            </div>
            <div class="mb-4">
                <label class="block">
                    <span class="text-gray-700 font-bold">Card Holder Name</span>
                    <input type="text" name="card_holder" class="form-input mt-1 block w-full"
                           placeholder="John Doe">
                </label>
            </div>
            <div class="mb-4">
                <label class="block">
                    <span class="text-gray-700 font-bold">CVV</span>
                    <input type="text" name="cvv" class="form-input mt-1 block w-full" placeholder="123">
                </label>
            </div>
            <form action="{!! $paymentData['credit-card']['actionUrl'] !!}" method="POST">
                @csrf
                <input type="hidden" name="jsondata" value="{!! $paymentData['credit-card']['body'] !!}">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Pay</button>
            </form>
        </div>
        <div id="line-pay" class="component bg-blue-500 text-white px-4 py-2 rounded">
            <form id="line-pay" action="{!! $paymentData['line-pay']['actionUrl'] !!}" method="POST">
                @csrf
                <input type="hidden" name="jsondata" value="{!! $paymentData['line-pay']['body'] !!}">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Go To Line Pay</button>
            </form>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const optionForm = document.getElementById('payment-form')
        const payComponents = document.querySelectorAll('.component')
        payComponents.forEach(component => component.classList.add('hidden'));
        const defaultOption = document.querySelector('input[name="option"]:checked');
        if (defaultOption) {
            const defaultComponent = document.getElementById(defaultOption.value);
            if (defaultComponent) {
                defaultComponent.classList.remove('hidden')
            }
        }

        optionForm.addEventListener('change', function (event) {
            console.log(event)
            if (event.target.name !== 'option') {
                return;
            }

            console.log(`change form: ${event.target.value}`)
            payComponents.forEach(component => component.classList.add('hidden'));
            const selectedPayComponent = document.getElementById(event.target.value)
            selectedPayComponent.classList.remove('hidden')
        })
    });
</script>
</body>
</html>
