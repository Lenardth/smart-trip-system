use App\Http\Controllers\AiTripController;

Route::post('/ai/trip-plan', [AiTripController::class, 'generate']);
