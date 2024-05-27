<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Enums\Language;
use App\Helpers\AesHelper;
use App\Services\PaymentChannelApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use UnexpectedValueException;

class PaymentController extends Controller
{
    private AesHelper $aesHelper;
    private PaymentChannelApiService $paymentChannelApiService;

    public function __construct(AesHelper $aesHelper, PaymentChannelApiService $paymentChannelApiService)
    {
        $this->aesHelper = $aesHelper;
        $this->paymentChannelApiService = $paymentChannelApiService;
    }

    public function mainPage(Request $request)
    {
        try {
            $langCode = Language::from($request->query('lang'));
            $currencyCode = Currency::from($request->query('currency'));
        } catch (UnexpectedValueException $e) {
            Log::error('payment.main-page', [
                'error' => $e->getMessage(),
            ]);
            return redirect(sprintf("%s?lang=%s&currency=%s", route('payment.main-page'), Language::CHINESE_TRADITIONAL_TAIWAN, Currency::TWD));
        }

        $availablePaymentChannel = $this->paymentChannelApiService->getAvailablePaymentChannels([
            'lang' => $langCode->getValue(),
            'currency' => $currencyCode->getValue(),
        ]);

        $paymentList = array_reduce(
            $availablePaymentChannel,
            function (array $list, array $pmchData) use ($currencyCode, $langCode) {
                $method = $pmchData['method'];
                $list[$method] = [
                    'name' => $pmchData['name'],
                    'data' => $this->generatePaymentData($pmchData, $currencyCode, $langCode),
                ];
                return $list;
            },
            []
        );

        return view('payment', [
            'langCode' => $langCode->getValue(),
            'currencyCode' => $currencyCode->getValue(),
            'paymentList' => $paymentList,
            'selectionSettings' => $this->getSelectionSettings(),
        ]);
    }

    public function result(Request $request)
    {
        $jsondata = $this->aesHelper->decrypt(data_get($request->all(), 'jsondata'));
        Log::info('result.request', $jsondata);

        $isSuccess = $jsondata['metadata']['status'] === '0000';
        $error = $isSuccess ? null : $jsondata['metadata'];

        return view('payment_result', [
            'error' => $error,
        ]);
    }
    private function getSelectionSettings(): array {
        $currencyList = Currency::values();
        $langList = Language::values();

        return [
            'language' => [
                'name' => 'Language',
                'value' => $langList,
            ],
            'currency' => [
                'name' => 'Currency',
                'value' => $currencyList,
            ],
        ];
    }

    private function getPaymentJsonBody(array $channel, string $currencyCode, string $lanCode)
    {
        $common = config('payment_channel.common');
        data_set($common, 'payment_source_info.source_ref_no', Str::random(15));
        data_set($common, 'member.member_uuid', Str::uuid()->toString());

        $channelKey = data_get($channel, 'method', '');
        $customParams = data_get(config('payment_channel'), $channelKey, []);
        $paymentData = array_merge($common, [
            'pay_currency' => $currencyCode,
            'is_3d' => data_get($channel, 'is_3d', true),
            'pmch_oid' => data_get($channel, 'id', 1),

            'return_url' => route('payment.result'),
            'cancel_url' => route('payment.result'),

            'custom_params' => $customParams,
        ]);

        return [
            'lang_code' => $lanCode,
            'timestamp' => time(),
            'json' => $paymentData
        ];
    }

    private function generatePaymentData(array $channel, string $currencyCode, string $langCode)
    {
        $jsonData = $this->getPaymentJsonBody($channel, $currencyCode, $langCode);
        $encodedJsonData = $this->aesHelper->encrypt($jsonData);

        return
            [
                'actionUrl' => config('url.kkday_payment_url') . '/' . Arr::get($channel, 'url', ''),
                'body' => $encodedJsonData
            ];
    }
}
