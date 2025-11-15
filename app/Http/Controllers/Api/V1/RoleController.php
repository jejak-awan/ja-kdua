<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends BaseApiController
{
    public function index(Request $request)
    {
        $roles = Role::orderBy('name')->get();

        return $this->success($roles, 'Roles retrieved successfully');
    }
}
