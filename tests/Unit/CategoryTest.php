<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Database\Seeders\CategorySeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(CategorySeeder::class);
    }

    /**
     * カテゴリーに紐づく複数お問い合わせ情報の取得確認
     */
    public function test_category_has_many_contact_relation(): void
    {
        // シーダーで挿入された既存データを取得
        $category = Category::where('content', '商品のお届けについて')->first();

        // カテゴリーIDを指定して子データ（お問い合わせ）を作成・紐付け
        $contact1 = Contact::factory()->create(['category_id' => $category->id]);
        $contact2 = Contact::factory()->create(['category_id' => $category->id]);

        // リレーション（親 -> 子）の検証
        $this->assertInstanceOf(Collection::class, $category->contacts);
        $this->assertCount(2, $category->contacts);
        $this->assertTrue($category->contacts->contains($contact1));
        $this->assertTrue($category->contacts->contains($contact2));
    }
}
