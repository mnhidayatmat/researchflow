<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStorageSetting extends Model
{
    protected $fillable = [
        'user_id',
        'storage_disk',
        'google_drive_client_id',
        'google_drive_client_secret',
        'google_drive_refresh_token',
        'google_drive_folder_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
