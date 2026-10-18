<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    /**
     * リクエストの認可
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|integer|in:1,2,3',
            'email' => 'required|string|max:255|email',
            'tel' => 'required|string|regex:/^[0-9]{10,11}$/',
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'detail' => 'required|string|max:120',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ];
    }

    /**
     * バリデーションメッセージ
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'お問い合わせの種類を選択してください',
            'category_id.exists' => '選択されたカテゴリーが存在しません',
            'first_name.required' => '姓を入力してください',
            'last_name.required' => '名を入力してください',
            'gender.required' => '性別を選択してください',
            'gender.in' => '性別の値が不正です',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'tel.required' => '電話番号を入力してください',
            'tel.regex' => '電話番号はハイフンなしの10〜11桁で入力してください',
            'address.required' => '住所を入力してください',
            'detail.required' => 'お問い合わせ内容を入力してください',
            'detail.max' => 'お問い合わせ内容は120文字以内で入力してください',
            'tag_ids.*.exists' => '選択されたタグが存在しません',
        ];
    }
}
