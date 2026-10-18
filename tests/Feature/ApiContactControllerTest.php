<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(CategorySeeder::class);
        $this->seed(TagSeeder::class);
    }

    public function test_お問い合わせ一覧をJSON形式で取得できる(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_お問い合わせ一覧のJSONレスポンス構造が正しい(): void
    {
        Contact::factory()->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category' => [
                        'id',
                        'content',
                        ],
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
                    'tags' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_お問い合わせ一覧のJSONレスポンス内容が正しい(): void
    {
        $contact = Contact::factory()->create([
            'first_name' => 'てすと',
            'gender' => 1,
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $contact->id,
            'first_name' => 'てすと',
            'gender' => 1,
        ]);
    }

    public function test_キーワード指定でお問い合わせの絞り込みができる(): void
    {
        // 該当するデータと該当しないデータを作成
        Contact::factory()->create([
            'first_name' => 'てすと', 
            'last_name' => '太郎'
            ]);
        Contact::factory()->create([
            'first_name' => 'てすと', 
            'last_name' => '花子'
            ]);

        // '太郎' でキーワード検索
        $response = $this->getJson('/api/v1/contacts?keyword=太郎');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['last_name' => '太郎']);
    }

    public function test_指定したper_page件数でページネーションされた結果が取得できる(): void
    {
        // 10件のお問い合わせデータを作成
        Contact::factory()->count(10)->create();

        // per_page=3 でリクエスト
        $response = $this->getJson('/api/v1/contacts?per_page=3');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')          // 取得件数が3
            ->assertJsonPath('meta.per_page', 3)  // per_pageが3
            ->assertJsonPath('meta.total', 10)    // 全体件数が10
            ->assertJsonStructure([
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ]);
    }

    public function test_検索パラメーターが不正な場合バリデーションエラーを返却する(): void
    {
        // 不正な性別を指定
        $response = $this->getJson('/api/v1/contacts?' . http_build_query([
            'gender' => 99,
        ]));

        // 422 Unprocessable Entity と該当フィールドのエラーを確認
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_特定のお問い合わせをJSON形式で取得できる(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'category' => [
                    'id',
                    'content',
                    ],
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'building',
                'detail',
                'tags' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_特定のお問い合わせのJSONレスポンス内容が正しい(): void
    {
        $category = Category::first();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 1,
        ]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'category' => [
                    'id' => $category->id,
                    'content' => $category->content,
                ],
                'first_name' => 'てすと',
                'last_name' => '太郎',
                'gender' => 1,
                'email' => $contact->email,
                'tel' => $contact->tel,
                'address' => $contact->address,
                'building' => $contact->building,
                'detail' => $contact->detail,
                'tags' => [],
                'created_at' => $contact->created_at->toIso8601String(),
                'updated_at' => $contact->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function test_存在しないお問い合わせIDで404エラーを返す(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertNotFound(); // 404
    }

    public function test_お問い合わせをタグ付きで新規登録できる(): void
    {
        $category = Category::first();
        $tags = Tag::take(2)->get(); // 2つのタグを取得

        $contact = [
            'category_id' => $category->id,
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '大阪府',
            'building' => '大阪マンション',
            'detail' => 'お問い合わせの内容',
            'tag_ids' => $tags->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/v1/contacts', $contact);

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', 'テスト')
            ->assertJsonPath('data.last_name', '太郎')
            ->assertJsonCount(2, 'data.tags');

        $this->assertDatabaseHas('contacts', [
            'category_id' => $category->id,
            'first_name' => 'テスト',
            'email' => 'test@example.com',
        ]);

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'tag_id' => $tag->id,
            ]);
        }
    }

    public function test_タグなしでお問い合わせを新規登録できる(): void
    {
        $category = Category::first();

        $contact = [
            'category_id' => $category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => 'お問い合わせの内容',
        ];

        $response = $this->postJson('/api/v1/contacts', $contact);

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', 'てすと')
            ->assertJsonCount(0, 'data.tags');

        $this->assertDatabaseHas('contacts', [
            'category_id' => $category->id,
            'first_name' => 'てすと',
            'email' => 'test@example.com',
        ]);
    }

    public function test_必須項目が不足している場合は登録失敗しDBに保存されない(): void
    {
        $category = Category::first();

        $contact = [
            'category_id' => $category->id,
            'gender' => 1,
        ];

        $response = $this->postJson('/api/v1/contacts', $contact);

        // 422 バリデーションエラー
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);

        $this->assertDatabaseMissing('contacts', [
            'category_id' => $category->id,
        ]);
    }

    public function test_お問い合わせ情報とタグを更新できる(): void
    {
        $categoryOld = Category::first();
        $categoryNew = Category::skip(1)->first() ?? $categoryOld;
        
        $tagOld = Tag::first();
        $tagNew = Tag::skip(1)->first() ?? $tagOld;

        // 既存のお問い合わせを作成
        $contact = Contact::factory()->create([
            'category_id' => $categoryOld->id,
            'first_name' => '旧太郎',
            'email' => 'test@example.com',
        ]);
        $contact->tags()->attach([$tagOld->id]);

        // 更新データ（名前・カテゴリー・タグ）
        $updateData = [
            'category_id' => $categoryNew->id,
            'first_name' => '新太郎',
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => 'new@example.com',
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => '更新後の詳細テキストです。',
            'tag_ids' => [$tagNew->id],
         ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', '新太郎')
            ->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonPath('data.tags.0.id', $tagNew->id);

       $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '新太郎',
            'email' => 'new@example.com',
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagNew->id,
        ]);
        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagOld->id,
        ]);
    }

    public function test_不正な値の場合は更新に失敗しDBも変更されない(): void
    {
        $contact = Contact::factory()->create([
            'tel' => '08012341234',
        ]);

        $updateData = [
            'first_name' => 'てすと',
            'tel' => '080-1234-1234', // 不正な電話番号
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tel']);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'tel' => '08012341234',
        ]);
    }

    public function test_存在しないIDの更新は404を返す(): void
    {
        $updateData = [
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => 'お問い合わせ内容',
        ];

        $response = $this->putJson('/api/v1/contacts/99999', $updateData);

        $response->assertStatus(404);
    }

    public function test_お問い合わせが削除できる(): void
    {
        $category = Category::first();
        $tag = Tag::first();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);
        $contact->tags()->attach([$tag->id]);

        // 削除前の存在確認
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id'     => $tag->id,
        ]);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id'     => $tag->id,
        ]);
    }

    public function test_存在しないIDのお問い合わせ削除は404を返す(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/99999');

        $response->assertStatus(404);
    }

}
