<?php

namespace App\Models;

use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function scopeVisibleTo($query, User $user)
    {
        if (! $user->isStaff()) {
            $query->where('created_by', $user->id);
        }

        return $query;
    }

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function assignee()
{
    return $this->belongsTo(User::class, 'assigned_to');
}

public function category()
{
return $this->belongsTo(Category::class);
}

public function comments()
{
    return $this->hasMany(Comment::class);
}

}
