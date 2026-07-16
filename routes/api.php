<?php

use App\Http\Controllers\Api\ClientApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public API routes
    Route::get('/products', [ClientApiController::class, 'products']);

    // Auth endpoints with rate limiting
    Route::post('/auth/token', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    })->middleware('throttle:30,1');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [ClientApiController::class, 'dashboard']);
        Route::get('/services', [ClientApiController::class, 'services']);
        Route::get('/services/{service}', [ClientApiController::class, 'service']);
        Route::post('/services/{service}/suspend', [ServiceApiController::class, 'suspend']);
        Route::post('/services/{service}/terminate', [ServiceApiController::class, 'terminate']);
        Route::get('/invoices', [ClientApiController::class, 'invoices']);
        Route::get('/invoices/{invoice}', [ClientApiController::class, 'invoice']);
        Route::get('/tickets', [ClientApiController::class, 'tickets']);

        Route::post('/auth/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out']);
        });
    });
});
