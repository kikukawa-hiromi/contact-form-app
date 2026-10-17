<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(CategorySeeder::class);
        $this->seed(TagSeeder::class);
        $this->category = Category::where('content', '商品トラブル')->first();
    }

    public function test_トップページにアクセスするとお問い合わせページが表示されること(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    public function test_お問い合わせ確認画面が表示される(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'building' => '大阪ハイツ',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertViewHas('validated');
        $response->assertViewHas('category');
    }

    public function test_お問い合わせを作成できる(): void
    {
        $response = $this->post(route('contact.store'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'building' => '大阪ハイツ',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertRedirect(route('contact.thanks'));
        $this->assertDatabaseHas('contacts', [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'building' => '大阪ハイツ',
            'detail' => '商品トラブルの内容',
        ]);
    }

    public function test_苗字が空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => '',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_名前が空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_メールアドレスが空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => '',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_住所が空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('address');
    }

    public function test_問い合わせ内容が空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '',
        ]);

        $response->assertSessionHasErrors('detail');
    }

    public function test_メールアドレスが不正だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_電話番号が不正だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '080-1234-1234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('tel');
    }

    public function test_苗字が255文字オーバーだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => str_repeat('あ', 256),
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_名前が255文字オーバーだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => str_repeat('あ', 256),
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_メールアドレスが255文字オーバーだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => str_repeat('a', 240).'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_住所が255文字オーバーだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => str_repeat('a', 256),
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('address');
    }

    public function test_建物名が255文字オーバーだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'building' => str_repeat('あ', 256),
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('building');
    }

    public function test_お問い合わせ内容が120文字オーバーだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => str_repeat('a', 121),
        ]);

        $response->assertSessionHasErrors('detail');
    }

    public function test_無効なカテゴリだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => 99, // 無効なカテゴリ
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_無効な性別だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 99, // 無効な性別（1, 2, 3以外）
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertSessionHasErrors('gender');
    }

    public function test_タグが配列でないとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
            'tag_ids' => '1,2',
        ]);

        $response->assertSessionHasErrors('tag_ids');
    }

    public function test_無効なタグだとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'detail' => '商品トラブルの内容',
            'tag_ids' => [1, 99],
        ]);

        $response->assertSessionHasErrors('tag_ids.*');
    }

    public function test_CSVを正しくダウンロードされる()
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'building' => '大阪マンション',
            'detail' => '商品トラブルの内容',
        ]);

        // CSVエクスポートルートへGETリクエストを送信
        $response = $this->actingAs($user)->get(route('contacts.export'));

        // レスポンスステータスとヘッダーの検証
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition');

        // ストリーム出力されたCSVコンテンツを取得
        $content = $response->streamedContent();

        // UTF-8 BOMが先頭に含まれているか検証
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // CSVヘッダー行およびデータ行の内容を検証
        $this->assertStringContainsString('ID,氏名,性別,メール,電話,住所,建物,カテゴリ,内容,作成日時', $content);
        $this->assertStringContainsString('てすと 太郎', $content);
        $this->assertStringContainsString('男性', $content);
        $this->assertStringContainsString('test@example.com', $content);
        $this->assertStringContainsString('08012341234', $content);
        $this->assertStringContainsString('大阪府', $content);
        $this->assertStringContainsString('大阪マンション', $content);
        $this->assertStringContainsString('商品トラブルの内容', $content);
    }

    public function test_検索条件で絞り込んだデータのみがCSVに含まれる()
    {
        $user = User::factory()->create();

        // 対象データ
        $targetContact = Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => 'てすと',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
        ]);

        // 非対象データ
        $otherContact = Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => 'てすと対象外',
            'last_name' => '太郎',
            'gender' => 2,
            'email' => 'test@example.com',
        ]);

        // 検索クエリ付きでリクエスト送信 (例: gender=1)
        $response = $this->actingAs($user)->get(route('contacts.export', ['gender' => 1]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        // 該当データが含まれ、対象外が含まれないことを検証
        $this->assertStringContainsString('てすと 太郎', $content);
        $this->assertStringNotContainsString('てすと対象外 太郎', $content);
    }
}
