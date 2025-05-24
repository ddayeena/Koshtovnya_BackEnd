<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    public function getUsdRate(): float
    {
        $response = Http::get('https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=USD&json');

        if ($response->ok() && isset($response[0]['rate'])) {
            return (float) $response[0]['rate'];
        }

        throw new \Exception('Unable to fetch exchange rate from NBU');
    }

    public function resolveCurrencyData(Request $request): array
    {
        $currency = $request->input('currency', 'uah');
        $rate = 1;

        if ($currency === 'usd') {
            try {
                $rate = $this->getUsdRate();
            } catch (\Exception $e) {
                throw new \Exception('Could not fetch exchange rate: ' . $e->getMessage());
            }
        }

        return [
            'currency' => $currency,
            'rate' => $rate,
        ];
    }

}
