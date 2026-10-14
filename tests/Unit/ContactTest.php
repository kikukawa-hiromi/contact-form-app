<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(CategorySeeder::class);
        $this->seed(TagSeeder::class);
    }

    /**
     * お問い合わせが属するカテゴリーの取得確認
     */
    public function test_contact_belong_to_category_relation(): void
    {
        // シーダーで挿入された既存データを取得
        $category = Category::where('content', '商品トラブル')->first();

        // カテゴリーIDを指定して子データ（お問い合わせ）を作成・紐付け
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        // リレーション（子 -> 親）の検証
        $this->assertInstanceOf(Category::class, $contact->category);
        $this->assertEquals($category->id, $contact->category->id);
        $this->assertEquals('商品トラブル', $contact->category->content);
    }

    /**
     * お問い合わせとタグの同期確認
     */
    public function test_contact_tag_sync(): void
    {
        // シーダーで挿入された既存データを取得
        $category = Category::where('content', '商品トラブル')->first();

        // カテゴリーIDを指定して子データ（お問い合わせ）を作成・紐付け
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        // タグ3件を用意
        $tag1 = Tag::where('name', '質問')->first();
        $tag2 = Tag::where('name', '要望')->first();
        $tag3 = Tag::create(['name' => 'テスト']);

        // tag1 と tag2 の ID を同期 (sync)
        $contact->tags()->sync([$tag1->id, $tag2->id]);

        // リレーション経由で取得したコレクションの検証
        $syncedTags = $contact->fresh()->tags;

        $this->assertInstanceOf(Collection::class, $syncedTags);
        $this->assertCount(2, $syncedTags);
        $this->assertTrue($syncedTags->contains($tag1));
        $this->assertTrue($syncedTags->contains($tag2));
        $this->assertFalse($syncedTags->contains($tag3)); // tag3 は含まれない

        // 中間テーブル (contact_tag) のデータ存在検証
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag1->id,
        ]);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag2->id,
        ]);
    }
}
