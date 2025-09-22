<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TagController extends Controller
{
    protected $service;

    public function __construct(Request $request)
    {
        $this->service = new TagService($request);
    }

    public function index()
    {
        $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        return response()->json(['tags' => $this->service->getAll()]);
    }

    public function store(Request $request)
    {
        $this->service->setActivity('createRecord')->checkIfPermissionGranted();
        $this->service->validateRequest();
        $tag = $this->service->create($request->all());
        return response()->json(['tag' => $tag], 201);
    }

    public function show(Tag $tag)
    {
        $this->service->setActivity('viewRecord')->checkIfPermissionGranted();
        // The redundant $this->authorize() call has been removed.
        return response()->json(['tag' => $tag]);
    }

    public function update(Request $request, Tag $tag)
    {
        $this->service->setActivity('updateRecord')->checkIfPermissionGranted();
        $this->service->validateRequest($tag);
        $updatedTag = $this->service->update($request->all(), $tag);
        return response()->json(['tag' => $updatedTag]);
    }

    public function destroy(Tag $tag)
    {
        $this->service->setActivity('deleteRecord')->checkIfPermissionGranted();
        try {
            DB::transaction(fn() => $this->service->delete($tag));
            return response()->json(null, 204);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error("Error deleting tag: " . $e->getMessage());
            return response()->json(['message' => "Could not delete tag."], 500);
        }
    }
}

