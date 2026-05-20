<?php

namespace App\Http\Controllers;

use App\Contracts\CurrencyServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyServiceInterface $currency) {}

    public function rates(Request $request): JsonResponse
    {
        $base  = strtoupper($request->input('base', 'USD'));
        $rates = $this->currency->getRates($base);

        return response()->json([
            'success'    => true,
            'base'       => $rates['base'],
            'rates'      => $rates['rates'],
            'updated_at' => $rates['updated_at'],
            'currencies' => $this->currency->getSupportedCurrencies(),
        ]);
    }

    public function setCurrency(Request $request): JsonResponse
    {
        $currency = strtoupper($request->input('currency', 'USD'));

        if (!$this->currency->isSupported($currency)) {
            return response()->json(['success' => false, 'message' => 'Unsupported currency.'], 422);
        }

        session(['preferred_currency' => $currency]);

        $supportedCurrencies = $this->currency->getSupportedCurrencies();
        
        return response()->json([
            'success'  => true,
            'currency' => $currency,
            'symbol'   => $this->currency->getSymbol($currency),
            'name'     => $supportedCurrencies[$currency]['name'] ?? $currency,
        ]);
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from'   => 'required|string|size:3',
            'to'     => 'required|string|size:3',
        ]);

        $to        = strtoupper($request->input('to'));
        $converted = $this->currency->convert(
            (float) $request->input('amount'),
            strtoupper($request->input('from')),
            $to
        );

        return response()->json([
            'success'   => true,
            'amount'    => $converted,
            'formatted' => $this->currency->format($converted, $to),
            'symbol'    => $this->currency->getSymbol($to),
            'currency'  => $to,
        ]);
    }
}
