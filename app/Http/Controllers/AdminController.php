<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    /**
     * お問い合わせ一覧を表示
     */
    public function index(IndexContactRequest $request)
    {
        $categories = Category::all();
        $tags = Tag::all();

        // リクエストから必要な検索パラメーターのみ取得
        $filters = $request->only(['keyword', 'gender', 'category_id', 'date']);

        // スコープ呼びで全検索条件が適用
        $contacts = Contact::filter($filters)
                        ->with('tags')
                        ->paginate(7); // ページネーション（1ページに7件）

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * お問い合わせ詳細を表示
     */
    public function show(Contact $contact)
    {
        $contact->load('category');

        return view('admin.show', compact('contact'));
    }

    /**
     * お問い合わせを削除
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
