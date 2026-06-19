<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MvpController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('health', [MvpController::class, 'health']);

    Route::post('auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::prefix('tenants/{tenant_id}')->middleware('tenant')->group(function (): void {
            Route::get('customers', [MvpController::class, 'customers']);
            Route::post('customers', [MvpController::class, 'storeCustomer']);
            Route::get('customers/{id}', [MvpController::class, 'showCustomer']);
            Route::put('customers/{id}', [MvpController::class, 'updateCustomer']);

            Route::get('services', [MvpController::class, 'services']);
            Route::post('services', [MvpController::class, 'storeService']);
            Route::get('services/{id}', [MvpController::class, 'showService']);
            Route::patch('services/{id}', [MvpController::class, 'patchService']);
            Route::post('services/{service_id}/router-mapping', [MvpController::class, 'mapServiceRouter']);

            Route::get('routers', [MvpController::class, 'routers']);
            Route::post('routers', [MvpController::class, 'storeRouter']);
            Route::get('routers/{id}', [MvpController::class, 'showRouter']);
            Route::put('routers/{id}', [MvpController::class, 'updateRouter']);

            Route::get('router-interfaces', [MvpController::class, 'routerInterfaces']);
            Route::post('router-interfaces', [MvpController::class, 'storeRouterInterface']);

            Route::get('nas-devices', [MvpController::class, 'nasDevices']);
            Route::post('nas-devices', [MvpController::class, 'storeNasDevice']);
            Route::get('nas-devices/{id}', [MvpController::class, 'showNasDevice']);
            Route::put('nas-devices/{id}', [MvpController::class, 'updateNasDevice']);

            Route::get('radius/servers', [MvpController::class, 'radiusServers']);
            Route::post('radius/servers', [MvpController::class, 'storeRadiusServer']);
            Route::get('radius/servers/{id}', [MvpController::class, 'showRadiusServer']);
            Route::post('radius/servers/{id}/test', [MvpController::class, 'testRadiusServer']);

            Route::get('radius/profiles', [MvpController::class, 'radiusProfiles']);
            Route::post('radius/profiles', [MvpController::class, 'storeRadiusProfile']);

            Route::get('radius/users', [MvpController::class, 'radiusUsers']);
            Route::post('radius/users', [MvpController::class, 'storeRadiusUser']);
            Route::post('radius/users/{id}/sync', [MvpController::class, 'syncRadiusUser']);
            Route::post('radius/users/{id}/suspend', [MvpController::class, 'suspendRadiusUser']);
            Route::post('radius/users/{id}/activate', [MvpController::class, 'activateRadiusUser']);

            Route::post('router-script-generator', [MvpController::class, 'generateRouterScript']);

            Route::get('invoices', [MvpController::class, 'invoices']);
            Route::post('invoices', [MvpController::class, 'storeInvoice']);
            Route::post('invoices/{id}/evaluate-unsuspend', [MvpController::class, 'evaluateInvoiceUnsuspend']);
            Route::post('payments', [MvpController::class, 'storePayment']);
        });
    });
});
