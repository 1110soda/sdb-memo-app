<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color_code',
    ];

    public function memos(): BelongsToMany
    {
        return $this->belongsToMany(Memo::class, 'memo_category', 'category_id', 'memo_id');
    }
}
