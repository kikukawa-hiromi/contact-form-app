<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'building',
        'detail',
    ];

    /**
     * 検索条件の共通化
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            // キーワード検索（苗字または名前またはメールアドレスに含まれるか）
            ->when($filters['keyword'] ?? null, 
                function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            // 性別指定（値が存在する場合のみ絞り込み）
            ->when($filters['gender'] ?? null, 
                function ($query, $gender) {
                    $query->where('gender', $gender);
            })
            // お問い合わせの種類指定
            ->when($filters['category_id'] ?? null, 
                function ($query, $categoryId) {
                    $query->where('category_id', $categoryId);
            })
            // 登録日指定
            ->when($filters['date'] ?? null, 
                function ($query, $date) {
                    $query->whereDate('created_at', $date);
            });
    }

    /**
     * この問い合わせが属するカテゴリを取得
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * シーダーでattach()を使用するための多対多リレーション
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
