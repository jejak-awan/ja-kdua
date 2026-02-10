<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Support;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\DataServer;
use Illuminate\Http\Request;

class DataServerController extends BaseApiController
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->success(DataServer::all());
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:isp_data_servers,name',
            'status' => 'nullable|string',
        ]);

        $server = DataServer::create($validated);

        return $this->success($server, 'Data server created successfully');
    }

    public function update(Request $request, DataServer $server): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:isp_data_servers,name,'.$server->id,
            'status' => 'sometimes|required|string',
        ]);

        $server->update($validated);

        return $this->success($server, 'Data server updated successfully');
    }

    public function destroy(DataServer $server): \Illuminate\Http\JsonResponse
    {
        $server->delete();

        return $this->success(null, 'Data server deleted successfully');
    }
}
