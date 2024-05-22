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
    <div class="bg-gray-200 p-4 rounded mb-6">
        <div class="mb-4">
            <label class="block">
                <span class="text-gray-700 font-bold text-lg">Currency</span>
                <select id="currency-selector" class="form-select mt-1 block w-full p-2 border border-gray-300 rounded">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="JPY">JPY</option>
                    <option value="TWD">TWD</option>
                    <!-- Add more currencies as needed -->
                </select>
            </label>
        </div>
        <div class="mb-4">
            <label class="block">
                <span class="text-gray-700 font-bold text-lg">Language</span>
                <select id="language-selector" class="form-select mt-1 block w-full p-2 border border-gray-300 rounded">
                    <option value="en">English</option>
                    <option value="es">Spanish</option>
                    <option value="zh-tw">Chinese (Taiwan)</option>
                    <option value="jp">Japan</option>
                    <!-- Add more languages as needed -->
                </select>
            </label>
        </div>
    </div>

    <h2 class="text-2xl font-bold mb-4"> Select Payment Method </h2>
    <form id="payment-form">
        @foreach ($paymentList as $paymentType => $paymentData)
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input id="{{$paymentType}}-option" type="radio" class="form-radio" name="option"
                           value="{{$paymentType}}">
                    <span class="ml-2"> {{ $paymentData['name'] }} </span>
                </label>
            </div>
        @endforeach
    </form>
    <div>
        @foreach($paymentList as $paymentType => $paymentData)
            <div id="{{ $paymentType }}" class="pay-component hidden">
                @if($paymentType === 'tappay')
                    @include('components.credit-card',[
                        'paymentUrl' => $paymentData['data']['actionUrl'],
                        'encodedData' => $paymentData['data']['body'],
                    ])
                @else
                    @include(
                     'components.button-pay', [
                     'paymentUrl' => $paymentData['data']['actionUrl'],
                     'encodedData' => $paymentData['data']['body'],
                     'displayText' => sprintf('Go To %s', $paymentData['name']),
                 ])
                @endif
            </div>
        @endforeach
    </div>
</div>
<pre>
        {{ json_encode($availablePaymentChannel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
</pre>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const optionForm = document.getElementById('payment-form')
        const defaultOption = optionForm.querySelector('input[name="option"]');
        if (defaultOption) {
            defaultOption.checked = true;
            const defaultComponent = document.getElementById(defaultOption.value);
            if (defaultComponent) {
                defaultComponent.classList.remove('hidden');
            }
        }

        const payComponents = document.querySelectorAll('.pay-component')
        optionForm.addEventListener('change', function (event) {
            if (event.target.name !== 'option') {
                return;
            }
            payComponents.forEach(component => component.classList.add('hidden'));
            const selectedPayComponent = document.getElementById(event.target.value)
            selectedPayComponent.classList.remove('hidden')
        })


        const currencySelector = document.getElementById('currency-selector');
        const languageSelector = document.getElementById('language-selector');

        function redirectToUpdatedPage() {
            const currency = currencySelector.value;
            const language = languageSelector.value;
            const newUrl = `${window.location.pathname}?currency=${currency}&lang=${language}`;
            console.log(`redirect to new page ${newUrl}`);
            window.location.href = newUrl;
        }

        const defaultCurrencyOption = currencySelector.querySelector('option[value={{$currencyCode}}]');
        const defaultLangOption = languageSelector.querySelector('option[value={{$langCode}}]');
        if (defaultLangOption) {
            defaultLangOption.selected = true
        }

        if (defaultCurrencyOption) {
            defaultCurrencyOption.selected = true
        }

        currencySelector.addEventListener('change', redirectToUpdatedPage);
        languageSelector.addEventListener('change', redirectToUpdatedPage);
    });
</script>
</body>
</html>
