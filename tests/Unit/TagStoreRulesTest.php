<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagStoreRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(TagSeeder::class);
    }

    public function test_名前が未入力の場合はエラーになること(): void
    {
        $request = new StoreTagRequest;
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => '', // 必須違反
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_名前が50文字を超える場合はエラーになること(): void
    {
        $request = new StoreTagRequest;
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => str_repeat('あ', 51), // 文字数オーバー
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_名前がデータ内で重複する場合はエラーになること(): void
    {
        $request = new StoreTagRequest;
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => '質問', // 重複
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
