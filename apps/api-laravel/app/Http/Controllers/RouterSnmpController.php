<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Services\RouterProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RouterSnmpController extends Controller
{
    public function show(Router $router, RouterProvisioningService $provisioning): JsonResponse
    {
        $user = Auth::user();

        abort_if(! $user || ! $user->hasPermission('router.manage'), 403);
        abort_if($user->tenant_id && $router->tenant_id !== $user->tenant_id, 404);

        return response()->json($provisioning->liveSnmpSnapshot($router));
    }
}
