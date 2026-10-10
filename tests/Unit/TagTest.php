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

class TagTest extends TestCase
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
     * 1つのタグに複数のお問い合わせが紐づいていることの確認
     */
    public function test_tag_has_many_contacts_relation(): void
    {
        // シーダーで挿入された既存データを取得
        $category = Category::where('content', '商品トラブル')->first();
        $tag = Tag::where('name', '質問')->first();

        // カテゴリーIDを指定して子データ（お問い合わせ）を作成・紐付け
        $contact1 = Contact::factory()->create(['category_id' => $category->id]);
        $contact2 = Contact::factory()->create(['category_id' => $category->id]);

        // タグに対して複数のお問い合わせIDを紐付け (attach)
        $tag->contacts()->attach([$contact1->id, $contact2->id]);

        // タグから紐づくお問い合わせを取得
        $relatedContacts = $tag->fresh()->contacts;

        $this->assertInstanceOf(Collection::class, $relatedContacts);
        $this->assertCount(2, $relatedContacts);
        $this->assertTrue($relatedContacts->contains($contact1));
        $this->assertTrue($relatedContacts->contains($contact2));

        // 中間テーブル (contact_tag) のデータ存在確認
        $this->assertDatabaseHas('contact_tag', [
            'tag_id'     => $tag->id,
            'contact_id' => $contact1->id,
        ]);
        $this->assertDatabaseHas('contact_tag', [
            'tag_id'     => $tag->id,
            'contact_id' => $contact2->id,
        ]);
    }
}
