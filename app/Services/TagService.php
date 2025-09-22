<?php

namespace App\Services;

use App\Models\Tag;
use App\Traits\FunctionsTrait; // Assuming this trait is available
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TagService
{
    use FunctionsTrait; // Using the same trait as UserService

    protected $request;
    protected $activity;
    protected $permissionCheckArray;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->activity = null;
        // Permissions are now in an array to match UserService pattern
        $this->permissionCheckArray = [
            'viewRecord'   => ['view-tag'],
            'listRecords'  => ['list-tags'],
            'createRecord' => ['create-tag'],
            'updateRecord' => ['edit-tag'],
            'deleteRecord' => ['delete-tag'],
        ];
    }

    public function setActivity(string $activity): self
    {
        $this->activity = $activity;
        return $this;
    }

    public function checkIfPermissionGranted(): void
    {
        // This now calls the method from your trait, just like in UserService
        if ($this->activity && isset($this->permissionCheckArray[$this->activity])) {
            $this->checkForPermission($this->permissionCheckArray[$this->activity]);
        } else {
            throw new \Exception("Activity or permission not set for TagService.");
        }
    }

    public function validateRequest(Tag $tag = null): void
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
             throw new HttpException(403, 'User is not associated with an organization.');
        }
        $organizationId = $user->organization_id;

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->where(function ($query) use ($organizationId) {
                    return $query->where('organization_id', $organizationId);
                })->ignore($tag ? $tag->id : null),
            ],
        ];

        $this->request->validate($rules);
    }

    public function getAll()
    {
        $user = Auth::user();
        $query = Tag::where('organization_id', $user->organization_id);

        if ($this->request && $this->request->filled('search_term')) {
            $query->where('name', 'ilike', '%' . $this->request->search_term . '%');
        }
        
        $query->orderBy('name');

        if ($this->request && $this->request->has('per_page')) {
            $perPage = (int) $this->request->input('per_page', 15);
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function create(array $data): Tag
    {
        $user = Auth::user();
        $data['slug'] = Str::slug($data['name']);
        $data['organization_id'] = $user->organization_id;

        return Tag::create($data);
    }

    public function update(array $data, Tag $tag): Tag
    {
        $data['slug'] = Str::slug($data['name']);
        $tag->update($data);
        return $tag;
    }

    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }
}

