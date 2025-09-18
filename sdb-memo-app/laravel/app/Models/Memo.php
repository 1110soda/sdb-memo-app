<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes; //SoftDeletes: 一時的削除（delete()、削除する代わりに'deleted_at'に時間を記録してメモを保持）、完全削除（forceDelete()）

class Memo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'deadline_at',
    ];

//  deadline_atをCarbonインスタンスとして扱う
    protected $casts = [
        'deadline_at' => 'datetime',
    ];

//メモの所有者であるユーザーを取得する
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'memo_category', 'memo_id', 'category_id');
    }
}
