<?php

namespace App\Helpers;

use GibberishAES\GibberishAES;

class AesHelper
{
    public function encrypt(array $payload)
    {
        $key = config('aes.jsondata_key');
        return GibberishAES::enc(json_encode($payload), $key);
    }

    public function decrypt(string $payload)
    {
        $key = config('aes.jsondata_key');
        return json_decode(GibberishAES::dec($payload, $key), true);
    }
}
