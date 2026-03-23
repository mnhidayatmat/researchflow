<?php

declare(strict_types=1);

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

return [

    /*
    |--------------------------------------------------------------------------
    | Settings Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache settings values in seconds.
    |
    */

    'cache_duration' => env('SETTINGS_CACHE_DURATION', 3600),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Default storage configuration values.
    |
    */

    'storage' => [
        'default_disk' => env('FILESYSTEM_DISK', 'local'),

        'max_file_size' => env('MAX_FILE_SIZE', 51200), // KB (50MB)

        'allowed_mime_types' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
        ],

        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp',
            'zip', 'rar', '7z'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Settings
    |--------------------------------------------------------------------------
    |
    | Default AI configuration values.
    |
    */

    'ai' => [
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),
        'ssl_verify' => env('AI_SSL_VERIFY', true),
        'ca_bundle' => env('AI_CA_BUNDLE'),

        'providers' => [
            'openai' => [
                'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'api_key' => env('OPENAI_API_KEY'),
                'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 2000),
                'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
            ],

            'gemini' => [
                'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/models'),
                'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
                'api_key' => env('GEMINI_API_KEY'),
                'max_tokens' => (int) env('GEMINI_MAX_TOKENS', 2000),
            ],

            'anthropic' => [
                'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
                'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
                'api_key' => env('ANTHROPIC_API_KEY'),
                'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 2000),
                'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
            ],

            'ollama' => [
                'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434/v1'),
                'model' => env('OLLAMA_MODEL', 'llama2'),
                'max_tokens' => (int) env('OLLAMA_MAX_TOKENS', 2000),
            ],
        ],

        'system_prompts' => [
            'default' => 'You are ResearchFlow AI, an academic research assistant. Help with research planning, writing, methodology, and analysis. Be concise and academic.',
            'planning' => 'You are an expert research advisor helping students plan their postgraduate research. Provide structured, actionable advice.',
            'proposal' => 'You are an academic mentor helping craft research proposals. Ensure suggestions follow academic standards.',
            'analysis' => 'You are a statistical consultant advising on research methodology and data analysis.',
            'writing' => 'You are an academic writing coach. Help students communicate their research effectively.',
        ],

        'rate_limits' => [
            'max_requests_per_hour' => (int) env('AI_RATE_LIMIT', 100),
            'max_tokens_per_request' => (int) env('AI_MAX_TOKENS', 4000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Research Settings
    |--------------------------------------------------------------------------
    |
    | Default research supervision settings.
    |
    */

    'research' => [
        'default_journey_duration_weeks' => 52, // 1 year

        'milestone_categories' => [
            'literature_review' => 'Literature Review',
            'proposal' => 'Research Proposal',
            'data_collection' => 'Data Collection',
            'analysis' => 'Data Analysis',
            'writing' => 'Thesis Writing',
            'defense' => 'Thesis Defense',
        ],

        'task_statuses' => [
            'backlog' => 'Backlog',
            'planned' => 'Planned',
            'in_progress' => 'In Progress',
            'waiting_review' => 'Waiting Review',
            'revision' => 'Revision',
            'completed' => 'Completed',
        ],

        'task_priorities' => [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Settings
    |--------------------------------------------------------------------------
    |
    | Progress report configuration.
    |
    */

    'reports' => [
        'frequency_weeks' => env('REPORT_FREQUENCY_WEEKS', 2),

        'required_sections' => [
            'activities_completed',
            'activities_planned',
            'challenges_encountered',
            'progress_assessment',
        ],

        'max_word_count' => (int) env('REPORT_MAX_WORD_COUNT', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Default notification configuration.
    |
    */

    'notifications' => [
        'channels' => ['database', 'email'],

        'events' => [
            'task_assigned' => true,
            'task_due_soon' => true,
            'task_overdue' => true,
            'report_submitted' => true,
            'report_feedback' => true,
            'meeting_scheduled' => true,
            'meeting_reminder' => true,
            'file_uploaded' => false,
        ],

        'reminder_days_before' => [
            'task' => (int) env('TASK_REMINDER_DAYS', 3),
            'meeting' => (int) env('MEETING_REMINDER_DAYS', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration.
    |
    */

    'security' => [
        'session_timeout_minutes' => (int) env('SESSION_TIMEOUT', 120),

        'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),

        'lockout_duration_minutes' => (int) env('LOCKOUT_DURATION', 15),

        'password_min_length' => (int) env('PASSWORD_MIN_LENGTH', 8),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Default pagination values.
    |
    */

    'pagination' => [
        'per_page' => [
            'tasks' => 20,
            'reports' => 15,
            'meetings' => 10,
            'files' => 20,
            'students' => 25,
            'notifications' => 20,
        ],
    ],

];

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
|
| Convenience functions for accessing settings.
|
*/

if (!function_exists('setting')) {
    /**
     * Get a setting value from the database or config.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        // First check database
        $value = SystemSetting::get($key);

        if ($value !== null) {
            return $value;
        }

        // Fallback to config
        $configKey = str_replace('.', '.', $key);
        return config("settings.{$configKey}", $default);
    }
}

if (!function_exists('setting_set')) {
    /**
     * Set a setting value in the database.
     */
    function setting_set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        SystemSetting::set($key, $value, $group, $type);
    }
}

if (!function_exists('ai_setting')) {
    /**
     * Get an AI-specific setting.
     */
    function ai_setting(string $key, mixed $default = null): mixed
    {
        return setting("ai.{$key}", $default);
    }
}

if (!function_exists('storage_setting')) {
    /**
     * Get a storage-specific setting.
     */
    function storage_setting(string $key, mixed $default = null): mixed
    {
        return setting("storage.{$key}", $default);
    }
}
