<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyService $currency) {}

    public function rates(Request $request): JsonResponse
    {
        $base  = strtoupper($request->input('base', 'USD'));
        $rates = $this->currency->getRates($base);

        return response()->json([
            'success'    => true,
            'base'       => $rates['base'],
            'rates'      => $rates['rates'],
            'updated_at' => $rates['updated_at'],
            'currencies' => CurrencyService::$SUPPORTED,
        ]);
    }

    public function setCurrency(Request $request): JsonResponse
    {
        $currency = strtoupper($request->input('currency', 'USD'));

        if (!isset(CurrencyService::$SUPPORTED[$currency])) {
            return response()->json(['success' => false, 'message' => 'Unsupported currency'], 422);
        }

        session(['preferred_currency' => $currency]);

        return response()->json([
            'success'  => true,
            'currency' => $currency,
            'symbol'   => CurrencyService::$SUPPORTED[$currency]['symbol'],
            'name'     => CurrencyService::$SUPPORTED[$currency]['name'],
        ]);
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from'   => 'required|string|size:3',
            'to'     => 'required|string|size:3',
        ]);

        $converted = $this->currency->convert(
            (float) $request->input('amount'),
            strtoupper($request->input('from')),
            strtoupper($request->input('to'))
        );

        $to = strtoupper($request->input('to'));

        return response()->json([
            'success'   => true,
            'amount'    => $converted,
            'formatted' => $this->currency->format($converted, $to),
            'symbol'    => $this->currency->getSymbol($to),
            'currency'  => $to,
        ]);
    }
}
