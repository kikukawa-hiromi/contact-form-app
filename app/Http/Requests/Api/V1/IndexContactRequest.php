<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexContactRequest extends FormRequest
{
    /**
     * リクエストの認可
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前のデータ処理
     */
    protected function prepareForValidation(): void
    {
        // per_page が未指定・空の場合はデフォルト値をセット
        if (!$this->has('per_page')) {
            $this->merge([
                'per_page' => 20,
            ]);
        }
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'gender' => 'nullable|integer|in:1,2,3',
            'category_id' => 'nullable|integer|exists:categories,id',
            'date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * バリデーションメッセージ
     */
    public function messages(): array
    {
        return [
            'gender.in' => '性別の値が不正です',
            'category_id.exists' => '選択されたカテゴリーが存在しません',
        ];
    }
}
