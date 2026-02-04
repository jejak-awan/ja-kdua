<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $author_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $success_message
 * @property string|null $redirect_url
 * @property array|null $settings
 * @property array|null $blocks
 * @property bool $is_active
 * @property int $submission_count
 * @property int $view_count
 * @property int $start_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormField[] $fields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormSubmission[] $submissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormAnalytics[] $analytics
 */
class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'name',
        'slug',
        'description',
        'success_message',
        'redirect_url',
        'settings',
        'blocks',
        'is_active',
        'submission_count',
        'view_count',
        'start_count',
    ];

    protected $casts = [
        'settings' => 'array',
        'blocks' => 'array',
        'is_active' => 'boolean',
        'submission_count' => 'integer',
        'view_count' => 'integer',
        'start_count' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\FormSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(FormAnalytics::class);
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
        $this->updateDailyStats('views');
    }

    public function incrementStartCount()
    {
        $this->increment('start_count');
        $this->updateDailyStats('starts');
    }

    public function incrementSubmissionCount()
    {
        $this->increment('submission_count');
        $this->updateDailyStats('submissions');
    }

    protected function updateDailyStats($field)
    {
        $analytics = $this->analytics()->firstOrCreate(
            ['date' => now()->toDateString()],
            ['views' => 0, 'starts' => 0, 'submissions' => 0]
        );

        $analytics->increment($field);
    }

    public function getUnreadSubmissionsCount()
    {
        return $this->submissions()->where('status', 'new')->count();
    }
}
