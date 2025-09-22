<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    protected $service;

    public function __construct(Request $request)
    {
        $this->service = new DocumentService($request);
    }

    public function search(Request $request)
    {
        $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        $results = $this->service->search($request->input('term'));
        return response()->json(['documents' => $results]);
    }

    public function listForDropdown()
    {
        $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        $documents = $this->service->listForDropdown();
        return response()->json(['documents' => $documents]);
    }

    public function uploadImage(Request $request)
    {
        // Permissions for this can be tied to 'create' or 'edit'
        // No explicit check needed if the user is already on the form page
        $url = $this->service->handleImageUpload($request);
        return response()->json(['location' => $url]);
    }

    public function index()
    {
        $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        return response()->json(['documents' => $this->service->getAll()]);
    }

    public function store(Request $request)
    {
        $this->service->setActivity('createRecord')->checkIfPermissionGranted();
        $this->service->validateRequest();
        $document = DB::transaction(function() use ($request) {
            return $this->service->create($request->all());
        });
        return response()->json(['document' => $document], 201);
    }

    public function show(Document $document)
    {
        $this->service->setActivity('viewRecord')->checkIfPermissionGranted();
        $loadedDocument = $this->service->getById($document->id);
        return response()->json(['document' => $loadedDocument]);
    }

    public function update(Request $request, Document $document)
    {
        $this->service->setActivity('updateRecord')->checkIfPermissionGranted();
        $this->service->validateRequest($document);
        $updatedDocument = DB::transaction(function() use ($request, $document) {
            return $this->service->update($request->all(), $document);
        });
        return response()->json(['document' => $updatedDocument]);
    }

    public function destroy(Document $document)
    {
        $this->service->setActivity('deleteRecord')->checkIfPermissionGranted();
        $this->service->delete($document);
        return response()->json(null, 204);
    }
}

