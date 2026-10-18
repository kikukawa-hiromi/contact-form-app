<?php

namespace Tests\Unit;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiStoreContactRequestTest extends TestCase
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
     * バリデーションルールの取得
     */
    private function getRules(): array
    {
        return (new StoreContactRequest())->rules();
    }

    /**
     * @dataProvider validationProvider
     */
    public function test_validation(array $data, bool $expectedResult, array $expectedFailedFields = [])
    {
        $validator = Validator::make($data, $this->getRules());

        if ($expectedResult && $validator->fails()) {
            dump('失敗したテストデータ:', $data);
            dd('発生したエラー:', $validator->errors()->toArray());
        }

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
            '正常系：全ての必須項目が正しい' => [
                'data' => [
                    'category_id' => 1,
                    'first_name' => 'てすと',
                    'last_name' => '太郎',
                    'gender' => 2,
                    'email' => 'test@example.com',
                    'tel' => '08012341234',
                    'address' => '大阪府',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => true,
            ],
            '異常系：カテゴリーは必須項目' => [
                'data' => [
                    'first_name' => 'てすと',
                    'last_name' => '太郎',
                    'gender' => 2,
                    'email' => 'test@example.com',
                    'tel' => '08012341234',
                    'address' => '大阪府',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['category_id' => 'category_id'],
            ],
            '異常系：苗字は必須項目' => [
                'data' => [
                    'category_id' => 1,
                    'last_name' => '太郎',
                    'gender' => 2,
                    'email' => 'test@example.com',
                    'tel' => '08012341234',
                    'address' => '大阪府',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['first_name' => 'first_name'],
            ],
            '異常系：名前は必須項目' => [
                'data' => [
                    'category_id' => 1,
                    'first_name' => 'てすと',
                    'gender' => 2,
                    'email' => 'test@example.com',
                    'tel' => '08012341234',
                    'address' => '大阪府',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['last_name' => 'last_name'],
            ],
            '異常系：性別は必須項目' => [
                'data' => [
                    'category_id' => 1,
                    'first_name' => 'てすと',
                    'last_name' => '太郎',
                    'email' => 'test@example.com',
                    'tel' => '08012341234',
                    'address' => '大阪府',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['gender' => 'gender'],
            ],
            '異常系：メールアドレスは必須項目' => [
                'data' => [
                    'category_id' => 1,
                    'first_name' => 'てすと',
                    'last_name' => '太郎',
                    'gender' => 2,
                    'tel' => '08012341234',
                    'address' => '大阪府',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['email' => 'email'],
            ],
            '異常系：電話番号は必須項目' => [
                'data' => [
                    'category_id' => 1,
                    'first_name' => 'てすと',
                    'last_name' => '太郎',
                    'gender' => 2,
                    'email' => 'test@example.com',
                    'address' => '大阪府',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['tel' => 'tel'],
            ],
            '異常系：住所は必須項目' => [
                'data' => [
                    'category_id' => 1,
                    'first_name' => 'てすと',
                    'last_name' => '太郎',
                    'gender' => 2,
                    'email' => 'test@example.com',
                    'tel' => '08012341234',
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['address' => 'address'],
            ],
            '異常系：お問い合わせ内容は必須項目' => [
                'data' => [
                    'category_id' => 1,
                    'first_name' => 'てすと',
                    'last_name' => '太郎',
                    'gender' => 2,
                    'email' => 'test@example.com',
                    'tel' => '08012341234',
                    'address' => '大阪府',
                    'tag_ids' => [1,3],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['detail' => 'detail'],
            ],
            '異常系：電話番号の形式が不正' => [
                'data' => [
                    'tel' => '080-1234-1234',
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['tel' => 'tel'],
            ],
            '異常系：性別の形式が不正' => [
                'data' => [
                    'gender' => 99,
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['gender' => 'gender'],
            ],
            '異常系：カテゴリーがデータに存在しない' => [
                'data' => [
                    'category_id' => 99,
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['category_id' => 'category_id'],
            ],
            '異常系：タグがデータに存在しない' => [
                'data' => [
                    'tag_ids' => [99],
                ],
                'expectedResult' => false,
                'expectedFailedFields' => ['tag_ids.0' => 'tag_ids.0'],
            ],
        ];
    }
}
