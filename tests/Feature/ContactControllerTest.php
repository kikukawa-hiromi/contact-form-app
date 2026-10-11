<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(CategorySeeder::class);
    }

    public function test_お問い合わせ確認画面が表示される(): void
    {
        $category = Category::where('content', '商品トラブル')->first();

        $response = $this->post(route('contacts.confirm'), [
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => 'テスト',
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
        $category = Category::where('content', '商品トラブル')->first();

        $response = $this->post(route('contacts.store'), [
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => 'テスト',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'building' => '大阪ハイツ',
            'detail' => '商品トラブルの内容',
        ]);

        $response->assertRedirect(route('contacts.thanks'));
        $this->assertDatabaseHas('contacts', [
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => 'テスト',
            'gender' => 3,
            'email' => 'test@example.com',
            'tel' => '08012341234',
            'address' => '大阪府',
            'building' => '大阪ハイツ',
            'detail' => '商品トラブルの内容',
        ]);
    }
}
