<?php

namespace App;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    protected $fillable =    [
        'user_id',
        'is_sms_notification_enabled',
        'is_mail_notification_enabled',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
