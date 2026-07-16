<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServerProvisioningService;
use Illuminate\Http\Request;

class ServiceApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function suspend(Request $request, Service $service)
    {
        if ($service->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($service->status !== 'active') {
            return response()->json(['error' => 'Service is not active'], 400);
        }

        $provisioning = new ServerProvisioningService;
        $result = $provisioning->suspend($service);

        return $result['success']
            ? response()->json(['message' => 'Service suspended'])
            : response()->json(['error' => $result['error']], 400);
    }

    public function terminate(Request $request, Service $service)
    {
        if ($service->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! in_array($service->status, ['active', 'suspended'])) {
            return response()->json(['error' => 'Service cannot be terminated'], 400);
        }

        $provisioning = new ServerProvisioningService;
        $result = $provisioning->terminate($service);

        return $result['success']
            ? response()->json(['message' => 'Service terminated'])
            : response()->json(['error' => $result['error']], 400);
    }
}
