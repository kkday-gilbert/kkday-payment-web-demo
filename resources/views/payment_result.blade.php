<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Result</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded-lg shadow-lg text-center">
    @if (!$error)
        <h1 class="text-2xl font-bold text-green-500 mb-4">Payment Successful!</h1>
        <p class="mb-4">Your payment was processed successfully.</p>
        <button
            onclick="window.location.href='{{ route('payment.main-page') }}'"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
            Return to Main Page
        </button>
    @else
        <h1 class="text-2xl font-bold text-red-500 mb-4">Payment Failed!</h1>
        <p class="mb-4">There was an issue processing your payment. Please try again.</p>
        <button
            onclick="window.location.href='{{ route('payment.main-page') }}'"
            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-300">
            Retry Payment
        </button>
    @endif
</div>
@if($error)
    <pre>
        {{ json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
    </pre>
@endif
</body>
</html>
