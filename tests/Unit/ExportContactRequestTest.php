<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してデータを挿入
        $this->seed(CategorySeeder::class);
    }

    /**
     * バリデーションルールの取得
     */
    private function getRules(): array
    {
        return (new ExportContactRequest())->rules();
    }

    /**
     * @dataProvider validationProvider
     */
    public function test_validation(array $data, bool $expectedResult, array $expectedFailedFields = [])
    {
        $validator = Validator::make($data, $this->getRules());

        // バリデーションの検証
        $this->assertEquals($expectedResult, $validator->passes());

        // 失敗時はエラーが発生しているか検証
        if (! $expectedResult) {
            $this->assertArrayHasKey(
                array_key_first($expectedFailedFields),
                $validator->errors()->toArray()
            );
        }
    }

    /**
     * テストデータの提供
     */
    public static function validationProvider(): array
    {
        return [
            '正常系：キーワードが正しい' => [
                'data' => [
                    'keyword' => 'てすと',
                ],
                'expectedResult' => true,
            ],
            '正常系：性別が正しい' => [
                'data' => [
                    'gender' => 1,
                ],
                'expectedResult' => true,
            ],
            '正常系：カテゴリーが正しい' => [
                'data' => [
                    'category_id' => 1,
                ],
                'expectedResult' => true,
            ],
            '正常系：登録日が正しい' => [
                'data' => [
                    'date' => '2026/10/15',
                ],
                'expectedResult' => true,
            ],
            '異常系：性別の形式が不正' => [
                'data' => [
                    'gender' => 99,
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['gender' => 'gender'],
            ],
            '異常系：カテゴリーの形式が不正' => [
                'data' => [
                    'category_id' => 99,
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['category_id' => 'category_id'],
            ],
        ];
    }
}
