<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleService;
use App\Traits\FunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    use FunctionsTrait;
    protected $service;

    public function __construct(Request $request)
    {
        $this->service = new RoleService($request);
    }

    public function index(Request $request)
    {
        $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        return response()->json(['roles' => $this->service->getAll()]);
    }

    public function store(Request $request)
    {
       
        // Log::info('--- ROLE STORE METHOD WAS REACHED ---',$request->all());
        //    dd("sss");
        $this->service->setActivity('createRecord')->checkIfPermissionGranted();
        $this->service->validateRequest();
        $role = DB::transaction(function () use ($request) {
            $role =  $this->service->create($request); 
            $this->service->SyncPermissionToRole($role, $request->permission_ids);
            return $role;
        });
        return response()->json(['role' => $role->load(['permissions'])], 201);
    }

    public function show(Role $role)
    {
        $this->service->setActivity('viewRecord')->checkIfPermissionGranted();
        $loadedRole = $this->service->getById($role->id,null);
        $loadedRole->append(['permission_ids'])->makeHidden('permissions');
        if (!$loadedRole) return response()->json(['message' => 'Role not found.'], 404);
        return response()->json(['role' => $loadedRole]);
    }

    public function update(Request $request, Role $role)
    {
        $this->service->setActivity('updateRecord')->checkIfPermissionGranted();
        $this->service->validateRequest($role);
        $updatedRole = DB::transaction(function () use ($request, $role) {
            $this->service->update($request, $role);
            $this->service->SyncPermissionToRole($role, $request->permission_ids);
            return $role;
        });
        return response()->json(['role' => $updatedRole->load(['permissions'])]);
    }

    public function destroy(Role $role)
    {
        $this->service->setActivity('deleteRecord')->checkIfPermissionGranted();
        try {
            DB::transaction(fn() => $this->service->delete($role));
            return response()->json(null, 204);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error("Error deleting role: " . $e->getMessage());
            return response()->json(['message' => "Could not delete role."], 500);
        }
    }
    public function deleteMultiple(Request $request)
    {

        $this->service->setActivity('deleteRecord')->checkIfPermissionGranted();
        try {
            DB::transaction(fn() => $this->service->deleteMultiple($request->ids));
            return response()->json(null, 204);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error("Error deleting district: " . $e->getMessage());
            return response()->json(['message' => "Could not delete address."], 500);
        }
    }
}
