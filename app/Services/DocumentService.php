<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DocumentService
{
    protected $request;
    protected $activity;
    protected $permissionCheckArray;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->activity = null;
        $this->permissionCheckArray = [
            'viewRecord'   => 'view-document',
            'listRecords'  => 'list-documents',
            'createRecord' => 'create-document',
            'updateRecord' => 'edit-document',
            'deleteRecord' => 'delete-document',
        ];
    }

    public function setActivity(string $activity): self
    {
        $this->activity = $activity;
        return $this;
    }

    public function checkIfPermissionGranted(): void
    {
        $user = Auth::user();
        if ($this->activity && isset($this->permissionCheckArray[$this->activity])) {
            if (!$user->can($this->permissionCheckArray[$this->activity])) {
                throw new HttpException(403, 'You do not have permission to perform this action.');
            }
        } else {
            throw new \Exception("Activity or permission not set for DocumentService.");
        }
    }

    public function validateRequest(Document $document = null): void
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        $rules = [
            'title' => [
                'required', 'string', 'max:255',
                Rule::unique('documents')->where(function ($query) use ($organizationId) {
                    return $query->where('organization_id', $organizationId);
                })->ignore($document ? $document->id : null),
            ],
            'content' => 'required|string',
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('documents', 'id')->where(function ($query) use ($organizationId) {
                    return $query->where('organization_id', $organizationId);
                }),
            ],
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => [
                'integer',
                Rule::exists('tags', 'id')->where(function ($query) use ($organizationId) {
                    return $query->where('organization_id', $organizationId);
                }),
            ],
        ];

        $this->request->validate($rules);
    }

    public function getAll()
    {
        $user = Auth::user();
        $query = Document::with('author', 'tags')->where('organization_id', $user->organization_id);

        if ($this->request && $this->request->filled('search_term')) {
            $query->where('title', 'ilike', '%' . $this->request->search_term . '%');
        }

        if ($this->request && $this->request->has('per_page')) {
            $perPage = (int) $this->request->input('per_page', 15);
            return $query->latest()->paginate($perPage);
        }

        return $query->latest()->get();
    }

    public function getById(int $id)
    {
        $user = Auth::user();
        return Document::with('tags')->where('organization_id', $user->organization_id)->findOrFail($id);
    }
    
    public function listForDropdown()
    {
        $user = Auth::user();
        return Document::where('organization_id', $user->organization_id)
            ->select('id', 'title')
            ->orderBy('title')
            ->get();
    }

    public function create(array $data): Document
    {
        $user = Auth::user();
        $data['slug'] = Str::slug($data['title']);
        $data['organization_id'] = $user->organization_id;
        $data['author_id'] = $user->id;

        $document = Document::create($data);

        if (isset($data['tag_ids'])) {
            $document->tags()->sync($data['tag_ids']);
        }

        return $document;
    }

    public function update(array $data, Document $document): Document
    {
        // Create a new version before updating
        Version::create([
            'document_id' => $document->id,
            'organization_id' => $document->organization_id,
            'author_id' => Auth::id(),
            'content' => $document->content,
            'change_summary' => $data['change_summary'] ?? 'Updated document',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $document->update($data);

        if (isset($data['tag_ids'])) {
            $document->tags()->sync($data['tag_ids']);
        }

        return $document;
    }

    public function delete(Document $document): bool
    {
        return $document->delete();
    }

    public function handleImageUpload(Request $request)
    {
        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048']);
        
        $path = $request->file('image')->store('documents', 'public');

        return Storage::url($path);
    }
    
    public function search(string $term = null)
    {
        $user = Auth::user();
        $query = Document::where('organization_id', $user->organization_id)
            ->where('status', 'published');

        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'ilike', '%' . $term . '%')
                  ->orWhere('content', 'ilike', '%' . $term . '%')
                  // This is the new logic to search by tag names
                  ->orWhereHas('tags', function ($tagQuery) use ($term) {
                      $tagQuery->where('name', 'ilike', '%' . $term . '%');
                  });
            });
        }

        return $query->select('id', 'title', 'slug')
            ->orderBy('title')
            ->get();
    }
}

