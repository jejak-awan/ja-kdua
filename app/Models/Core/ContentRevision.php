<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentRevision extends Model
{
    /** @use HasFactory<\Database\Factories\Core\ContentRevisionFactory> */
    use HasFactory;

    protected $fillable = [
        'content_id',
        'author_id',
        'title',
        'body',
        'blocks',
        'meta',
        'reason',
    ];

    protected $casts = [
        'blocks' => 'array',
        'meta' => 'array',
    ];

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Alias for backward compatibility if needed, or just remove
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
