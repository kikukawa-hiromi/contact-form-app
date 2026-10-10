<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact_tag extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contact_id',
        'tag_id',
    ];

    /**
     * この問い合わせ×タグが属する問い合わせ情報を取得
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * この問い合わせ×タグが属するタグを取得
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
