<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'api_key',
        'model',
        'base_url',
        'is_active',
        'is_default',
        'settings',
        'temperature',
        'max_tokens',
    ];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'settings' => 'array',
            'api_key' => 'encrypted',
            'temperature' => 'float',
            'max_tokens' => 'integer',
        ];
    }
}
