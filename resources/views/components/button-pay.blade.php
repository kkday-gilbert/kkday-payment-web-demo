<div class="bg-blue-500 text-white px-4 py-2 rounded">
    <form id="line-pay" action="{{ $paymentUrl }}" method="POST">
        @csrf
        <input type="hidden" name="jsondata" value="{{ $encodedData }}">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">{{ $displayText }}</button>
    </form>
</div>
