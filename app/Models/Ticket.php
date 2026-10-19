<?php

namespace App\Models;

use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'created_by',
        'assigned_to',
        'category_id',
        'title',
        'description',
        'status',
        'priority',
    ];

    /**
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isStaff()) {
            $query->where('created_by', $user->id);
        }

        return $query;
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return HasMany<TicketActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->oldest();
    }

    public function log(User $user, string $type, ?string $field = null, ?string $from = null, ?string $to = null): void
    {
        $this->activities()->create([
            'user_id' => $user->id,
            'type' => $type,
            'field' => $field,
            'from_value' => $from,
            'to_value' => $to,
        ]);
    }
}
