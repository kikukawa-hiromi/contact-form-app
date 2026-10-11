<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    /**
     * お問い合わせ入力フォームを表示
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories','tags'));
    }

    /**
     * お問い合わせの確認画面を表示
     */
    public function confirm(StoreContactRequest $request)
    {
         $validated = $request->validated();
         $category = Category::find($validated['category_id']);

        // 入力値を保持したまま確認画面へ
        return view('contact.confirm', compact('validated','category'));
    }

    /**
     * お問い合わせを新規作成
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        Contact::create($validated);

        return redirect()->route('contacts.thanks');
    }

    /**
     * サンクスページを表示
     */
    public function thanks()
    {
        return view('contact.thanks');
    }
}
