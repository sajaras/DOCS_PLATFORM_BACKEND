<?php

namespace App\Http\Controllers;


use App\Services\PermissionService;
use App\Traits\FunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use FunctionsTrait;
    protected $service;

    public function __construct(Request $request)
    {
        $this->service = new PermissionService($request);
    }

    public function index(Request $request)
    {
        $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        return response()->json(['permissions' => $this->service->getAll()]);
    }

    public function store(Request $request)
    {

        $this->service->setActivity('createRecord')->checkIfPermissionGranted();
        $this->service->validateRequest();
        $permission = DB::transaction(function () use ($request) {
            return $this->service->create($request);
        });
        return response()->json(['permission' => $permission->load(['roles'])], 201);
    }

    public function show(Permission $permission)
    {
        $this->service->setActivity('viewRecord')->checkIfPermissionGranted();
        $loadedPermission = $this->service->getById($permission->id);
        if (!$loadedPermission) return response()->json(['message' => 'Permission not found.'], 404);
        return response()->json(['permission' => $loadedPermission]);
    }

    public function update(Request $request, Permission $permission)
    {
        $this->service->setActivity('updateRecord')->checkIfPermissionGranted();
        $this->service->validateRequest($permission);
        $updatedPermission = DB::transaction(fn() => $this->service->update($request, $permission));
        return response()->json(['permission' => $updatedPermission->load(['district', 'state'])]);
    }

    public function destroy(Permission $permission)
    {
        $this->service->setActivity('deleteRecord')->checkIfPermissionGranted();
        try {
            DB::transaction(fn() => $this->service->delete($permission));
            return response()->json(null, 204);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error("Error deleting permission: " . $e->getMessage());
            return response()->json(['message' => "Could not delete permission."], 500);
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
    public function getPermissionGroup(Request $request)
    {
         $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        return response()->json(['permission-groups' => $this->service->getPermissionGroups($request)]);
    }
}
