<?php

namespace App\Models;


use App\UserNotificationSetting;
use App\Models\UserPersonalInformation;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

     protected $guard_name = 'sanctum'; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'password',
        'phone_number',
        'is_ris_admin',
        'phone_verified_at',
        'profile_pic_path'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //  protected $appends = ['role_ids'];


    public function stores()
    {
        return $this->belongsToMany(Store::class)->withPivot('start_date', 'end_date');
    }

      /**
     * The organizations that the user belongs to.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'user_organizations')
                    ->withPivot('is_super_user')
                    ->withTimestamps();
    }
    public function currentOrganization()
    {
        $organizations = $this->organizations;
        if(count($organizations))
        {
            return $organizations->first();
        }
        return null;
    }

    public function noficationSettings()
    {
        return $this->hasOne(UserNotificationSetting::class, 'user_id');
                    
    }



    /**
     * Accessor for the user's currently active organization.
     *
     * This function retrieves the organization model that the user has
     * currently selected to work within. It checks the session for a
     * 'current_organization_id'. If not found, it defaults to the first
     * organization the user belongs to.
     *
     * @return \App\Models\Organization|null
     */
    public function getCurrentOrganizationAttribute()
    {
        // Get the current organization ID from the session.
        $organizationId = session('current_organization_id');

        // If no ID is in the session, default to the user's first organization.
        if (!$organizationId && $this->organizations()->exists()) {
            $firstOrganization = $this->organizations()->first();
            if ($firstOrganization) {
                // Set it in the session for subsequent requests in the same login session.
                session(['current_organization_id' => $firstOrganization->id]);
                return $firstOrganization;
            }
        }
        
        // Find and return the organization model from the user's list of organizations.
        // This ensures a user cannot access an organization they don't belong to.
        return $this->organizations()->find($organizationId);
    }

     public function getRoleIdsAttribute()
    {
        // Eager load permissions to avoid N+1 query problem
        return $this->roles->pluck('id');
    }

    public function personalInformation()
    {
        return $this->hasOne(UserPersonalInformation::class, 'user_id');
    }
    public function notificationSettings()
    {
        return $this->hasOne(UserNotificationSetting::class, 'user_id');
    }
}