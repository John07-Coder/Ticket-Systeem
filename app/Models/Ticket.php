<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function assignee()
{
    return $this->belongsTo(User::class, "assisgned_to");
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
