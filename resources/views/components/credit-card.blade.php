<div class="bg-blue-100 p-4 rounded">
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
    <form action="{{ $paymentUrl }}" method="POST">
        @csrf
        <input type="hidden" name="jsondata" value="{{ $encodedData }}">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Pay</button>
    </form>
</div>
