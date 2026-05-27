<?php

namespace App\Http\Controllers;

use App\Services\CurrencyPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    public function __construct(private readonly CurrencyPreferenceService $currencies) {}

    public function rates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'base' => ['nullable', 'string', 'size:3', Rule::in($this->supportedCurrencyCodes())],
        ]);

        return response()->json($this->currencies->rates($validated['base'] ?? 'USD'));
    }

    public function setCurrency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currency' => ['required', 'string', 'size:3', Rule::in($this->supportedCurrencyCodes())],
        ]);

        $result = $this->currencies->setCurrency($validated['currency']);
        $status = $result['status'] ?? 200;
        unset($result['status']);

        return response()->json($result, $status);
    }

    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from'   => ['required', 'string', 'size:3', Rule::in($this->supportedCurrencyCodes())],
            'to'     => ['required', 'string', 'size:3', Rule::in($this->supportedCurrencyCodes())],
        ]);

        return response()->json($this->currencies->convert(
            (float) $validated['amount'],
            $validated['from'],
            $validated['to']
        ));
    }

    private function supportedCurrencyCodes(): array
    {
        return array_keys(config('currencies.supported', []));
    }
}
