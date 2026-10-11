<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagUpdateRulesTest extends TestCase
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
        $request = new UpdateTagRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => '', // 必須違反
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_名前が50文字を超える場合はエラーになること(): void
    {
        $request = new UpdateTagRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => str_repeat('あ', 51), // 文字数オーバー
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_名前がデータ内で重複する場合はエラーになること(): void
    {
        $request = new UpdateTagRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => '質問', // 重複
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_自分自身のタグ名であればignoreされて通過すること(): void
    {
        $tag = Tag::where('name', '質問')->first();

        $request = new UpdateTagRequest();
        $request->merge([
            'id' => $tag->id,
            'name' => '質問',
        ]);
        $rules = $request->rules();

        $validator = Validator::make($request->all(), $rules);
        $this->assertTrue($validator->passes());
    }
}
