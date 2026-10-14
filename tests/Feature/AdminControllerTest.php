<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(CategorySeeder::class);
        $this->seed(TagSeeder::class);

        // 管理者認証が必要場合は、ログイン状態を作成
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::factory()->create();
    }

    public function test_管理者トップページにアクセスするとお問い合わせ一覧画面が表示される(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    public function test_お問い合わせ一覧画面が表示される(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('contacts');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    public function test_キーワードが255文字オーバーだとバリデーションエラーになる(): void
    {
        $response = $this->get('/admin?keyword='.str_repeat('あ', 256));

        $response->assertSessionHasErrors('keyword');
    }

    public function test_無効な性別だとバリデーションエラーになる(): void
    {
        $response = $this->get('/admin?gender=99');

        $response->assertSessionHasErrors('gender');
    }

    public function test_無効なカテゴリだとバリデーションエラーになる(): void
    {
        $response = $this->get('/admin?category_id=99');

        $response->assertSessionHasErrors('category_id');
    }

    public function test_苗字、名前、メールアドレスの絞り込み(): void
    {
        $Contact1 = Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
        ]);

        $Contact2 = Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
        ]);

        // キーワード検索（「山田」で検索）
        $response = $this->get('/admin?keyword=山田');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($Contact1, $Contact2) {
            return $contacts->contains($Contact1) && ! $contacts->contains($Contact2);
        });

        // キーワード検索（「花子」で検索）
        $response = $this->get('/admin?keyword=花子');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($Contact1, $Contact2) {
            return ! $contacts->contains($Contact1) && $contacts->contains($Contact2);
        });

        // キーワード検索（「example」で検索）
        $response = $this->get('/admin?keyword=example');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($Contact1, $Contact2) {
            return $contacts->contains($Contact1) && $contacts->contains($Contact2);
        });
    }

    public function test_性別の絞り込み(): void
    {
        $Contact1 = Contact::factory()->create([
            'gender' => 1,
        ]);

        $Contact2 = Contact::factory()->create([
            'gender' => 2,
        ]);

        // キーワード検索（「1」で検索）
        $response = $this->get('/admin?gender=1');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($Contact1, $Contact2) {
            return $contacts->contains($Contact1) && ! $contacts->contains($Contact2);
        });
    }

    public function test_カテゴリの絞り込み(): void
    {
        $category1 = Category::first();
        $category2 = Category::orderBy('id', 'desc')->first();

        $Contact1 = Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        $Contact2 = Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        // キーワード検索（カテゴリ1で検索）
        $response = $this->get('/admin?category_id='.$category1->id);

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($Contact1, $Contact2) {
            return $contacts->contains($Contact1) && ! $contacts->contains($Contact2);
        });
    }

    public function test_登録日の絞り込み(): void
    {
        $Contact1 = Contact::factory()->create([
            'created_at' => '2026-10-10 10:00:00',
        ]);

        $Contact2 = Contact::factory()->create([
            'created_at' => '2026-11-11 10:00:00',
        ]);

        // キーワード検索（「2026-10-10」で検索）
        $response = $this->get('/admin?date=2026-10-10');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($Contact1, $Contact2) {
            return $contacts->contains($Contact1) && ! $contacts->contains($Contact2);
        });
    }

    public function test_ページネーション（1ページ7件）の確認(): void
    {
        Contact::factory()->count(10)->create();

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 7;
        });
    }

    public function test_お問い合わせ詳細画面が表示される(): void
    {
        Contact::factory()->create();
        $contact = Contact::first();

        $response = $this->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertViewHas('contact', function ($Contact) use ($contact) {
            return $Contact->id === $contact->id && $Contact->relationLoaded('category');
        });
    }

    public function test_お問い合わせが削除される(): void
    {
        Contact::factory()->create();
        $contact = Contact::first();

        $response = $this->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        // データベースから削除されたことを確認
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
